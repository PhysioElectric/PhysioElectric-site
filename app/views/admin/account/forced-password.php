<?php
/**
 * Admin: forced first-login password change.
 * Router blocks every other /admin route while force_password_change=1.
 * Expects: $user
 */
$adminTitle  = 'تغییر رمز عبور الزامی';
$adminActive = '';
?>
<div class="max-w-2xl mx-auto">
    <div class="admin-card p-8">
        <div class="flex items-start gap-4 mb-6">
            <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="shield-alert" class="w-5 h-5"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-800">سلام <?= e((string) ($user['name'] ?? '')) ?> — اول رمز عبور را عوض کنید</h2>
                <p class="text-sm text-slate-500 leading-relaxed mt-1">
                    حساب شما با یک رمز عبور موقت ساخته شده است. تا زمانی که آن را عوض نکنید،
                    دسترسی به سایر بخش‌های پنل ممکن نیست. رمز جدید باید قوی و یکتا باشد
                    (در محیط production حداقل ۱۶ کاراکتر).
                </p>
            </div>
        </div>

        <?php if (!empty($user['email'])): ?>
            <p class="text-xs text-slate-400 mb-6 ltr" dir="ltr"><?= e((string) $user['email']) ?></p>
        <?php endif; ?>

        <form method="post" action="/admin/forced-password" class="space-y-5">
            <?= Csrf::field() ?>
            <div>
                <label class="admin-label" for="fp_new">رمز عبور جدید</label>
                <input type="password" id="fp_new" name="new_password" class="admin-input" dir="ltr"
                       autocomplete="new-password" required>
                <p class="text-xs text-slate-400 mt-1.5">پیشنهاد: <code dir="ltr" class="ltr">openssl rand -base64 24</code> یا ۵–۶ کلمه‌ی تصادفی diceware.</p>
            </div>
            <div>
                <label class="admin-label" for="fp_new2">تکرار رمز عبور جدید</label>
                <input type="password" id="fp_new2" name="confirm_password" class="admin-input" dir="ltr"
                       autocomplete="new-password" required>
            </div>
            <button type="submit" class="admin-btn admin-btn-primary !px-8 !py-3">
                <i data-lucide="check" class="w-4 h-4"></i>
                تغییر رمز عبور و ورود به پنل
            </button>
        </form>
    </div>
</div>
