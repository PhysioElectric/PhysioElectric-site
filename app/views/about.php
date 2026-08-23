<?php
/**
 * About page.
 */
?>
<!-- Header -->
<section class="pt-36 pb-16 bg-white border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 reveal">
        <nav class="text-xs text-slate-400 mb-6 flex items-center gap-2" aria-label="Breadcrumb">
            <a href="<?= e(url(lang())) ?>" class="hover:text-physio-600 transition-colors"><?= e(t('nav.home')) ?></a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 rtl:rotate-180"></i>
            <span class="text-slate-600"><?= e(t('about.title')) ?></span>
        </nav>
        <h1 class="text-4xl md:text-5xl font-bold tracking-tight text-physio-950 max-w-3xl leading-tight">
            <?= e(t('about.title')) ?>
            <span class="text-gradient">.</span>
        </h1>
        <p class="mt-5 text-lg text-slate-500 max-w-2xl leading-relaxed"><?= e(t('about.subtitle')) ?></p>
    </div>
</section>

<!-- Story -->
<section class="py-20">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-14 items-center">
        <div class="reveal">
            <span class="text-physio-500 font-bold text-xl mb-2 block">Physio</span>
            <h2 class="text-2xl md:text-3xl font-bold text-physio-950 mb-5"><?= e(t('about.story.t')) ?></h2>
            <p class="text-slate-600 leading-relaxed mb-4"><?= e(t('about.story.p1')) ?></p>
            <p class="text-slate-600 leading-relaxed"><?= e(t('about.story.p2')) ?></p>
        </div>
        <div class="reveal reveal-delay-1">
            <div class="bg-slate-900 rounded-2xl h-[340px] flex items-center justify-center relative overflow-hidden shadow-lg p-8">
                <div class="absolute inset-0 bg-gradient-radial from-physio-600/25 to-transparent"></div>
                <div class="relative z-10 flex flex-wrap justify-center gap-3">
                    <span class="tech-tag" style="animation-delay: 0s">MATLAB</span>
                    <span class="tech-tag" style="animation-delay: 0.5s">COMSOL</span>
                    <span class="tech-tag" style="animation-delay: 1s">Python</span>
                    <span class="tech-tag" style="animation-delay: 1.5s">C++</span>
                    <span class="tech-tag" style="animation-delay: 2s">OpenCV</span>
                    <span class="tech-tag" style="animation-delay: 2.5s">PHP</span>
                    <span class="tech-tag" style="animation-delay: 3s">MySQL</span>
                    <span class="tech-tag" style="animation-delay: 3.5s">Tailwind</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php
            $values = [
                ['icon' => 'target', 'title' => t('about.v1.t'), 'desc' => t('about.v1.d')],
                ['icon' => 'eye',    'title' => t('about.v2.t'), 'desc' => t('about.v2.d')],
                ['icon' => 'shield-check', 'title' => t('about.v3.t'), 'desc' => t('about.v3.d')],
            ];
            ?>
            <?php foreach ($values as $i => $v): ?>
                <div class="reveal glass-card rounded-2xl p-8 pe-card reveal-delay-<?= $i + 1 ?>">
                    <div class="w-12 h-12 rounded-xl bg-physio-100 text-physio-600 flex items-center justify-center mb-5">
                        <i data-lucide="<?= e($v['icon']) ?>" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-physio-950"><?= e($v['title']) ?></h3>
                    <p class="mt-2 text-sm text-slate-500 leading-relaxed"><?= e($v['desc']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-20 bg-slate-900 relative overflow-hidden">
    <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 15% 50%, rgba(14,165,233,0.35) 0, transparent 40%), radial-gradient(circle at 85% 20%, rgba(59,130,246,0.3) 0, transparent 40%);"></div>
    <div class="max-w-4xl mx-auto px-6 lg:px-8 text-center relative reveal">
        <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-white"><?= e(t('about.cta')) ?></h2>
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="<?= e(cta_telegram_url()) ?>" data-tg-link="<?= e(cta_tg_scheme()) ?>" class="btn-shine w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-medium text-white bg-physio-500 hover:bg-physio-600 rounded-full shadow-lg transition-all hover:-translate-y-1">
                <i data-lucide="send" class="w-5 h-5"></i>
                <?= e(t('contact.telegram')) ?>
            </a>
            <a href="<?= e(url(lang(), 'contact')) ?>" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-base font-medium text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-full transition-all">
                <?= e(t('nav.contact')) ?>
            </a>
        </div>
    </div>
</section>
