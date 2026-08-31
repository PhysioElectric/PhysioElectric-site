<?php
/**
 * Admin: team member create / edit.
 * Expects: $member, $formErrors
 */
$adminTitle  = t('admin.team.title');
$adminActive = $adminActive ?? 'team';
$isEdit      = !empty($member['id']);
$action      = $isEdit ? '/admin/team/' . (int) $member['id'] : '/admin/team/create';
?>
<div class="flex items-center justify-between mb-5">
    <a href="/admin/team" class="admin-btn admin-btn-ghost"><i data-lucide="arrow-right" class="w-4 h-4 rtl:rotate-180"></i><?= e(t('admin.team.title')) ?></a>
</div>

<form method="post" action="<?= e($action) ?>" class="space-y-6">
    <?= Csrf::field() ?>

    <div class="admin-card p-6">
        <h2 class="text-sm font-bold text-slate-700 mb-4"><?= e(t('admin.team.info')) ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="admin-label"><?= e(t('admin.team.nameFa')) ?> *</label>
                <input type="text" name="name_fa" class="admin-input" maxlength="120"
                       value="<?= e((string) ($member['name_fa'] ?? '')) ?>">
            </div>
            <div>
                <label class="admin-label"><?= e(t('admin.team.nameEn')) ?></label>
                <input type="text" name="name_en" class="admin-input" dir="ltr" maxlength="120"
                       value="<?= e((string) ($member['name_en'] ?? '')) ?>">
            </div>
            <div>
                <label class="admin-label"><?= e(t('admin.team.roleFa')) ?></label>
                <input type="text" name="role_fa" class="admin-input" maxlength="160"
                       value="<?= e((string) ($member['role_fa'] ?? '')) ?>">
            </div>
            <div>
                <label class="admin-label"><?= e(t('admin.team.roleEn')) ?></label>
                <input type="text" name="role_en" class="admin-input" dir="ltr" maxlength="160"
                       value="<?= e((string) ($member['role_en'] ?? '')) ?>">
            </div>
            <div>
                <label class="admin-label"><?= e(t('admin.team.descFa')) ?></label>
                <textarea name="desc_fa" class="admin-textarea" rows="3" maxlength="600"><?= e((string) ($member['desc_fa'] ?? '')) ?></textarea>
            </div>
            <div>
                <label class="admin-label"><?= e(t('admin.team.descEn')) ?></label>
                <textarea name="desc_en" class="admin-textarea" rows="3" maxlength="600" dir="ltr"><?= e((string) ($member['desc_en'] ?? '')) ?></textarea>
            </div>
            <div>
                <label class="admin-label"><?= e(t('admin.team.order')) ?></label>
                <input type="number" name="sort_order" class="admin-input" dir="ltr"
                       value="<?= (int) ($member['sort_order'] ?? 0) ?>">
            </div>
        </div>
    </div>

    <div class="admin-card p-6">
        <h2 class="text-sm font-bold text-slate-700 mb-4"><?= e(t('admin.team.photo')) ?></h2>
        <div class="pe-image-field" data-field="image">
            <input type="hidden" name="image" value="<?= e((string) ($member['image'] ?? '')) ?>">
            <div class="flex items-center gap-3">
                <div class="w-20 h-24 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0" data-preview>
                    <?php if (!empty($member['image'])): ?>
                        <img src="<?= e((string) $member['image']) ?>" alt="" class="w-full h-full object-cover" data-preview-img>
                    <?php else: ?>
                        <i data-lucide="image" class="w-6 h-6 text-slate-300" data-preview-icon></i>
                    <?php endif; ?>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="admin-btn admin-btn-ghost !py-1.5 !px-3 !text-xs cursor-pointer">
                        <i data-lucide="upload" class="w-3.5 h-3.5"></i><?= e(t('admin.uploadImage')) ?>
                        <input type="file" accept="image/jpeg,image/png,image/webp" data-file-input class="hidden">
                    </label>
                    <div class="flex gap-2">
                        <button type="button" class="text-[11px] font-semibold text-physio-600 hover:text-physio-500" data-media-toggle><?= e(t('admin.mediaLibrary')) ?></button>
                        <button type="button" class="text-[11px] font-semibold text-rose-500 hover:text-rose-400 hidden" data-remove-image><?= e(t('admin.removeImage')) ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="admin-btn admin-btn-primary"><i data-lucide="save" class="w-4 h-4"></i><?= e(t('admin.save')) ?></button>
        <a href="/admin/team" class="admin-btn admin-btn-ghost"><?= e(t('admin.cancel')) ?></a>
    </div>
</form>
