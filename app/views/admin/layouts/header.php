<?php
/**
 * Admin layout header (Persian UI, RTL).
 */
$adminActive    = $adminActive ?? '';
$adminUser      = Auth::check() ? Auth::userName() : '';
$unreadMessages = Auth::check() ? MessageModel::unreadCount() : 0;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= e(t('admin.title')) ?> | <?= e(setting('site_name', 'PhysioElectric')) ?></title>
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="/assets/fonts/fonts.css">
<script src="/assets/js/tailwind.js"></script>
<script nonce="<?= e(\Security::nonce()) ?>">
tailwind.config = {
    theme: {
        extend: {
            fontFamily: { sans: ['Inter', 'Vazirmatn', 'sans-serif'] },
            colors: {
                physio: {
                    50: '#f0f9ff', 100: '#e0f2fe', 200: '#bae6fd', 300: '#7dd3fc',
                    400: '#38bdf8', 500: '#0ea5e9', 600: '#0284c7', 900: '#0f172a', 950: '#020617',
                }
            }
        }
    }
}
</script>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="antialiased font-sans bg-slate-100 text-slate-900">
<div class="admin-shell">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="h-16 flex items-center gap-2.5 px-5 border-b border-slate-800/70">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-physio-500 to-physio-900 flex items-center justify-center text-white font-bold text-lg">P</div>
            <div>
                <p class="text-sm font-bold text-white leading-tight"><?= e(setting('site_name', 'PhysioElectric')) ?></p>
                <p class="text-[10px] text-slate-500 uppercase tracking-wider"><?= e(t('admin.title')) ?></p>
            </div>
        </div>

        <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">
            <a href="/admin/dashboard" class="admin-navlink <?= $adminActive === 'dashboard' ? 'active' : '' ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <?= e(t('admin.dashboard')) ?>
            </a>
            <a href="/admin/posts" class="admin-navlink <?= $adminActive === 'posts' ? 'active' : '' ?>">
                <i data-lucide="file-text" class="w-5 h-5"></i>
                <?= e(t('admin.posts')) ?>
            </a>
            <a href="/admin/projects" class="admin-navlink <?= $adminActive === 'projects' ? 'active' : '' ?>">
                <i data-lucide="folder-git2" class="w-5 h-5"></i>
                <?= e(t('admin.projects')) ?>
            </a>
            <a href="/admin/team" class="admin-navlink <?= $adminActive === 'team' ? 'active' : '' ?>">
                <i data-lucide="users" class="w-5 h-5"></i>
                <?= e(t('admin.team.title')) ?>
            </a>
            <a href="/admin/messages" class="admin-navlink <?= $adminActive === 'messages' ? 'active' : '' ?>">
                <i data-lucide="inbox" class="w-5 h-5"></i>
                <?= e(t('admin.msg.title')) ?>
                <?php if ($unreadMessages > 0): ?>
                    <span class="ms-auto inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-physio-500 text-white text-[11px] font-bold"><?= (int) $unreadMessages ?></span>
                <?php endif; ?>
            </a>
            <?php if (Auth::hasRole('super_admin')): ?>
                <a href="/admin/users" class="admin-navlink <?= $adminActive === 'users' ? 'active' : '' ?>">
                    <i data-lucide="shield" class="w-5 h-5"></i>
                    <?= e(t('admin.users')) ?>
                </a>
            <?php endif; ?>
        </nav>

        <div class="px-3 py-4 border-t border-slate-800/70 space-y-1">
            <a href="/admin/account" class="admin-navlink <?= $adminActive === 'account' ? 'active' : '' ?>">
                <i data-lucide="user-circle" class="w-5 h-5"></i>
                <?= e(t('admin.account')) ?>
            </a>
            <a href="/" class="admin-navlink">
                <i data-lucide="external-link" class="w-5 h-5"></i>
                <?= e(t('admin.viewSite')) ?>
            </a>
            <form method="post" action="/admin/logout">
                <?= Csrf::field() ?>
                <button type="submit" class="admin-navlink w-full text-rose-400 hover:!bg-rose-500/10">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                    <?= e(t('admin.logout')) ?>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main -->
    <main class="flex-1 min-w-0">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 sticky top-0 z-40">
            <h1 class="text-base font-bold text-slate-800"><?= e($adminTitle ?? t('admin.dashboard')) ?></h1>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-500"><?= e(t('admin.welcome', ['name' => $adminUser])) ?></span>
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-physio-500 to-physio-900 text-white flex items-center justify-center text-sm font-bold">
                    <?= e(mb_strtoupper(mb_substr($adminUser !== '' ? $adminUser : 'A', 0, 1))) ?>
                </div>
            </div>
        </header>

        <div class="p-6">
            <?php if ($flash = pop_flash()): ?>
                <div class="flash-banner <?= $flash['type'] === 'success' ? 'flash-success' : 'flash-error' ?>">
                    <i data-lucide="<?= $flash['type'] === 'success' ? 'check-circle-2' : 'alert-triangle' ?>" class="w-5 h-5 shrink-0"></i>
                    <?= e($flash['msg']) ?>
                </div>
            <?php endif; ?>
