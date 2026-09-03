<?php
declare(strict_types=1);

namespace Admin;

/**
 * "My account": the signed-in admin manages *their own* credentials here.
 *
 *  - changePassword(): current password verified with password_verify,
 *    new password must pass PasswordPolicy::validate() (shared policy) and
 *    differ from the old one; after the rotation the session id is
 *    regenerated and the CSRF token rotated (Auth::refreshSession()).
 *  - forcedPasswordChange(): same rotation, but reached right after a login
 *    where users.force_password_change = 1. Until it succeeds the router
 *    blocks every other /admin route.
 *  - TOTP second factor management (optional, opt-in per account).
 *
 * Editing OTHER users' passwords is deliberately impossible here and in
 * AdminUserController: a password can only be changed by its owner.
 */
final class AccountController
{
    public static function index(): void
    {
        $user = \UserModel::byId(\Auth::userId());
        if ($user === null) {
            \Auth::logout();
            redirect('/admin/login');
        }
        admin_view('account/index', [
            'user'        => $user,
            'roleLabels'  => \UserModel::ROLE_LABELS,
            'adminActive' => 'account',
        ]);
    }

    // ------------------------------------------------------------------
    //  Password change (own account)
    // ------------------------------------------------------------------

    public static function changePasswordPost(): void
    {
        $actor = self::actorOrRedirect();
        if ($actor === null) {
            return;
        }

        $current = input_str('current_password');
        $new     = input_str('new_password');
        $confirm = input_str('confirm_password');

        // Re-authentication: the *current* password must verify first.
        if (!password_verify($current, (string) $actor['password_hash'])) {
            \Security::audit('account.reauth_failed', [
                'user_id' => (int) $actor['id'],
                'context' => 'password_change',
            ]);
            flash('error', 'رمز عبور فعلی نادرست است.');
            redirect('/admin/account');
        }

        $error = self::validateNewPassword($new, $confirm, (string) $actor['password_hash'], $actor);
        if ($error !== null) {
            flash('error', $error);
            redirect('/admin/account');
        }

        self::rotatePassword((int) $actor['id'], $new, false);
        flash('success', 'رمز عبور با موفقیت تغییر کرد. برای امنیت بیشتر، ۲FA را فعال کنید.');
        redirect('/admin/account');
    }

    /**
     * Forced first-login password rotation (users.force_password_change=1).
     * The session is freshly authenticated, so no current-password prompt.
     */
    public static function forcedPasswordForm(): void
    {
        if (!\Auth::forcePasswordChange()) {
            redirect('/admin/dashboard');
        }
        admin_view('account/forced-password', [
            'user'        => \UserModel::byId(\Auth::userId()),
            'roleLabels'  => \UserModel::ROLE_LABELS,
            'adminActive' => '',
        ]);
    }

    public static function forcedPasswordPost(): void
    {
        if (!\Auth::forcePasswordChange()) {
            redirect('/admin/dashboard');
        }
        $actor = self::actorOrRedirect();
        if ($actor === null) {
            return;
        }

        $new     = input_str('new_password');
        $confirm = input_str('confirm_password');

        $error = self::validateNewPassword($new, $confirm, (string) $actor['password_hash'], $actor);
        if ($error !== null) {
            flash('error', $error);
            redirect('/admin/forced-password');
        }

        self::rotatePassword((int) $actor['id'], $new, true);
        flash('success', 'رمز عبور تغییر کرد. حالا می‌توانید از پنل استفاده کنید.');
        redirect('/admin/dashboard');
    }

    /**
     * Shared by both flows: policy check + different-from-current check.
     */
    private static function validateNewPassword(string $new, string $confirm, string $oldHash, array $actor): ?string
    {
        if ($new === '' || $confirm === '') {
            return 'رمز عبور جدید و تکرار آن را وارد کنید.';
        }
        if ($new !== $confirm) {
            return 'تکرار رمز عبور با رمز عبور جدید یکسان نیست.';
        }
        $policy = \PasswordPolicy::validate($new, (string) ($actor['email'] ?? ''), (string) ($actor['name'] ?? ''));
        if (!$policy['ok']) {
            return (string) $policy['reason'];
        }
        if (password_verify($new, $oldHash)) {
            return 'رمز عبور جدید باید با رمز عبور فعلی متفاوت باشد.';
        }
        return null;
    }

    /**
     * Hash with the project's algorithm/cost, persist, refresh the session
     * and audit. $forced controls the force_password_change flag.
     */
    private static function rotatePassword(int $userId, string $new, bool $forced): void
    {
        $hash = password_hash($new, \Auth::hashOptions(), \Auth::hashAlgoOptions());
        \UserModel::setPasswordHash($userId, $hash, true);
        \Auth::refreshSession(); // new session id + rotated CSRF token
        \Security::audit(
            $forced ? 'account.forced_password_change_completed' : 'account.password_changed',
            ['user_id' => $userId]
        );
    }

    // ------------------------------------------------------------------
    //  TOTP second factor (self-service)
    // ------------------------------------------------------------------

    /** Secret parked in the session until the confirm step succeeds. */
    private const SESSION_PENDING_TOTP = 'pe_2fa_pending_secret';

    /** Show a freshly generated secret + otpauth:// URI + confirm form. */
    public static function twofaSetupForm(): void
    {
        $user = self::actorOrRedirect();
        if ($user === null) {
            return;
        }
        if ((int) ($user['totp_enabled'] ?? 0) === 1) {
            // Already active — the account page shows the current state.
            redirect('/admin/account');
        }
        $secret = $_SESSION[self::SESSION_PENDING_TOTP] ?? null;
        if (!is_string($secret) || $secret === '') {
            $secret = \Totp::generateSecret();
            $_SESSION[self::SESSION_PENDING_TOTP] = $secret;
        }
        admin_view('account/twofa-setup', [
            'user'    => $user,
            'secret'  => $secret,
            'otpauth' => \Totp::otpauthUri($secret, (string) ($user['email'] ?? '')),
            'adminActive' => 'account',
        ]);
    }

    /** Confirm: current password + a valid 6-digit code from the app. */
    public static function twofaSetupPost(): void
    {
        $actor = self::actorOrRedirect();
        if ($actor === null) {
            return;
        }
        if ((int) ($actor['totp_enabled'] ?? 0) === 1) {
            flash('error', '۲FA روی این حساب قبلاً فعال است.');
            redirect('/admin/account');
        }
        $secret = $_SESSION[self::SESSION_PENDING_TOTP] ?? null;
        if (!is_string($secret) || $secret === '' || strlen($secret) < 16) {
            flash('error', 'نشست ساخت ۲FA منقضی شده؛ دوباره شروع کنید.');
            redirect('/admin/account/2fa/setup');
        }

        $current = input_str('current_password');
        $code    = input_str('code');
        if (!password_verify($current, (string) $actor['password_hash'])) {
            \Security::audit('account.reauth_failed', [
                'user_id' => (int) $actor['id'],
                'context' => 'twofa_enable',
            ]);
            flash('error', 'رمز عبور فعلی نادرست است.');
            redirect('/admin/account/2fa/setup');
        }
        if (!\Totp::verify($secret, $code)) {
            \Security::audit('account.twofa_confirm_failed', ['user_id' => (int) $actor['id']]);
            flash('error', 'کد تأیید معتبر نیست. کد ۶ رقمی اپلیکیشن احراز هویت را وارد کنید.');
            redirect('/admin/account/2fa/setup');
        }

        \UserModel::enableTotp((int) $actor['id'], $secret);
        unset($_SESSION[self::SESSION_PENDING_TOTP]);
        \Auth::refreshSession();
        \Security::audit('account.twofa_enabled', ['user_id' => (int) $actor['id']]);
        flash('success', 'ورود دومرحله‌ای (TOTP) فعال شد. در ورودهای بعدی کد ۶ رقمی خواسته می‌شود.');
        redirect('/admin/account');
    }

    /** Disable: needs the account password again (session alone is not enough). */
    public static function twofaDisablePost(): void
    {
        $actor = self::actorOrRedirect();
        if ($actor === null) {
            return;
        }
        if (!password_verify(input_str('current_password'), (string) $actor['password_hash'])) {
            \Security::audit('account.reauth_failed', [
                'user_id' => (int) $actor['id'],
                'context' => 'twofa_disable',
            ]);
            flash('error', 'رمز عبور فعلی نادرست است.');
            redirect('/admin/account');
        }
        \UserModel::disableTotp((int) $actor['id']);
        unset($_SESSION[self::SESSION_PENDING_TOTP]);
        \Auth::refreshSession();
        \Security::audit('account.twofa_disabled', ['user_id' => (int) $actor['id']]);
        flash('success', 'ورود دومرحله‌ای غیرفعال شد.');
        redirect('/admin/account');
    }

    // ------------------------------------------------------------------

    /**
     * The acting user's full row (with hash). Redirects when the session
     * user vanished from the database.
     * @return array<string,mixed>|null
     */
    private static function actorOrRedirect(): ?array
    {
        $actor = \UserModel::byIdWithHash(\Auth::userId());
        if ($actor === null) {
            \Auth::logout();
            redirect('/admin/login');
        }
        return $actor;
    }
}
