<?php
/**
 * Admin: team members list.
 * Expects: $members
 */
$adminTitle  = t('admin.team.title');
$adminActive = $adminActive ?? 'team';
?>
<?php if (!empty($schemaMissing)): ?>
    <div class="mb-5 rounded-lg border border-amber-300 bg-amber-50 text-amber-800 text-sm p-3 leading-relaxed">
        <?= e(t('admin.schemaMissing')) ?>
    </div>
<?php endif; ?>
<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-slate-500"><?= e(t('admin.perPage')) ?> <b><?= count($members) ?></b> <?= e(t('admin.team.members')) ?></p>
    <?php if (admin_can_edit()): ?>
        <a href="/admin/team/create" class="admin-btn admin-btn-primary"><i data-lucide="user-plus" class="w-4 h-4"></i><?= e(t('admin.team.new')) ?></a>
    <?php endif; ?>
</div>

<div class="admin-card overflow-x-auto">
    <?php if (empty($members)): ?>
        <p class="p-10 text-sm text-slate-400 text-center"><?= e(t('admin.team.empty')) ?></p>
    <?php else: ?>
        <table class="admin-table min-w-[720px]">
            <thead>
                <tr>
                    <th><?= e(t('admin.team.photo')) ?></th>
                    <th><?= e(t('admin.team.name')) ?></th>
                    <th><?= e(t('admin.team.role')) ?></th>
                    <th><?= e(t('admin.team.order')) ?></th>
                    <th class="text-end"><?= e(t('admin.actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $m): ?>
                    <tr>
                        <td>
                            <?php if ((string) $m['image'] !== ''): ?>
                                <img src="<?= e((string) $m['image']) ?>" alt="" class="w-12 h-12 rounded-lg object-cover ring-1 ring-slate-200" loading="lazy">
                            <?php else: ?>
                                <span class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center text-slate-300"><i data-lucide="image" class="w-5 h-5"></i></span>
                            <?php endif; ?>
                        </td>
                        <td class="font-medium text-slate-700">
                            <span class="block"><?= e((string) $m['name_fa']) ?></span>
                            <span class="block text-xs text-slate-400 ltr" dir="ltr"><?= e((string) $m['name_en']) ?></span>
                        </td>
                        <td class="text-slate-500 text-sm"><?= e((string) $m['role_fa']) ?></td>
                        <td class="text-slate-500"><?= (int) $m['sort_order'] ?></td>
                        <td class="text-end">
                            <?php if (admin_can_edit()): ?>
                            <div class="flex items-center justify-end gap-2">
                                <a href="/admin/team/<?= (int) $m['id'] ?>/edit" class="admin-btn admin-btn-ghost"><i data-lucide="pencil" class="w-4 h-4"></i><?= e(t('admin.edit')) ?></a>
                                <form method="post" action="/admin/team/delete" onsubmit="return confirm('<?= e(t('admin.team.confirmDelete')) ?>');">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                    <button type="submit" class="admin-btn text-rose-500 hover:!bg-rose-500/10"><i data-lucide="trash-2" class="w-4 h-4"></i><?= e(t('admin.delete')) ?></button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
