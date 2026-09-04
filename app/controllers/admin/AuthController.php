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
        // A password-verified 2FA challenge is waiting for its code.
        if (\Auth::totpChallengeActive()) {
            redirect('/admin/login/2fa');
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
        $code   = (string) ($result['code'] ?? '');

        // Password OK, second factor pending → intermediate 2FA step.
        if ($code === 'needs_totp') {
            redirect('/admin/login/2fa');
        }

        if (!empty($result['ok'])) {
            flash('success', t('admin.welcome', ['name' => (string) ($result['name'] ?? '')]));
            // First login with a forced password change: the central router
            // blocks everything else until the password is rotated.
            if (!empty($result['force_password_change'])) {
                redirect('/admin/forced-password');
            }
            // Return to the page the admin originally asked for (allow-listed
            // to /admin by Auth::takeLoginTarget).
            redirect(\Auth::takeLoginTarget());
        }

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

    // ------------------------------------------------------------------
    //  2FA challenge step (public /admin/login/2fa)
    // ------------------------------------------------------------------

    public static function twoFaForm(): void
    {
        if (\Auth::check()) {
            redirect('/admin/dashboard');
        }
        if (!\Auth::totpChallengeActive()) {
            redirect('/admin/login');
        }
        $error = null;
        $status = 200;
        $bucket = self::twoFaBucket();
        if ($bucket !== null && \RateLimiter::isLocked(null, $bucket)) {
            http_response_code(429);
            $status = 429;
            $mins = (int) ceil(\RateLimiter::lockSecondsLeft(null, $bucket) / 60);
            $error = t('admin.login.2fa.locked', ['min' => (string) max(1, $mins)]);
        }
        admin_view('login2fa', ['error' => $error, 'status' => $status]);
    }

    public static function twoFaPost(): void
    {
        if (\Auth::check()) {
            redirect('/admin/dashboard');
        }
        if (!\Auth::totpChallengeActive()) {
            redirect('/admin/login');
        }
        // Digits only; a malformed payload simply fails verification.
        $code = (string) preg_replace('/[^0-9]/', '', (string) ($_POST['code'] ?? ''));

        $bucket = self::twoFaBucket();
        if ($bucket !== null && \RateLimiter::isLocked(null, $bucket)) {
            http_response_code(429);
            $mins = (int) ceil(\RateLimiter::lockSecondsLeft(null, $bucket) / 60);
            admin_view('login2fa', [
                'error'  => t('admin.login.2fa.locked', ['min' => (string) max(1, $mins)]),
                'status' => 429,
            ]);
            return;
        }

        $result = \Auth::verifyTotpChallenge($code);
        if (!empty($result['ok'])) {
            flash('success', t('admin.welcome', ['name' => (string) ($result['name'] ?? '')]));
            if (!empty($result['force_password_change'])) {
                redirect('/admin/forced-password');
            }
            redirect(\Auth::takeLoginTarget());
        }

        $rCode = (string) ($result['code'] ?? 'invalid');
        if ($rCode === 'locked') {
            http_response_code(429);
            $mins = (int) ceil(\RateLimiter::lockSecondsLeft(null, (string) $bucket) / 60);
            admin_view('login2fa', [
                'error'  => t('admin.login.2fa.locked', ['min' => (string) max(1, $mins)]),
                'status' => 429,
            ]);
            return;
        }
        if ($rCode === 'expired') {
            // Challenge timed out or the account state changed mid-login.
            redirect('/admin/login');
        }
        http_response_code(401);
        admin_view('login2fa', ['error' => t('admin.login.2fa.invalid'), 'status' => 401]);
    }

    public static function logoutPost(): void
    {
        \Auth::logout();
        redirect('/admin/login');
    }

    /** Rate-limit bucket of the pending challenge (null when inactive). */
    private static function twoFaBucket(): ?string
    {
        $id = $_SESSION['pe_2fa_user_id'] ?? null;
        return is_int($id) || is_string($id) ? '2fa:' . (int) $id : null;
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
