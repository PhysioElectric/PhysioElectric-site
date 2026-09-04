<?php
/**
 * Admin: create / edit admin user. Super_admin only (route gate in
 * index.php). Create: name/email/role + initial password. Edit: name /
 * role / active state only — never another user's password, and never the
 * actor's own role or active state.
 * Expects: $user, $isEdit, $roleLabels, $isSelf
 */
$adminTitle  = $isEdit ? 'ویرایش کاربر' : 'ساخت ادمین جدید';
$adminActive = 'users';
$action      = $isEdit ? '/admin/users/' . (int) $user['id'] : '/admin/users/create';
$id          = (int) ($user['id'] ?? 0);
$role        = (string) ($user['role'] ?? 'editor');
$active      = (int) ($user['is_active'] ?? 1) === 1;
?>
<div class="flex items-center justify-between mb-5">
    <a href="/admin/users" class="admin-btn admin-btn-ghost"><i data-lucide="arrow-right" class="w-4 h-4 rtl:rotate-180"></i>مدیریت ادمین‌ها</a>
    <?php if ($isEdit): ?>
        <span class="text-xs text-slate-400 ltr" dir="ltr">#<?= $id ?> · <?= e((string) ($user['email'] ?? '')) ?></span>
    <?php endif; ?>
</div>

<form method="post" action="<?= e($action) ?>" class="max-w-3xl space-y-6">
    <?= Csrf::field() ?>

    <div class="admin-card p-6">
        <h2 class="text-sm font-bold text-slate-700 mb-5"><?= $isEdit ? 'مشخصات کاربر' : 'مشخصات ادمین جدید' ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="admin-label" for="u_name">نام</label>
                <input type="text" id="u_name" name="name" class="admin-input" maxlength="120"
                       value="<?= e((string) ($user['name'] ?? '')) ?>" required>
            </div>
            <div>
                <label class="admin-label" for="u_email">ایمیل</label>
                <?php if ($isEdit): ?>
                    <input type="text" id="u_email" class="admin-input bg-slate-50 text-slate-400" dir="ltr"
                           value="<?= e((string) ($user['email'] ?? '')) ?>" disabled>
                    <p class="text-xs text-slate-400 mt-1">ایمیل پس از ساخت قابل تغییر نیست.</p>
                <?php else: ?>
                    <input type="email" id="u_email" name="email" class="admin-input" dir="ltr" maxlength="190"
                           value="<?= e((string) ($user['email'] ?? '')) ?>" autocomplete="off" required>
                <?php endif; ?>
            </div>
            <div>
                <label class="admin-label" for="u_role">نقش</label>
                <?php if ($isSelf): ?>
                    <input type="text" class="admin-input bg-slate-50 text-slate-400" value="<?= e($roleLabels[$role] ?? $role) ?>" disabled>
                    <p class="text-xs text-slate-400 mt-1">تغییر نقش خودتان مجاز نیست.</p>
                <?php else: ?>
                    <select id="u_role" name="role" class="admin-select">
                        <?php foreach ($roleLabels as $val => $label): ?>
                            <option value="<?= e($val) ?>" <?= $role === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div>
                <label class="admin-label">نقش‌ها</label>
                <div class="text-xs text-slate-500 leading-relaxed bg-slate-50 rounded-lg p-3">
                    <b>super_admin</b>: مدیریت کاربران + همه‌چیز ·
                    <b>editor</b>: پست/پروژه/تیم/پیام‌ها ·
                    <b>viewer</b>: فقط مشاهده
                </div>
            </div>
        </div>
    </div>

    <?php if (!$isEdit): ?>
        <div class="admin-card p-6">
            <h2 class="text-sm font-bold text-slate-700 mb-2">رمز عبور اولیه</h2>
            <p class="text-xs text-slate-400 mb-5 leading-relaxed">
                رمز عبور را <b>شما برای او تعیین می‌کنید</b> و او در اولین ورود <b>مجبور به تغییر آن</b> خواهد شد
                (force_password_change). رمز باید از سیاست مشترک عبور کند — در production حداقل ۱۶ کاراکتر؛
                برای رمزهای کوتاه‌تر از ۲۰ کاراکتر، ترکیب ۳ از ۴ دسته‌ی حرف بزرگ/کوچک/عدد/نماد لازم است.
                از همین‌جا رمز کاربران دیگر را به‌هیچ‌وجه نمی‌توان تغییر داد.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="admin-label" for="u_pass">رمز عبور اولیه</label>
                    <input type="password" id="u_pass" name="password" class="admin-input" dir="ltr"
                           autocomplete="new-password" required>
                </div>
                <div class="flex items-end">
                    <p class="text-xs text-slate-400">پیشنهاد تولید: <code dir="ltr" class="ltr">openssl rand -base64 24</code></p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="admin-card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-700">وضعیت حساب</h2>
                    <p class="text-xs text-slate-400 mt-1">غیرفعال‌سازی = soft-delete (حذف فیزیکی هیچ‌وقت انجام نمی‌شود).</p>
                </div>
                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="is_active" value="1" class="accent-physio-600 w-4 h-4"
                           <?= $active ? 'checked' : '' ?> <?= $isSelf ? 'disabled' : '' ?>>
                    <span class="text-sm font-medium text-slate-700">حساب فعال است</span>
                </label>
            </div>
            <?php if ($isSelf): ?>
                <p class="text-xs text-rose-500 mt-2">غیرفعال‌سازی حساب خودتان مجاز نیست.</p>
            <?php endif; ?>
            <?php if ((int) ($user['force_password_change'] ?? 0) === 1): ?>
                <p class="text-xs text-amber-600 mt-2">این کاربر در ورود بعدی ملزم به تغییر رمز عبور است.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="admin-card p-6 border-amber-200 !bg-amber-50/50">
        <label class="admin-label" for="u_current">رمز عبور خودتان (تأیید مجدد عملیات)</label>
        <input type="password" id="u_current" name="current_password" class="admin-input max-w-md" dir="ltr"
               autocomplete="current-password" required>
        <p class="text-xs text-slate-500 mt-1.5">هر عملیات حساس روی کاربران فقط پس از تأیید رمز عبور خودِ شما اجرا می‌شود.</p>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="admin-btn admin-btn-primary !px-8 !py-3">
            <i data-lucide="save" class="w-4 h-4"></i>
            <?= $isEdit ? 'ذخیرهٔ تغییرات' : 'ساخت کاربر' ?>
        </button>
        <a href="/admin/users" class="admin-btn admin-btn-ghost">انصراف</a>
    </div>
</form>
