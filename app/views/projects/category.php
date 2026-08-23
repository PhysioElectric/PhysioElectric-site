<?php
/**
 * Project category archive.
 * Expects: $cat, $projects, $categories
 */
$catName = L($cat, 'name');
$catDesc = L($cat, 'description', $catName);
?>
<!-- Category header -->
<section class="pt-36 pb-12 bg-slate-900 text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-40" style="background-image: radial-gradient(circle at 20% 20%, rgba(14,165,233,0.25) 0, transparent 40%), radial-gradient(circle at 80% 80%, rgba(59,130,246,0.2) 0, transparent 45%);"></div>
    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative reveal">
        <nav class="text-xs text-slate-400 mb-4 flex items-center gap-2" aria-label="Breadcrumb">
            <a href="<?= e(url(lang())) ?>" class="hover:text-physio-400 transition-colors"><?= e(t('nav.home')) ?></a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 rtl:rotate-180"></i>
            <a href="<?= e(url(lang(), 'projects')) ?>" class="hover:text-physio-400 transition-colors"><?= e(t('projects.title')) ?></a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 rtl:rotate-180"></i>
            <span class="text-slate-200"><?= e($catName) ?></span>
        </nav>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-physio-500/15 border border-physio-500/30 flex items-center justify-center text-physio-400 shrink-0">
                <i data-lucide="<?= e((string) $cat['icon']) ?>" class="w-7 h-7"></i>
            </div>
            <div>
                <h1 class="text-3xl md:text-5xl font-bold tracking-tight"><?= e($catName) ?></h1>
                <p class="mt-2 text-slate-400 max-w-2xl"><?= e($catDesc) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Filter chips -->
<section class="bg-slate-900 pb-8">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-wrap gap-2.5">
        <a href="<?= e(url(lang(), 'projects')) ?>" class="px-4 py-2 rounded-full text-sm font-medium bg-slate-800/70 border border-slate-700 text-slate-300 hover:text-white hover:border-physio-500 transition-all">
            <?= e(t('projects.all')) ?>
        </a>
        <?php foreach ($categories as $c): ?>
            <?php $isCurrent = $c['id'] == $cat['id']; ?>
            <a href="<?= e(url(lang(), 'projects/' . e($c['slug']))) ?>"
               class="px-4 py-2 rounded-full text-sm font-semibold transition-all <?= $isCurrent ? 'bg-physio-500 text-white shadow-lg' : 'bg-slate-800/70 border border-slate-700 text-slate-300 hover:text-white hover:border-physio-500' ?>">
                <?= e(L($c, 'name')) ?>
                <span class="ms-1.5 text-xs <?= $isCurrent ? 'text-white/70' : 'text-slate-500' ?>">(<?= e((int) $c['published_count']) ?>)</span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="py-16">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <?php if (empty($projects)): ?>
            <div class="text-center text-slate-400 py-24"><?= e(t('projects.notFound')) ?></div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($projects as $p): ?>
                    <?php include BASE_PATH . '/views/projects/_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
