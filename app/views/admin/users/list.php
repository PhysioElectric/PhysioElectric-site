<?php
/**
 * Admin: user accounts list ("مدیریت ادمین‌ها") — super_admin only
 * (route gate lives in index.php). Expects: $users, $roleLabels.
 */
$adminTitle  = 'مدیریت ادمین‌ها';
$adminActive = $adminActive ?? 'users';
?>
<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-slate-500"><b><?= count($users) ?></b> کاربر</p>
    <a href="/admin/users/create" class="admin-btn admin-btn-primary"><i data-lucide="user-plus" class="w-4 h-4"></i>ساخت ادمین جدید</a>
</div>

<div class="admin-card overflow-x-auto">
    <table class="admin-table min-w-[860px]">
        <thead>
            <tr>
                <th>کاربر</th>
                <th>نقش</th>
                <th>وضعیت</th>
                <th>آخرین ورود</th>
                <th>ساخته‌شده توسط</th>
                <th class="text-end">عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <?php
                $role   = (string) ($u['role'] ?? '');
                $active = (int) ($u['is_active'] ?? 0) === 1;
                $forced = (int) ($u['force_password_change'] ?? 0) === 1;
                $isSelf = (int) $u['id'] === Auth::userId();
                ?>
                <tr>
                    <td>
                        <span class="block font-medium text-slate-700">
                            <?= e((string) $u['name']) ?>
                            <?php if ($isSelf): ?><span class="text-[10px] text-slate-400 font-normal">(شما)</span><?php endif; ?>
                        </span>
                        <span class="block text-xs text-slate-400 ltr" dir="ltr"><?= e((string) $u['email']) ?></span>
                    </td>
                    <td>
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold <?= $role === 'super_admin' ? 'bg-rose-50 text-rose-600' : ($role === 'editor' ? 'bg-sky-50 text-sky-600' : 'bg-slate-100 text-slate-500') ?>">
                            <?= e($roleLabels[$role] ?? $role) ?>
                        </span>
                    </td>
                    <td>
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold <?= $active ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-500' ?>">
                            <?= $active ? 'فعال' : 'غیرفعال' ?>
                        </span>
                        <?php if ($forced): ?>
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-600 mt-1" title="باید در اولین ورود رمز را عوض کند">تغییر رمز اجباری</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-slate-500 text-sm whitespace-nowrap"><?= e(format_date((string) ($u['last_login_at'] ?? '')) ?: '—') ?></td>
                    <td class="text-slate-500 text-sm">
                        <?php if ($isSelf): ?>
                            <span class="text-slate-300">—</span>
                        <?php else: ?>
                            <?= e((string) ($u['created_by_name'] ?? '') ?: '—') ?>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <div class="flex items-center justify-end gap-2">
                            <?php if (!$isSelf && $active): ?>
                                <form method="post" action="/admin/users/toggle" class="inline">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                    <button type="submit" class="admin-btn text-amber-600 hover:!bg-amber-500/10" title="غیرفعال‌سازی (soft-delete)"><i data-lucide="user-x" class="w-4 h-4"></i>غیرفعال</button>
                                </form>
                            <?php elseif (!$isSelf): ?>
                                <form method="post" action="/admin/users/toggle" class="inline">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                    <button type="submit" class="admin-btn text-emerald-600 hover:!bg-emerald-500/10"><i data-lucide="user-check" class="w-4 h-4"></i>فعال‌سازی</button>
                                </form>
                            <?php endif; ?>
                            <a href="/admin/users/<?= (int) $u['id'] ?>/edit" class="admin-btn admin-btn-ghost"><i data-lucide="pencil" class="w-4 h-4"></i>ویرایش</a>
                            <?php if (!$isSelf): ?>
                                <form method="post" action="/admin/users/delete" class="inline" onsubmit="return confirm('<?= e(t('admin.confirmDelete')) ?>');">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                    <button type="submit" class="admin-btn text-rose-500 hover:!bg-rose-500/10"><i data-lucide="trash-2" class="w-4 h-4"></i>حذف</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="mt-4 text-xs text-slate-400 leading-relaxed max-w-3xl">
    <p class="font-bold mb-1">قوانین حفاظتی:</p>
    <ul class="list-disc pr-5 space-y-0.5">
        <li>هیچ‌کس (حتی super_admin) نمی‌تواند حساب خودش را غیرفعال/حذف کند یا نقش خودش را عوض کند.</li>
        <li>آخرین super_admin فعالِ سیستم هرگز قابل غیرفعال‌سازی، حذف یا تنزل نقش نیست.</li>
        <li>عملیات حساس فقط با رمز عبور خودِ فرد اجراکننده (تأیید مجدد) انجام می‌شود.</li>
        <li>رمز عبور هر کاربر فقط توسط خودِ او از مسیر «حساب من» تغییر می‌کند؛ غیرفعال‌سازی به‌جای حذف فیزیکی انجام می‌شود.</li>
    </ul>
</div>
