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
 *
 * RBAC & second factor (added in CHANGES-SECURITY-2.md):
 *  - the authoritative role lives in the database and is re-read on EVERY
 *    request (Auth::check()), so a role change by a super admin applies to
 *    open sessions immediately (no role cached in the session)
 *  - Auth::requireRole() is the central 403 gate used by the router
 *  - force_password_change is enforced server side on every admin request
 *    (Auth::forcePasswordChange()), not only in the UI
 *  - optional TOTP second factor: after a correct password the login is
 *    paused into a short-lived challenge (Auth::beginTotpChallenge) and only
 *    a valid 6-digit code starts the real authenticated session
 */
final class Auth
{
    private const SESSION_USER_ID    = 'pe_user_id';
    private const SESSION_USER_NAME  = 'pe_user_name';
    private const SESSION_CREATED_AT = 'pe_sess_created';
    private const SESSION_LAST_SEEN  = 'pe_sess_seen';

    /** Pending TOTP challenge (password already verified). */
    private const SESSION_2FA_USER_ID    = 'pe_2fa_user_id';
    private const SESSION_2FA_NAME       = 'pe_2fa_name';
    private const SESSION_2FA_TS         = 'pe_2fa_ts';

    private const TOTP_CHALLENGE_TTL = 600;   // 10 minutes to type the code

    /** Roles that exist in the users.role ENUM. */
    public const ROLES = ['super_admin', 'editor', 'viewer'];

    /** Lazily built, always-valid dummy hash for the unknown-user path. */
    private static ?string $dummyHash = null;

    /** User row as (re)validated by check(); null until first check(). */
    private static ?array $profile = null;

    /**
     * @return array{ok:bool, code:string, name?:string, user_id?:int, force_password_change?:bool}
     */
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
            'SELECT id, name, email, password_hash, is_active, role,
                    force_password_change, totp_enabled, totp_secret
             FROM users WHERE email = :email LIMIT 1'
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

        $userId   = (int) $user['id'];
        $userName = (string) $user['name'];
        $forcePw  = (int) ($user['force_password_change'] ?? 0) === 1;
        $totpOn   = (int) ($user['totp_enabled'] ?? 0) === 1;
        $totpKey  = is_string($user['totp_secret'] ?? null) ? (string) $user['totp_secret'] : '';

        // ---- Optional second factor -----------------------------------
        if ($totpOn) {
            if ($totpKey === '') {
                // Enabled flag without a stored key is a broken account:
                // silently skipping the second factor would be a downgrade.
                Security::audit('login.totp_misconfigured', ['email' => $email, 'user_id' => $userId]);
                RateLimiter::recordFailure(null, $email);
                return ['ok' => false, 'code' => 'invalid'];
            }
            RateLimiter::recordSuccess(null, $email); // password stage passed
            self::beginTotpChallenge($userId, $userName);
            Security::audit('login.2fa_required', ['email' => $email, 'user_id' => $userId]);
            return ['ok' => true, 'code' => 'needs_totp', 'name' => $userName];
        }

        self::startAuthenticatedSession($userId, $userName);

        RateLimiter::recordSuccess(null, $email);
        $upd = Database::pdo()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $upd->execute([':id' => $userId]);

        Security::audit('login.success', ['email' => $email, 'user_id' => $userId]);

        return [
            'ok' => true,
            'code' => 'ok',
            'name' => $userName,
            'user_id' => $userId,
            'force_password_change' => $forcePw,
        ];
    }

    /** Regenerate the session, bind the user, rotate the CSRF token. */
    private static function startAuthenticatedSession(int $userId, string $name): void
    {
        self::beginTotpChallengeReset(); // never leave a stale 2FA challenge around
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
     * re-verifies against the database on every request: the account must
     * still exist and be active, and the authoritative role / forced-password
     * flags are reloaded so changes apply to open sessions immediately.
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

        $st = Database::pdo()->prepare(
            'SELECT id, name, email, is_active, role, force_password_change
             FROM users WHERE id = :id LIMIT 1'
        );
        $st->execute([':id' => (int) $id]);
        $row = $st->fetch();
        if (!is_array($row) || (int) $row['is_active'] !== 1) {
            self::clearIdentity();
            return false;
        }
        // Keep the session display name in sync with the database.
        if ((string) $row['name'] !== (string) ($_SESSION[self::SESSION_USER_NAME] ?? '')) {
            $_SESSION[self::SESSION_USER_NAME] = (string) $row['name'];
        }
        self::$profile = $row;
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

    // ------------------------------------------------------------------
    //  Roles (authoritative copy re-read from the database by check())
    // ------------------------------------------------------------------

    /** Current role, or null when no validated profile is loaded. */
    public static function role(): ?string
    {
        $role = self::$profile['role'] ?? null;
        if (!is_string($role) || !in_array($role, self::ROLES, true)) {
            return null;
        }
        return $role;
    }

    /** True when the current user has one of the given roles. */
    public static function hasRole(string ...$allowed): bool
    {
        $role = self::role();
        if ($role === null) {
            return false;
        }
        return in_array($role, $allowed, true);
    }

    /**
     * Central 403 gate: like requireLogin() but for roles. Audited as
     * 'authz.denied' so blind role probing is visible in the logs. Only
     * meaningful after a successful Auth::check() (requireLogin).
     */
    public static function requireRole(string ...$allowed): void
    {
        if (self::hasRole(...$allowed)) {
            return;
        }
        Security::audit('authz.denied', [
            'user_id' => self::userId(),
            'role'    => self::role() ?? 'none',
            'required'=> implode('|', $allowed),
            'path'    => substr((string) ($_SERVER['REQUEST_URI'] ?? ''), 0, 200),
        ]);
        if (!headers_sent()) {
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            header('Cache-Control: no-store');
        }
        exit('403 - Forbidden. Your role does not allow this action.');
    }

    /**
     * True when the account must rotate its password before it can use any
     * other admin route (enforced centrally by the router).
     */
    public static function forcePasswordChange(): bool
    {
        if (self::$profile === null && !self::check()) {
            return false;
        }
        return (int) (self::$profile['force_password_change'] ?? 0) === 1;
    }

    // ------------------------------------------------------------------
    //  TOTP second factor (challenge state lives in the session)
    // ------------------------------------------------------------------

    /** True while a password-verified 2FA challenge is pending. */
    public static function totpChallengeActive(): bool
    {
        $uid = $_SESSION[self::SESSION_2FA_USER_ID] ?? null;
        $ts  = (int) ($_SESSION[self::SESSION_2FA_TS] ?? 0);
        if ((!is_int($uid) && !is_string($uid)) || $ts <= 0) {
            return false;
        }
        if (time() - $ts > self::TOTP_CHALLENGE_TTL) {
            self::beginTotpChallengeReset();
            Security::audit('login.2fa_expired', ['user_id' => (int) $uid]);
            return false;
        }
        return true;
    }

    /** Called after the password stage: regenerate + park the user id. */
    private static function beginTotpChallenge(int $userId, string $name): void
    {
        session_regenerate_id(true);
        $_SESSION[self::SESSION_2FA_USER_ID] = $userId;
        $_SESSION[self::SESSION_2FA_NAME]    = $name;
        $_SESSION[self::SESSION_2FA_TS]      = time();
    }

    private static function beginTotpChallengeReset(): void
    {
        unset(
            $_SESSION[self::SESSION_2FA_USER_ID],
            $_SESSION[self::SESSION_2FA_NAME],
            $_SESSION[self::SESSION_2FA_TS]
        );
    }

    /**
     * Verify the submitted authenticator code and, on success, start the
     * real authenticated session (session_regenerate_id + Csrf::rotate).
     *
     * @return array{ok:bool, code:string, name?:string, force_password_change?:bool}
     */
    public static function verifyTotpChallenge(string $code): array
    {
        if (!self::totpChallengeActive()) {
            return ['ok' => false, 'code' => 'expired'];
        }
        $userId = (int) $_SESSION[self::SESSION_2FA_USER_ID];

        // The 2FA code itself is brute-forceable: throttle it per account.
        $bucket = '2fa:' . $userId;
        if (RateLimiter::isLocked(null, $bucket)) {
            Security::audit('login.2fa_locked', ['user_id' => $userId]);
            return ['ok' => false, 'code' => 'locked'];
        }

        $st = Database::pdo()->prepare(
            'SELECT id, name, email, is_active, role, force_password_change,
                    totp_enabled, totp_secret
             FROM users WHERE id = :id LIMIT 1'
        );
        $st->execute([':id' => $userId]);
        $user = $st->fetch();
        if (!is_array($user) || (int) $user['is_active'] !== 1
            || (int) ($user['totp_enabled'] ?? 0) !== 1
            || !is_string($user['totp_secret'] ?? null)
            || (string) $user['totp_secret'] === '') {
            // Account deactivated / 2FA turned off mid-challenge → abort.
            self::beginTotpChallengeReset();
            Security::audit('login.2fa_challenge_invalid', ['user_id' => $userId]);
            return ['ok' => false, 'code' => 'expired'];
        }

        if (!Totp::verify((string) $user['totp_secret'], $code)) {
            RateLimiter::recordFailure(null, $bucket);
            Security::audit('login.2fa_failed', ['user_id' => $userId]);
            return ['ok' => false, 'code' => 'invalid'];
        }

        self::beginTotpChallengeReset();
        self::startAuthenticatedSession($userId, (string) $user['name']);

        RateLimiter::recordSuccess(null, $bucket);
        $upd = Database::pdo()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $upd->execute([':id' => $userId]);

        Security::audit('login.success', [
            'email'   => (string) ($user['email'] ?? ''),
            'user_id' => $userId,
            'via'     => 'totp',
        ]);

        return [
            'ok' => true,
            'code' => 'ok',
            'name' => (string) $user['name'],
            'force_password_change' => (int) ($user['force_password_change'] ?? 0) === 1,
        ];
    }

    // ------------------------------------------------------------------

    /** Drop the identity but keep the (now anonymous) session alive. */
    private static function clearIdentity(): void
    {
        self::$profile = null;
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
        self::$profile = null;
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

    /**
     * Post-privilege-change session refresh: new session id + rotated CSRF
     * token, identity preserved. Mirrors startAuthenticatedSession() for
     * flows that change credentials mid-session (password rotation).
     */
    public static function refreshSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        session_regenerate_id(true);
        Csrf::rotate();
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
