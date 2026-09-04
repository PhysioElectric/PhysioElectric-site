<?php
/**
 * Single project page with the "Order a project like this" CTA.
 * Expects: $project, $cat, $related, $categories, $ctaTg, $ctaTgScheme, $ctaMailto
 */
$pTitle   = L($project, 'title');
$pImg     = (string) ($project['image'] ?? '');
$pCatName = L($cat, 'name');
$pTags    = tech_tags($project['tech_tags']);
$pUrl     = url(lang(), 'projects/' . e($cat['slug']) . '/' . e(slugOf($project)));
?>
<!-- Project hero -->
<section class="pt-36 pb-12 bg-slate-900 text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-40" style="background-image: radial-gradient(circle at 20% 20%, rgba(14,165,233,0.25) 0, transparent 40%), radial-gradient(circle at 80% 80%, rgba(59,130,246,0.2) 0, transparent 45%);"></div>
    <div class="max-w-5xl mx-auto px-6 lg:px-8 relative reveal">
        <nav class="text-xs text-slate-400 mb-6 flex flex-wrap items-center gap-2" aria-label="Breadcrumb">
            <a href="<?= e(url(lang())) ?>" class="hover:text-physio-400 transition-colors"><?= e(t('nav.home')) ?></a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 rtl:rotate-180"></i>
            <a href="<?= e(url(lang(), 'projects')) ?>" class="hover:text-physio-400 transition-colors"><?= e(t('projects.title')) ?></a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 rtl:rotate-180"></i>
            <a href="<?= e(url(lang(), 'projects/' . e($cat['slug']))) ?>" class="hover:text-physio-400 transition-colors"><?= e($pCatName) ?></a>
        </nav>
        <h1 class="text-3xl md:text-5xl font-bold tracking-tight leading-tight"><?= e($pTitle) ?></h1>
        <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-3">
            <span class="inline-flex items-center gap-2 text-sm text-physio-400 font-semibold uppercase tracking-wider">
                <i data-lucide="<?= e((string) $cat['icon']) ?>" class="w-4 h-4"></i>
                <?= e($pCatName) ?>
            </span>
            <?php foreach ($pTags as $tag): ?>
                <span class="px-3 py-1 bg-slate-800/80 border border-slate-700 rounded-full text-xs text-slate-300"><?= e($tag) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($pImg !== ''): ?>
<section class="max-w-5xl mx-auto px-6 lg:px-8 -mt-4">
    <img src="<?= e($pImg) ?>" alt="<?= e($pTitle) ?>" class="w-full max-h-[520px] object-cover rounded-2xl shadow-premium">
</section>
<?php endif; ?>

<section class="py-12">
    <div class="max-w-5xl mx-auto px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-12 items-start">

        <!-- Main content -->
        <div>
            <div class="prose-pe">
                <?= L($project, 'content') /* sanitized on save */ ?>
            </div>

            <?php if (!empty($related)): ?>
            <div class="mt-16 pt-10 border-t border-slate-200">
                <h2 class="text-2xl font-bold text-physio-950 mb-8"><?= e(t('projects.related')) ?></h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($related as $p): ?>
                        <?php
                        $rTitle = L($p, 'title');
                        $rImg   = (string) ($p['image'] ?? '');
                        $rUrl   = url(lang(), 'projects/' . e($p['category_slug']) . '/' . e(slugOf($p)));
                        ?>
                        <a href="<?= e($rUrl) ?>" class="glass-card rounded-xl overflow-hidden group pe-card flex">
                            <div class="w-28 h-24 shrink-0 overflow-hidden">
                                <?php if ($rImg !== ''): ?>
                                    <img src="<?= e($rImg) ?>" alt="<?= e($rTitle) ?>" class="w-full h-full object-cover pe-img-zoom" loading="lazy">
                                <?php else: ?>
                                    <div class="pe-cover w-full h-full flex items-center justify-center">
                                        <span class="relative z-10 text-physio-400/80"><i data-lucide="<?= e((string) ($p['category_icon'] ?? 'box')) ?>" class="w-8 h-8"></i></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-4">
                                <h3 class="text-sm font-bold text-physio-950 leading-snug group-hover:text-physio-600 transition-colors line-clamp-2"><?= e($rTitle) ?></h3>
                                <span class="mt-2 inline-flex items-center text-xs font-semibold text-physio-600">
                                    <?= e(t('projects.view')) ?>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 rtl:rotate-180"></i>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- CTA sidebar (highly visible order button) -->
        <aside class="lg:sticky lg:top-24">
            <div class="rounded-2xl bg-slate-900 text-white p-8 relative overflow-hidden shadow-xl">
                <div class="absolute inset-0 opacity-50 pointer-events-none" style="background-image: radial-gradient(circle at 80% 0%, rgba(14,165,233,0.35) 0, transparent 55%);"></div>
                <div class="relative">
                    <div class="w-12 h-12 rounded-xl bg-physio-500/20 border border-physio-500/40 flex items-center justify-center text-physio-400 mb-5">
                        <i data-lucide="rocket" class="w-6 h-6"></i>
                    </div>
                    <h2 class="text-xl font-bold leading-snug"><?= e(t('cta.title')) ?></h2>
                    <p class="mt-2 text-sm text-slate-400 leading-relaxed"><?= e(t('cta.subtitle')) ?></p>

                    <a href="<?= e($ctaTg) ?>" data-tg-link="<?= e($ctaTgScheme) ?>"
                       class="btn-shine mt-6 w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 text-sm font-semibold text-white bg-physio-500 hover:bg-physio-600 rounded-full shadow-lg transition-all hover:-translate-y-0.5">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <?= e(t('cta.telegram')) ?>
                    </a>
                    <a href="<?= e($ctaMailto) ?>"
                       class="mt-3 w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 text-sm font-semibold text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-full transition-all">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                        <?= e(t('cta.email')) ?>
                    </a>

                    <div class="mt-6 pt-5 border-t border-slate-800 space-y-2 text-xs text-slate-500">
                        <p class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-physio-400"></i><?= e(t('process.s1.t')) ?></p>
                        <p class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-physio-400"></i><?= e(t('process.s3.t')) ?></p>
                        <p class="flex items-center gap-2"><i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-physio-400"></i><?= e(t('process.s4.t')) ?></p>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</section>
