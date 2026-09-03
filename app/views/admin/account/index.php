<?php
/**
 * Admin: "حساب من" — profile, own-password change and 2FA status.
 * Expects: $user, $roleLabels
 */
$adminTitle  = 'حساب من';
$adminActive = $adminActive ?? 'account';
$roleName    = (string) ($user['role'] ?? '');
$twofaOn     = (int) ($user['totp_enabled'] ?? 0) === 1;
?>
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">

    <!-- Profile summary -->
    <div class="admin-card p-6 xl:sticky xl:top-24">
        <div class="flex items-center gap-4 mb-5">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-physio-500 to-physio-900 text-white flex items-center justify-center text-xl font-bold shrink-0">
                <?= e(mb_strtoupper(mb_substr((string) ($user['name'] ?? 'A'), 0, 1))) ?>
            </div>
            <div class="min-w-0">
                <p class="font-bold text-slate-800 truncate"><?= e((string) ($user['name'] ?? '')) ?></p>
                <p class="text-xs text-slate-400 ltr" dir="ltr"><?= e((string) ($user['email'] ?? '')) ?></p>
            </div>
        </div>
        <dl class="space-y-3 text-sm">
            <div class="flex items-center justify-between">
                <dt class="text-slate-400">نقش</dt>
                <dd>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $roleName === 'super_admin' ? 'bg-rose-50 text-rose-600' : ($roleName === 'editor' ? 'bg-sky-50 text-sky-600' : 'bg-slate-100 text-slate-500') ?>">
                        <?= e($roleLabels[$roleName] ?? $roleName) ?>
                    </span>
                </dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-slate-400">ورود دومرحله‌ای (2FA)</dt>
                <dd class="<?= $twofaOn ? 'text-emerald-600 font-bold' : 'text-slate-400' ?>"><?= $twofaOn ? 'فعال' : 'غیرفعال' ?></dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-slate-400">آخرین ورود</dt>
                <dd class="text-slate-600"><?= e(format_date((string) ($user['last_login_at'] ?? '')) ?: '—') ?></dd>
            </div>
        </dl>
    </div>

    <div class="xl:col-span-2 space-y-6">
        <!-- Change password -->
        <div class="admin-card p-6">
            <h2 class="text-sm font-bold text-slate-700 mb-1">تغییر رمز عبور</h2>
            <p class="text-xs text-slate-400 mb-5">برای تغییر، ابتدا رمز عبور فعلی را وارد کنید. رمز جدید باید از سیاست رمز عبور عبور کند (در production حداقل ۱۶ کاراکتر).</p>
            <form method="post" action="/admin/account/password" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <?= Csrf::field() ?>
                <div class="md:col-span-2">
                    <label class="admin-label" for="pw_current">رمز عبور فعلی</label>
                    <input type="password" id="pw_current" name="current_password" class="admin-input" dir="ltr"
                           autocomplete="current-password" required>
                </div>
                <div>
                    <label class="admin-label" for="pw_new">رمز عبور جدید</label>
                    <input type="password" id="pw_new" name="new_password" class="admin-input" dir="ltr"
                           autocomplete="new-password" required>
                </div>
                <div>
                    <label class="admin-label" for="pw_new2">تکرار رمز عبور جدید</label>
                    <input type="password" id="pw_new2" name="confirm_password" class="admin-input" dir="ltr"
                           autocomplete="new-password" required>
                </div>
                <div class="md:col-span-2 flex items-center gap-3">
                    <button type="submit" class="admin-btn admin-btn-primary"><i data-lucide="key-round" class="w-4 h-4"></i>تغییر رمز عبور</button>
                </div>
            </form>
        </div>

        <!-- Two-factor authentication -->
        <div class="admin-card p-6">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-sm font-bold text-slate-700">ورود دومرحله‌ای (TOTP)</h2>
                <span class="text-xs px-2.5 py-1 rounded-full font-bold <?= $twofaOn ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' ?>">
                    <?= $twofaOn ? 'فعال' : 'غیرفعال' ?>
                </span>
            </div>
            <p class="text-xs text-slate-400 mb-5">
                <?= $twofaOn
                    ? 'حساب شما با کد ۶ رقمی اپلیکیشن احراز هویت محافظت می‌شود. برای غیرفعال‌سازی، رمز عبور خودتان لازم است.'
                    : 'با فعال‌سازی، بعد از رمز عبور، کد ۶ رقمی (Google Authenticator و مشابه) هم برای ورود لازم می‌شود.' ?>
            </p>
            <?php if ($twofaOn): ?>
                <form method="post" action="/admin/account/2fa/disable" class="flex flex-wrap items-end gap-4">
                    <?= Csrf::field() ?>
                    <div class="grow min-w-[220px]">
                        <label class="admin-label" for="tf_disable_pass">رمز عبور فعلی (تأیید مجدد)</label>
                        <input type="password" id="tf_disable_pass" name="current_password" class="admin-input" dir="ltr"
                               autocomplete="current-password" required>
                    </div>
                    <button type="submit" class="admin-btn text-rose-500 hover:!bg-rose-500/10"><i data-lucide="shield-off" class="w-4 h-4"></i>غیرفعال‌سازی 2FA</button>
                </form>
            <?php else: ?>
                <a href="/admin/account/2fa/setup" class="admin-btn admin-btn-primary"><i data-lucide="shield-plus" class="w-4 h-4"></i>فعال‌سازی 2FA</a>
            <?php endif; ?>
        </div>
    </div>
</div>
