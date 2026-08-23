<?php
/**
 * Public layout <head> + navbar.
 * Expects: $seo (array from seo_head), $lang available via lang().
 */
$lang        = lang();
$dir         = $lang === 'fa' ? 'rtl' : 'ltr';
$seo         = $seo ?? [];
$currentPath = (string) ($seo['url'] ?? url($lang));
$altPath     = preg_replace('#^/' . $lang . '(?=/|$)#', '/' . altLang(), $currentPath);
if ($altPath === $currentPath) {
    $altPath = '/' . altLang() . $currentPath;
}
$activeNav   = basename((string) $currentPath); // home|projects|blog|about|contact
?>
<!-- Navbar -->
<nav id="navbar" class="fixed w-full z-50 glass-nav transition-all duration-300 py-4">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 flex items-center justify-between">
        <!-- Logo -->
        <a href="<?= e(url($lang)) ?>" class="flex items-center gap-2 group" aria-label="<?= e(setting('site_name', 'PhysioElectric')) ?>">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-physio-500 to-physio-900 flex items-center justify-center text-white font-bold text-xl shadow-glow group-hover:scale-105 transition-transform">
                P
            </div>
            <span class="text-xl font-bold tracking-tight text-physio-900"><?= e(setting('site_name', 'PhysioElectric')) ?></span>
        </a>

        <!-- Desktop Menu -->
        <div class="hidden md:flex items-center gap-8">
            <a href="<?= e(url($lang)) ?>" class="text-sm font-medium <?= $activeNav === 'home' || $currentPath === url($lang) ? 'text-physio-600' : 'text-slate-600 hover:text-physio-600' ?> transition-colors"><?= e(t('nav.home')) ?></a>
            <a href="<?= e(url($lang, 'projects')) ?>" class="text-sm font-medium <?= $activeNav === 'projects' ? 'text-physio-600' : 'text-slate-600 hover:text-physio-600' ?> transition-colors"><?= e(t('nav.projects')) ?></a>
            <a href="<?= e(url($lang, 'blog')) ?>" class="text-sm font-medium <?= $activeNav === 'blog' ? 'text-physio-600' : 'text-slate-600 hover:text-physio-600' ?> transition-colors"><?= e(t('nav.blog')) ?></a>
            <a href="<?= e(url($lang, 'about')) ?>" class="text-sm font-medium <?= $activeNav === 'about' ? 'text-physio-600' : 'text-slate-600 hover:text-physio-600' ?> transition-colors"><?= e(t('nav.about')) ?></a>
            <a href="<?= e(url($lang, 'contact')) ?>" class="text-sm font-medium <?= $activeNav === 'contact' ? 'text-physio-600' : 'text-slate-600 hover:text-physio-600' ?> transition-colors"><?= e(t('nav.contact')) ?></a>
        </div>

        <!-- Right Actions -->
        <div class="flex items-center gap-4">
            <!-- Language Switcher -->
            <a href="<?= e($altPath) ?>" class="text-sm font-medium text-slate-500 hover:text-physio-900 transition-colors flex items-center gap-1" lang="<?= e(altLang()) ?>" aria-label="Switch language">
                <i data-lucide="globe" class="w-4 h-4"></i>
                <span><?= e(t('lang.switch')) ?></span>
            </a>

            <a href="<?= e(url($lang, 'contact')) ?>" class="hidden sm:inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white bg-physio-900 hover:bg-physio-950 rounded-full shadow-sm hover:shadow-md transition-all hover:-translate-y-0.5">
                <?= e(t('nav.cta')) ?>
            </a>

            <!-- Mobile Menu Button -->
            <button class="md:hidden text-slate-600" id="mobileMenuBtn" aria-label="Menu">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="md:hidden absolute top-full inset-x-0 glass-nav border-b border-slate-100">
        <div class="px-6 py-4 flex flex-col gap-3">
            <a href="<?= e(url($lang)) ?>" class="text-sm font-medium text-slate-700 py-2"><?= e(t('nav.home')) ?></a>
            <a href="<?= e(url($lang, 'projects')) ?>" class="text-sm font-medium text-slate-700 py-2"><?= e(t('nav.projects')) ?></a>
            <a href="<?= e(url($lang, 'blog')) ?>" class="text-sm font-medium text-slate-700 py-2"><?= e(t('nav.blog')) ?></a>
            <a href="<?= e(url($lang, 'about')) ?>" class="text-sm font-medium text-slate-700 py-2"><?= e(t('nav.about')) ?></a>
            <a href="<?= e(url($lang, 'contact')) ?>" class="text-sm font-medium text-slate-700 py-2"><?= e(t('nav.contact')) ?></a>
            <a href="<?= e(url($lang, 'contact')) ?>" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-white bg-physio-900 rounded-full"><?= e(t('nav.cta')) ?></a>
        </div>
    </div>
</nav>
