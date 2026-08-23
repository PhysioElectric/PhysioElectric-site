<?php
declare(strict_types=1);

/**
 * CSRF protection: per-session token, verified on every admin POST.
 */
final class Csrf
{
    private const SESSION_KEY = 'csrf_token';
    private const TOKEN_BYTES = 32;

    /** Returns the current token (generates one on first use). */
    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(self::TOKEN_BYTES));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /** Hidden input for forms. */
    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::token() . '">';
    }

    /** Constant-time verification. */
    public static function verify(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }
        return hash_equals(self::token(), $token);
    }

    /**
     * Guard for admin POST requests. Aborts with 419 when the token
     * is missing or invalid.
     */
    public static function protect(): void
    {
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        if (!self::verify(is_string($token) ? $token : null)) {
            http_response_code(419);
            exit('419 - CSRF token mismatch. Go back, refresh the page and try again.');
        }
    }
}
