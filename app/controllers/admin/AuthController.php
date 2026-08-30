<?php
declare(strict_types=1);

namespace Admin;

final class AuthController
{
    public static function loginForm(): void
    {
        if (\Auth::check()) {
            redirect('/admin/dashboard');
        }
        $error = null;
        if (\RateLimiter::isLocked()) {
            $error = self::lockedMessage();
            http_response_code(429);
        }
        admin_view('login', ['error' => $error]);
    }

    public static function loginPost(): void
    {
        $email    = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $result = \Auth::attemptLogin($email, $password);

        if ($result['ok']) {
            flash('success', t('admin.welcome', ['name' => (string) ($result['name'] ?? '')]));
            // Return to the page the admin originally asked for (allow-listed
            // to /admin by Auth::takeLoginTarget).
            redirect(\Auth::takeLoginTarget());
        }

        $code = (string) ($result['code'] ?? 'invalid');
        if ($code === 'locked') {
            http_response_code(429);
            $error = self::lockedMessage();
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || self::wantsJson()) {
                header('Retry-After: ' . \RateLimiter::lockSecondsLeft());
            }
        } elseif ($code === 'empty') {
            http_response_code(400);
            $error = t('admin.login.empty');
        } else {
            http_response_code(401);
            $error = t('admin.login.invalid');
        }

        // Never echo the submitted password back; the e-mail is safe.
        admin_view('login', ['error' => $error, 'email' => $email]);
    }

    public static function logoutPost(): void
    {
        \Auth::logout();
        redirect('/admin/login');
    }

    private static function lockedMessage(): string
    {
        $mins = (int) ceil(\RateLimiter::lockSecondsLeft() / 60);
        return t('admin.login.locked', ['min' => (string) max(1, $mins)]);
    }

    private static function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return is_string($accept) && str_contains($accept, 'application/json');
    }
}
