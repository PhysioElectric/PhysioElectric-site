<?php
/**
 * Admin dashboard.
 * Expects: $stats, $recentPosts, $recentProjects
 */
$adminTitle  = t('admin.dashboard');
$adminActive = 'dashboard';
$cards = [
    ['icon' => 'file-text',  'label' => t('admin.stat.posts'),     'value' => $stats['posts'],     'to' => '/admin/posts',     'color' => 'from-sky-500 to-blue-600'],
    ['icon' => 'folder-git2','label' => t('admin.stat.projects'),  'value' => $stats['projects'],  'to' => '/admin/projects',  'color' => 'from-indigo-500 to-violet-600'],
    ['icon' => 'globe',      'label' => t('admin.stat.published'), 'value' => $stats['published'], 'to' => '/',                'color' => 'from-emerald-500 to-teal-600'],
    ['icon' => 'image',      'label' => t('admin.stat.uploads'),   'value' => $stats['uploads'],   'to' => '#',                'color' => 'from-amber-500 to-orange-600'],
];
?>

<!-- Stats -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
    <?php foreach ($cards as $c): ?>
        <a href="<?= e($c['to']) ?>" class="admin-card p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br <?= e($c['color']) ?> text-white flex items-center justify-center shrink-0">
                <i data-lucide="<?= e($c['icon']) ?>" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900 leading-tight"><?= e((string) $c['value']) ?></p>
                <p class="text-xs text-slate-500 font-medium mt-0.5"><?= e($c['label']) ?></p>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<!-- Quick actions -->
<div class="admin-card mt-6 p-5">
    <h2 class="text-sm font-bold text-slate-700 mb-4"><?= e(t('admin.quick')) ?></h2>
    <div class="flex flex-wrap gap-3">
        <a href="/admin/posts/create" class="admin-btn admin-btn-primary"><i data-lucide="plus" class="w-4 h-4"></i><?= e(t('admin.newPost')) ?></a>
        <a href="/admin/projects/create" class="admin-btn admin-btn-primary"><i data-lucide="plus" class="w-4 h-4"></i><?= e(t('admin.newProject')) ?></a>
        <a href="/admin/team" class="admin-btn admin-btn-ghost"><i data-lucide="users" class="w-4 h-4"></i><?= e(t('admin.team.title')) ?></a>
        <a href="/admin/messages" class="admin-btn admin-btn-ghost"><i data-lucide="inbox" class="w-4 h-4"></i><?= e(t('admin.msg.title')) ?></a>
        <a href="/" class="admin-btn admin-btn-ghost"><i data-lucide="external-link" class="w-4 h-4"></i><?= e(t('admin.viewSite')) ?></a>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6">
    <!-- Recent posts -->
    <div class="admin-card overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-700"><?= e(t('admin.recent')) ?> — <?= e(t('admin.posts')) ?></h2>
            <a href="/admin/posts" class="text-xs font-semibold text-physio-600 hover:text-physio-500"><?= e(t('admin.edit')) ?> ←</a>
        </div>
        <?php if (empty($recentPosts)): ?>
            <p class="p-6 text-sm text-slate-400 text-center"><?= e(t('admin.noRows')) ?></p>
        <?php else: ?>
            <table class="admin-table">
                <tbody>
                    <?php foreach ($recentPosts as $post): ?>
                        <tr>
                            <td class="font-medium text-slate-700"><?= e((string) $post['title_fa']) ?></td>
                            <td>
                                <?php if ($post['status'] === 'published'): ?>
                                    <span class="badge badge-green"><?= e(t('admin.published')) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-amber"><?= e(t('admin.draft')) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-slate-400 text-xs whitespace-nowrap"><?= e(format_date((string) $post['created_at'])) ?></td>
                            <td class="text-end">
                                <a href="/admin/posts/<?= (int) $post['id'] ?>/edit" class="text-xs font-semibold text-physio-600 hover:text-physio-500"><?= e(t('admin.edit')) ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Recent projects -->
    <div class="admin-card overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-700"><?= e(t('admin.recent')) ?> — <?= e(t('admin.projects')) ?></h2>
            <a href="/admin/projects" class="text-xs font-semibold text-physio-600 hover:text-physio-500"><?= e(t('admin.edit')) ?> ←</a>
        </div>
        <?php if (empty($recentProjects)): ?>
            <p class="p-6 text-sm text-slate-400 text-center"><?= e(t('admin.noRows')) ?></p>
        <?php else: ?>
            <table class="admin-table">
                <tbody>
                    <?php foreach ($recentProjects as $p): ?>
                        <tr>
                            <td class="font-medium text-slate-700"><?= e((string) $p['title_fa']) ?></td>
                            <td class="text-xs text-slate-500"><?= e((string) ($p['category_name_fa'] ?? '')) ?></td>
                            <td>
                                <?php if ($p['status'] === 'published'): ?>
                                    <span class="badge badge-green"><?= e(t('admin.published')) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-amber"><?= e(t('admin.draft')) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/admin/projects/<?= (int) $p['id'] ?>/edit" class="text-xs font-semibold text-physio-600 hover:text-physio-500"><?= e(t('admin.edit')) ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
