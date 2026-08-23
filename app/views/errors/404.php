<?php
/**
 * 404 page (rendered with the public header; seo_head was called
 * by the not_found() helper).
 */
?>
<section class="pt-40 pb-24 min-h-[70vh] flex items-center">
    <div class="max-w-2xl mx-auto px-6 text-center">
        <p class="text-8xl md:text-9xl font-bold text-gradient leading-none">404</p>
        <h1 class="mt-6 text-2xl md:text-3xl font-bold text-physio-950"><?= e(t('error.404.title')) ?></h1>
        <p class="mt-3 text-slate-500"><?= e(t('error.404.text')) ?></p>
        <a href="<?= e(url(lang())) ?>" class="btn-shine mt-8 inline-flex items-center gap-2 px-8 py-4 text-base font-medium text-white bg-physio-900 hover:bg-physio-950 rounded-full shadow-lg transition-all hover:-translate-y-1">
            <?= e(t('error.404.home')) ?>
            <i data-lucide="arrow-right" class="w-5 h-5 rtl:rotate-180"></i>
        </a>
    </div>
</section>
