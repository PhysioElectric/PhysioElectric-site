<?php
/**
 * Admin: posts list.
 * Expects: $posts
 */
$adminTitle  = t('admin.posts');
$adminActive = 'posts';
?>
<div class="flex items-center justify-between mb-5">
    <p class="text-sm text-slate-500"><?= e(t('admin.perPage')) ?> <b><?= count($posts) ?></b> <?= e(t('admin.of')) ?> <?= e(t('admin.posts')) ?></p>
    <a href="/admin/posts/create" class="admin-btn admin-btn-primary"><i data-lucide="plus" class="w-4 h-4"></i><?= e(t('admin.newPost')) ?></a>
</div>

<div class="admin-card overflow-x-auto">
    <?php if (empty($posts)): ?>
        <p class="p-10 text-sm text-slate-400 text-center"><?= e(t('admin.noRows')) ?></p>
    <?php else: ?>
        <table class="admin-table min-w-[720px]">
            <thead>
                <tr>
                    <th><?= e(t('admin.title')) ?></th>
                    <th><?= e(t('admin.slug')) ?></th>
                    <th><?= e(t('admin.status')) ?></th>
                    <th><?= e(t('admin.date')) ?></th>
                    <th class="text-end"><?= e(t('admin.actions')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td class="font-medium text-slate-700">
                            <span class="block max-w-[320px] truncate" title="<?= e((string) $post['title_fa']) ?>"><?= e((string) $post['title_fa']) ?></span>
                            <span class="block text-xs text-slate-400 max-w-[320px] truncate ltr" dir="ltr"><?= e((string) $post['title_en']) ?></span>
                        </td>
                        <td dir="ltr" class="text-slate-500 text-xs font-mono truncate max-w-[180px]">/fa/blog/<?= e((string) $post['slug_fa']) ?></td>
                        <td>
                            <?php if ($post['status'] === 'published'): ?>
                                <span class="badge badge-green"><?= e(t('admin.published')) ?></span>
                            <?php else: ?>
                                <span class="badge badge-amber"><?= e(t('admin.draft')) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-slate-400 text-xs whitespace-nowrap"><?= e(format_date((string) ($post['published_at'] ?? $post['created_at']))) ?></td>
                        <td class="text-end whitespace-nowrap">
                            <a href="/admin/posts/<?= (int) $post['id'] ?>/edit" class="admin-btn admin-btn-ghost !py-1.5 !px-3 !text-xs me-2">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i><?= e(t('admin.edit')) ?>
                            </a>
                            <form method="post" action="/admin/posts/delete" class="inline" onsubmit="return confirm('<?= e(t('admin.confirmDelete')) ?>');">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                                <button type="submit" class="admin-btn admin-btn-danger !py-1.5 !px-3 !text-xs">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i><?= e(t('admin.delete')) ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
