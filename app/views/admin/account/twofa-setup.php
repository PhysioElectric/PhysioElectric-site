<?php
/**
 * Admin: TOTP setup — shows the secret + otpauth:// URI and asks for a
 * confirming 6-digit code (plus the account password for re-auth).
 * Expects: $user, $secret, $otpauth
 */
$adminTitle  = 'فعال‌سازی ورود دومرحله‌ای';
$adminActive = 'account';
?>
<div class="max-w-2xl mx-auto">
    <div class="admin-card p-8">
        <div class="flex items-start gap-4 mb-6">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="shield-plus" class="w-5 h-5"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-800">فعال‌سازی ورود دومرحله‌ای</h2>
                <p class="text-sm text-slate-500 leading-relaxed mt-1">
                    در اپلیکیشن احراز هویت (Google Authenticator، Aegis، 1Password و مشابه) یک
                    حساب جدید با کلید زیر بسازید، سپس کد ۶ رقمی تولیدشده را برای تأیید وارد کنید.
                </p>
            </div>
        </div>

        <!-- Secret -->
        <div class="bg-slate-900 rounded-xl p-5 mb-5">
            <p class="text-[11px] text-slate-400 mb-2">کلید محرمانه (Secret) — فقط همین‌جا نمایش داده می‌شود</p>
            <p dir="ltr" class="text-center font-mono text-lg tracking-[0.2em] text-emerald-300 break-all select-all"><?= e($secret) ?></p>
        </div>

        <div class="bg-slate-50 rounded-xl p-5 mb-6">
            <p class="text-[11px] text-slate-400 mb-2">otpauth URI (برای اپلیکیشن‌هایی که اسکن QR ندارند)</p>
            <p dir="ltr" class="text-xs font-mono text-slate-600 break-all select-all"><?= e($otpauth) ?></p>
        </div>

        <form method="post" action="/admin/account/2fa/setup" class="space-y-5">
            <?= Csrf::field() ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="admin-label" for="t2fa_code">کد ۶ رقمی اپلیکیشن</label>
                    <input type="text" id="t2fa_code" name="code" class="admin-input text-center tracking-[0.4em] font-mono" dir="ltr"
                           placeholder="••••••" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required>
                </div>
                <div>
                    <label class="admin-label" for="t2fa_pass">رمز عبور فعلی (تأیید مجدد)</label>
                    <input type="password" id="t2fa_pass" name="current_password" class="admin-input" dir="ltr"
                           autocomplete="current-password" required>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="admin-btn admin-btn-primary"><i data-lucide="shield-check" class="w-4 h-4"></i>تأیید و فعال‌سازی</button>
                <a href="/admin/account" class="admin-btn admin-btn-ghost">انصراف</a>
            </div>
        </form>
    </div>
</div>
