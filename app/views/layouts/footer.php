<?php
/**
 * Public layout footer + scripts.
 */
$lang     = lang();
$catList  = $catList ?? CategoryModel::active();
$email    = (string) setting('contact_email', 'info@physioelectric.com');
$phone    = (string) setting('contact_phone', '');
$address  = (string) setting($lang === 'fa' ? 'address_fa' : 'address_en', '');
$telegram = cta_telegram_url();
$year     = (string) date('Y');
?>
<!-- Footer -->
<footer class="bg-physio-950 text-slate-400 pt-16 pb-8 relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none opacity-40" style="background-image: radial-gradient(circle at 20% 0%, rgba(14,165,233,0.15) 0, transparent 40%), radial-gradient(circle at 85% 100%, rgba(59,130,246,0.12) 0, transparent 45%);"></div>
    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
            <!-- Brand -->
            <div>
                <a href="<?= e(url($lang)) ?>" class="flex items-center gap-2 mb-4 group">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-physio-500 to-physio-600 flex items-center justify-center text-white font-bold text-xl">P</div>
                    <span class="text-xl font-bold text-white"><?= e(setting('site_name', 'PhysioElectric')) ?></span>
                </a>
                <p class="text-sm leading-relaxed"><?= e(t('footer.desc')) ?></p>
            </div>

            <!-- Quick links -->
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider"><?= e(t('footer.links')) ?></h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="<?= e(url($lang)) ?>" class="hover:text-physio-400 transition-colors"><?= e(t('nav.home')) ?></a></li>
                    <li><a href="<?= e(url($lang, 'projects')) ?>" class="hover:text-physio-400 transition-colors"><?= e(t('nav.projects')) ?></a></li>
                    <li><a href="<?= e(url($lang, 'blog')) ?>" class="hover:text-physio-400 transition-colors"><?= e(t('nav.blog')) ?></a></li>
                    <li><a href="<?= e(url($lang, 'about')) ?>" class="hover:text-physio-400 transition-colors"><?= e(t('nav.about')) ?></a></li>
                    <li><a href="<?= e(url($lang, 'contact')) ?>" class="hover:text-physio-400 transition-colors"><?= e(t('nav.contact')) ?></a></li>
                </ul>
            </div>

            <!-- Categories -->
<div>
    <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider"><?= e(t('footer.categories')) ?></h4>
    <ul class="space-y-2.5 text-sm">
        <?php if (empty($catList)): ?>
            <li class="text-slate-500">—</li>
        <?php else: ?>
            <?php foreach ($catList as $c): ?>
            <li>
                <a href="<?= e(url($lang, 'projects/' . e($c['slug']))) ?>" class="hover:text-physio-400 transition-colors">
                    <?= e(L($c, 'name')) ?>
                </a>
            </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>
            <!-- Contact -->
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider"><?= e(t('footer.contact')) ?></h4>
                <ul class="space-y-3 text-sm">
                    <li>
                        <a href="<?= e($telegram) ?>" data-tg-link="<?= e(cta_tg_scheme()) ?>" class="flex items-center gap-2.5 hover:text-physio-400 transition-colors">
                            <i data-lucide="send" class="w-4 h-4 shrink-0"></i>
                            <span>Telegram</span>
                        </a>
                    </li>
                    <?php if ($email !== ''): ?>
                    <li>
                        <a href="mailto:<?= e($email) ?>" class="flex items-center gap-2.5 hover:text-physio-400 transition-colors" dir="ltr">
                            <i data-lucide="mail" class="w-4 h-4 shrink-0"></i>
                            <span><?= e($email) ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($phone !== ''): ?>
                    <li>
                        <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>" class="flex items-center gap-2.5 hover:text-physio-400 transition-colors" dir="ltr">
                            <i data-lucide="phone" class="w-4 h-4 shrink-0"></i>
                            <span><?= e($phone) ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($address !== ''): ?>
                    <li class="flex items-center gap-2.5">
                        <i data-lucide="map-pin" class="w-4 h-4 shrink-0"></i>
                        <span><?= e($address) ?></span>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="border-t border-slate-800 pt-6 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <p>© <?= e($year) ?> <?= e(setting('site_name', 'PhysioElectric')) ?> — <?= e(t('footer.rights')) ?></p>
            <div class="flex items-center gap-4">
                <a href="<?= e(url('fa')) ?>" class="hover:text-slate-300 transition-colors">فارسی</a>
                <span class="w-1 h-1 rounded-full bg-slate-700"></span>
                <a href="<?= e(url('en')) ?>" class="hover:text-slate-300 transition-colors">English</a>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="/assets/js/lucide.min.js"></script>
<script src="/assets/js/main.js" data-lang="<?= e($lang) ?>"></script>
</body>
</html>
