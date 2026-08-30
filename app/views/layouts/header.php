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
<!-- Bulletproof Navbar (Independent of Tailwind) -->
<nav id="navbar" class="pe-navbar">
    <div class="pe-nav-container">
        <!-- Logo -->
        <a href="<?= e(url($lang)) ?>" class="pe-logo-wrapper">
            <div class="pe-logo-icon">P</div>
            <span class="pe-logo-text"><?= e(setting('site_name', 'PhysioElectric')) ?></span>
        </a>

        <!-- Desktop Menu -->
        <div class="pe-desktop-menu">
            <a href="<?= e(url($lang)) ?>" class="pe-nav-link <?= $activeNav === 'home' || $currentPath === url($lang) ? 'active' : '' ?>"><?= e(t('nav.home')) ?></a>
            <a href="<?= e(url($lang, 'projects')) ?>" class="pe-nav-link <?= $activeNav === 'projects' ? 'active' : '' ?>"><?= e(t('nav.projects')) ?></a>
            <a href="<?= e(url($lang, 'blog')) ?>" class="pe-nav-link <?= $activeNav === 'blog' ? 'active' : '' ?>"><?= e(t('nav.blog')) ?></a>
            <a href="<?= e(url($lang, 'about')) ?>" class="pe-nav-link <?= $activeNav === 'about' ? 'active' : '' ?>"><?= e(t('nav.about')) ?></a>
            <a href="<?= e(url($lang, 'contact')) ?>" class="pe-nav-link <?= $activeNav === 'contact' ? 'active' : '' ?>"><?= e(t('nav.contact')) ?></a>
        </div>

        <!-- Right Actions -->
        <div class="pe-nav-actions">
            <!-- Language Switcher -->
            <a href="<?= e($altPath) ?>" class="pe-nav-link" style="display: flex; gap: 0.25rem; align-items: center;" lang="<?= e(altLang()) ?>">
                <i data-lucide="globe" style="width:18px;height:18px;"></i>
                <span><?= e(t('lang.switch')) ?></span>
            </a>

            <!-- CTA Button -->
            <a href="<?= e(url($lang, 'contact')) ?>" class="pe-nav-cta">
                <?= e(t('nav.cta')) ?>
            </a>

            <!-- Mobile Menu Button -->
            <button class="pe-mobile-btn" id="mobileMenuBtn">
                <i data-lucide="menu" style="width:26px;height:26px;"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Menu Dropdown -->
    <div id="mobileMenu" style="display: none; position: absolute; top: 75px; left: 0; right: 0; background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <div style="display: flex; flex-direction: column; gap: 1.2rem;">
            <a href="<?= e(url($lang)) ?>" class="pe-nav-link"><?= e(t('nav.home')) ?></a>
            <a href="<?= e(url($lang, 'projects')) ?>" class="pe-nav-link"><?= e(t('nav.projects')) ?></a>
            <a href="<?= e(url($lang, 'blog')) ?>" class="pe-nav-link"><?= e(t('nav.blog')) ?></a>
            <a href="<?= e(url($lang, 'about')) ?>" class="pe-nav-link"><?= e(t('nav.about')) ?></a>
            <a href="<?= e(url($lang, 'contact')) ?>" class="pe-nav-link"><?= e(t('nav.contact')) ?></a>
        </div>
    </div>
</nav>