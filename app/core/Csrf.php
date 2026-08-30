<?php
declare(strict_types=1);

/**
 * CSRF protection.
 *
 *  - one random token per session, stored server side
 *  - rotated on login / logout (token is bound to the session, not the browser)
 *  - constant-time comparison
 *  - verified on every state-changing admin request, from either the
 *    `csrf_token` form field or the `X-CSRF-TOKEN` header
 *  - a same-origin check runs in front of it as a second layer
 */
final class Csrf
{
    private const SESSION_KEY = 'csrf_token';
    private const TOKEN_BYTES = 32;

    /** Returns the current token (generates one on first use). */
    public static function token(): string
    {
        $current = $_SESSION[self::SESSION_KEY] ?? null;
        if (!is_string($current) || strlen($current) !== self::TOKEN_BYTES * 2) {
            self::rotate();
        }
        return (string) $_SESSION[self::SESSION_KEY];
    }

    /** Issue a fresh token. Call after any privilege change (login/logout). */
    public static function rotate(): string
    {
        $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(self::TOKEN_BYTES));
        return $_SESSION[self::SESSION_KEY];
    }

    /** Hidden input for forms. */
    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    /** Attribute for the admin JS bootstrap. */
    public static function attr(): string
    {
        return htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
    }

    /** Constant-time verification. */
    public static function verify(?string $token): bool
    {
        if (!is_string($token) || $token === '') {
            return false;
        }
        return hash_equals(self::token(), $token);
    }

    /**
     * Guard for state-changing admin requests. Aborts with 403 when the
     * token is missing, malformed, or the request is cross-origin.
     */
    public static function protect(): void
    {
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        if (!self::verify(is_string($token) ? $token : null)) {
            Security::audit('csrf.rejected', [
                'path'  => $_SERVER['REQUEST_URI'] ?? '',
                'has_token' => is_string($token) && $token !== '',
            ]);
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            exit('403 - CSRF token mismatch. Go back, refresh the page and try again.');
        }

        if (!Security::isSameOrigin()) {
            Security::audit('csrf.origin_mismatch', ['path' => $_SERVER['REQUEST_URI'] ?? '']);
            http_response_code(403);
            header('Content-Type: text/plain; charset=utf-8');
            exit('403 - Cross-origin request rejected.');
        }
    }
}
