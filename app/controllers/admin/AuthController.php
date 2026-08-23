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
            $error = t('admin.login.locked', [
                'min' => (string) (int) ceil(\RateLimiter::lockSecondsLeft() / 60),
            ]);
        }
        admin_view('login', ['error' => $error]);
    }

    public static function loginPost(): void
    {
        $email    = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $result = \Auth::attemptLogin($email, $password);

        if ($result['ok']) {
            flash('success', t('admin.welcome', ['name' => $result['name'] ?? '']));
            redirect('/admin/dashboard');
        }

        $code = $result['code'] ?? 'invalid';
        if ($code === 'locked') {
            $error = t('admin.login.locked', [
                'min' => (string) (int) ceil(\RateLimiter::lockSecondsLeft() / 60),
            ]);
        } elseif ($code === 'empty') {
            $error = t('admin.login.empty');
        } else {
            $error = t('admin.login.invalid');
        }
        admin_view('login', ['error' => $error, 'email' => $email]);
    }

    public static function logoutPost(): void
    {
        \Auth::logout();
        // Fresh session for the login page.
        session_name('PESESS');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
        session_regenerate_id(true);
        redirect('/admin/login');
    }
}
