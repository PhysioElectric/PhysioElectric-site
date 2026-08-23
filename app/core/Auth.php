<?php
declare(strict_types=1);

/**
 * Session-based authentication with Argon2id/Bcrypt password hashing,
 * session-id regeneration on login and strict session cookies.
 */
final class Auth
{
    private const SESSION_USER_ID   = 'pe_user_id';
    private const SESSION_USER_NAME = 'pe_user_name';

    public static function attemptLogin(string $email, string $password): array
    {
        $email = trim($email);
        if ($email === '' || $password === '') {
            return ['ok' => false, 'code' => 'empty'];
        }
        if (RateLimiter::isLocked()) {
            return ['ok' => false, 'code' => 'locked'];
        }

        $st = Database::pdo()->prepare(
            'SELECT id, name, email, password_hash, is_active FROM users WHERE email = :email LIMIT 1'
        );
        $st->execute([':email' => $email]);
        $user = $st->fetch();

        // Constant-ish time: always run a verify against a dummy hash
        // when the user does not exist, to limit user enumeration.
        $hash = $user['password_hash'] ?? '$argon2id$v=19$m=65536,t=4,p=1$c2VjcmV0c2FsdHNlYw$dummydummydummydummydummydu';
        $verified = is_array($user) && password_verify($password, $hash);

        if (!is_array($user) || $user['is_active'] != 1 || !$verified) {
            RateLimiter::recordFailure();
            return ['ok' => false, 'code' => 'invalid'];
        }

        // Rehash transparently if the algorithm/cost changed.
        if (password_needs_rehash($hash, self::hashOptions())) {
            $upd = Database::pdo()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
            $upd->execute([':hash' => password_hash($password, self::hashOptions()), ':id' => $user['id']]);
        }

        session_regenerate_id(true);
        $_SESSION[self::SESSION_USER_ID]   = (int) $user['id'];
        $_SESSION[self::SESSION_USER_NAME] = $user['name'];

        RateLimiter::recordSuccess();
        $upd = Database::pdo()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $upd->execute([':id' => $user['id']]);

        return ['ok' => true, 'code' => 'ok', 'name' => $user['name']];
    }

    public static function check(): bool
    {
        $id = $_SESSION[self::SESSION_USER_ID] ?? null;
        if (!is_int($id) && !is_string($id)) {
            return false;
        }
        $st = Database::pdo()->prepare('SELECT id, is_active FROM users WHERE id = :id LIMIT 1');
        $st->execute([':id' => (int) $id]);
        $row = $st->fetch();
        return is_array($row) && (int) $row['is_active'] === 1;
    }

    public static function userId(): ?int
    {
        $id = $_SESSION[self::SESSION_USER_ID] ?? null;
        return is_int($id) || is_string($id) ? (int) $id : null;
    }

    public static function userName(): string
    {
        $name = $_SESSION[self::SESSION_USER_NAME] ?? '';
        return is_string($name) ? $name : 'Admin';
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    /** Guard: aborts with a redirect when not authenticated. */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            session_regenerate_id(true);
            header('Location: /admin/login');
            exit;
        }
    }

    public static function hashOptions(): string
    {
        if (defined('PASSWORD_ARGON2ID')) {
            return PASSWORD_ARGON2ID;
        }
        return PASSWORD_BCRYPT;
    }
}
