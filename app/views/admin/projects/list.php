<?php
/**
 * Admin: projects list.
 * Expects: $projects
 */
$adminTitle  = t('admin.projects');
$adminActive = 'projects';
?>
<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-slate-500"><?= e(t('admin.perPage')) ?> <b><?= count($projects) ?></b> <?= e(t('admin.of')) ?> <?= e(t('admin.projects')) ?></p>
    <?php if (admin_can_edit()): ?>
        <a href="/admin/projects/create" class="admin-btn admin-btn-primary"><i data-lucide="plus" class="w-4 h-4"></i><?= e(t('admin.newProject')) ?></a>
    <?php endif; ?>
</div>

<div class="admin-card overflow-x-auto">
    <?php if (empty($projects)): ?>
        <p class="p-10 text-sm text-slate-400 text-center"><?= e(t('admin.noRows')) ?></p>
    <?php else: ?>
        <table class="admin-table min-w-[760px]">
            <thead>
                <tr>
                    <th><?= e(t('admin.title')) ?></th>
                    <th><?= e(t('admin.category')) ?></th>
                    <th><?= e(t('admin.status')) ?></th>
                    <th><?= e(t('admin.date')) ?></th>
                    <th class="text-end"><?= e(t('admin.actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $p): ?>
                    <tr>
                        <td class="font-medium text-slate-700">
                            <span class="block max-w-[300px] truncate" title="<?= e((string) $p['title_fa']) ?>"><?= e((string) $p['title_fa']) ?></span>
                            <span class="block text-xs text-slate-400 max-w-[300px] truncate" dir="ltr"><?= e((string) $p['title_en']) ?></span>
                        </td>
                        <td class="text-xs text-slate-500 whitespace-nowrap"><?= e((string) ($p['category_name_fa'] ?? '')) ?></td>
                        <td>
                            <?php if ($p['status'] === 'published'): ?>
                                <span class="badge badge-green"><?= e(t('admin.published')) ?></span>
                            <?php else: ?>
                                <span class="badge badge-amber"><?= e(t('admin.draft')) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-slate-400 text-xs whitespace-nowrap"><?= e(format_date((string) $p['created_at'])) ?></td>
                        <td class="text-end whitespace-nowrap">
                            <?php if (admin_can_edit()): ?>
                            <a href="/admin/projects/<?= (int) $p['id'] ?>/edit" class="admin-btn admin-btn-ghost !py-1.5 !px-3 !text-xs me-2">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i><?= e(t('admin.edit')) ?>
                            </a>
                            <form method="post" action="/admin/projects/delete" class="inline" onsubmit="return confirm('<?= e(t('admin.confirmDelete')) ?>');">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                <button type="submit" class="admin-btn admin-btn-danger !py-1.5 !px-3 !text-xs">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i><?= e(t('admin.delete')) ?>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
