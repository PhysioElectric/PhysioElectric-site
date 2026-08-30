<?php
declare(strict_types=1);

/**
 * Session-based authentication.
 *
 *  - Argon2id (fallback: bcrypt) with explicit cost parameters
 *  - the "user does not exist" path runs a real hash verification against a
 *    *valid* dummy hash, so response time does not reveal whether an e-mail
 *    is registered (the previous dummy hash was malformed and made
 *    password_verify() return in ~0.004 ms instead of ~400 ms)
 *  - session id regenerated on login, CSRF token rotated with it
 *  - idle and absolute session lifetimes enforced server side
 *  - every attempt is written to the security audit log
 */
final class Auth
{
    private const SESSION_USER_ID    = 'pe_user_id';
    private const SESSION_USER_NAME  = 'pe_user_name';
    private const SESSION_CREATED_AT = 'pe_sess_created';
    private const SESSION_LAST_SEEN  = 'pe_sess_seen';

    /** Lazily built, always-valid dummy hash for the unknown-user path. */
    private static ?string $dummyHash = null;

    public static function attemptLogin(string $email, string $password): array
    {
        $email = trim($email);
        if ($email === '' || $password === '') {
            return ['ok' => false, 'code' => 'empty'];
        }
        if (mb_strlen($password) > 1000) {
            // Refuse absurd payloads before they reach the hash function.
            RateLimiter::recordFailure(null, $email);
            return ['ok' => false, 'code' => 'invalid'];
        }
        if (RateLimiter::isLocked(null, $email)) {
            Security::audit('login.locked', ['email' => $email]);
            return ['ok' => false, 'code' => 'locked'];
        }

        $st = Database::pdo()->prepare(
            'SELECT id, name, email, password_hash, is_active FROM users WHERE email = :email LIMIT 1'
        );
        $st->execute([':email' => mb_strtolower($email)]);
        $user = $st->fetch();

        // Always spend the same amount of CPU, known user or not.
        $hash     = is_array($user) ? (string) $user['password_hash'] : self::dummyHash();
        $verified = is_array($user) && password_verify($password, $hash);

        if (!is_array($user) || (int) $user['is_active'] !== 1 || !$verified) {
            RateLimiter::recordFailure(null, $email);
            Security::audit('login.failed', [
                'email'  => $email,
                'reason' => !is_array($user) ? 'unknown_user'
                    : ((int) $user['is_active'] !== 1 ? 'inactive' : 'bad_password'),
            ]);
            return ['ok' => false, 'code' => 'invalid'];
        }

        // Rehash transparently when the algorithm or its cost changed.
        if (password_needs_rehash($hash, self::hashOptions(), self::hashAlgoOptions())) {
            $upd = Database::pdo()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
            $upd->execute([
                ':hash' => password_hash($password, self::hashOptions(), self::hashAlgoOptions()),
                ':id'   => (int) $user['id'],
            ]);
        }

        self::startAuthenticatedSession((int) $user['id'], (string) $user['name']);

        RateLimiter::recordSuccess(null, $email);
        $upd = Database::pdo()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $upd->execute([':id' => (int) $user['id']]);

        Security::audit('login.success', ['email' => $email, 'user_id' => (int) $user['id']]);

        return ['ok' => true, 'code' => 'ok', 'name' => $user['name']];
    }

    /** Regenerate the session, bind the user, rotate the CSRF token. */
    private static function startAuthenticatedSession(int $userId, string $name): void
    {
        session_regenerate_id(true);
        $now = time();
        $_SESSION[self::SESSION_USER_ID]    = $userId;
        $_SESSION[self::SESSION_USER_NAME]  = $name;
        $_SESSION[self::SESSION_CREATED_AT] = $now;
        $_SESSION[self::SESSION_LAST_SEEN]  = $now;
        Csrf::rotate();
    }

    /**
     * Validated session check. Enforces the idle and absolute lifetimes and
     * re-verifies that the account is still active in the database.
     */
    public static function check(): bool
    {
        $id = $_SESSION[self::SESSION_USER_ID] ?? null;
        if (!is_int($id) && !is_string($id)) {
            return false;
        }

        $now     = time();
        $created = (int) ($_SESSION[self::SESSION_CREATED_AT] ?? 0);
        $seen    = (int) ($_SESSION[self::SESSION_LAST_SEEN] ?? 0);

        if ($created <= 0 || $seen <= 0) {
            // Session predates the lifetime tracking: force a fresh login.
            self::clearIdentity();
            return false;
        }
        if (($now - $seen) > Config::sessionIdleSeconds()) {
            Security::audit('session.idle_timeout', ['user_id' => (int) $id]);
            self::clearIdentity();
            return false;
        }
        if (($now - $created) > Config::sessionAbsoluteSeconds()) {
            Security::audit('session.absolute_timeout', ['user_id' => (int) $id]);
            self::clearIdentity();
            return false;
        }
        $_SESSION[self::SESSION_LAST_SEEN] = $now;

        $st = Database::pdo()->prepare('SELECT id, is_active FROM users WHERE id = :id LIMIT 1');
        $st->execute([':id' => (int) $id]);
        $row = $st->fetch();
        if (!is_array($row) || (int) $row['is_active'] !== 1) {
            self::clearIdentity();
            return false;
        }
        return true;
    }

    public static function userId(): ?int
    {
        $id = $_SESSION[self::SESSION_USER_ID] ?? null;
        return is_int($id) || is_string($id) ? (int) $id : null;
    }

    public static function userName(): string
    {
        $name = $_SESSION[self::SESSION_USER_NAME] ?? '';
        return is_string($name) && $name !== '' ? $name : 'Admin';
    }

    /** Drop the identity but keep the (now anonymous) session alive. */
    private static function clearIdentity(): void
    {
        unset(
            $_SESSION[self::SESSION_USER_ID],
            $_SESSION[self::SESSION_USER_NAME],
            $_SESSION[self::SESSION_CREATED_AT],
            $_SESSION[self::SESSION_LAST_SEEN]
        );
    }

    /** Full teardown: destroy the session and issue a brand new empty one. */
    public static function logout(): void
    {
        $userId = self::userId();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $p['path'],
                'domain'   => $p['domain'],
                'secure'   => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => $p['samesite'] ?? 'Lax',
            ]);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        // Force a *new* id: without this the browser would reuse the old one
        // whenever session.use_strict_mode is off (session fixation).
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        session_id(bin2hex(random_bytes(32)));
        session_start();
        $_SESSION = [];
        Csrf::rotate();

        Security::audit('logout', ['user_id' => $userId]);
    }

    /** Guard: redirects to the login page when not authenticated. */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            // Remember where the admin wanted to go (same-origin paths only).
            $target = $_SERVER['REQUEST_URI'] ?? '';
            if (is_string($target) && str_starts_with($target, '/admin')
                && !str_contains($target, "\n") && strlen($target) < 200) {
                $_SESSION['pe_login_target'] = $target;
            }
            header('Location: /admin/login');
            header('Cache-Control: no-store');
            exit;
        }
    }

    /** Consume the post-login redirect target (safe: allow-listed prefix). */
    public static function takeLoginTarget(): string
    {
        $t = $_SESSION['pe_login_target'] ?? '/admin/dashboard';
        unset($_SESSION['pe_login_target']);
        if (!is_string($t) || !str_starts_with($t, '/admin')
            || str_contains($t, '//') || str_contains($t, '\\')
            || str_contains($t, "\n") || str_contains($t, "\r")) {
            return '/admin/dashboard';
        }
        return $t;
    }

    public static function hashOptions(): string
    {
        if (defined('PASSWORD_ARGON2ID')) {
            return PASSWORD_ARGON2ID;
        }
        return PASSWORD_BCRYPT;
    }

    /** @return array<string,int> */
    public static function hashAlgoOptions(): array
    {
        if (defined('PASSWORD_ARGON2ID')) {
            return [
                'memory_cost' => 65536, // 64 MiB
                'time_cost'   => 4,
                'threads'     => 1,     // 1 keeps cost predictable per login
            ];
        }
        return ['cost' => 12];
    }

    /**
     * A syntactically valid hash of a random throwaway password. It is only
     * used to keep the timing of the unknown-user path identical to the
     * known-user path.
     */
    public static function dummyHash(): string
    {
        if (self::$dummyHash === null) {
            self::$dummyHash = password_hash(
                bin2hex(random_bytes(24)),
                self::hashOptions(),
                self::hashAlgoOptions()
            );
        }
        return self::$dummyHash;
    }
}
