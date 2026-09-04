<?php
/**
 * Shared project card (light surface).
 * Expects: $p (project row)
 */
$pTitle = L($p, 'title');
$pImg   = (string) ($p['image'] ?? '');
$pUrl   = url(lang(), 'projects/' . e($p['category_slug']) . '/' . e(slugOf($p)));
$pTg    = cta_telegram_url($pTitle);
$pTags  = array_slice(tech_tags($p['tech_tags']), 0, 3);
?>
<article class="reveal glass-card rounded-2xl overflow-hidden group pe-card flex flex-col">
    <a href="<?= e($pUrl) ?>" class="block relative aspect-[16/10] overflow-hidden">
        <?php if ($pImg !== ''): ?>
            <img src="<?= e($pImg) ?>" alt="<?= e($pTitle) ?>" class="w-full h-full object-cover pe-img-zoom" loading="lazy">
        <?php else: ?>
            <div class="pe-cover w-full h-full flex items-center justify-center">
                <span class="relative z-10 text-physio-400/80" aria-hidden="true">
                    <i data-lucide="<?= e((string) ($p['category_icon'] ?? 'box')) ?>" class="w-14 h-14"></i>
                </span>
            </div>
        <?php endif; ?>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
    </a>
    <div class="p-6 flex flex-col flex-1">
        <a href="<?= e(url(lang(), 'projects/' . e($p['category_slug']))) ?>" class="text-xs font-semibold text-physio-600 uppercase tracking-wider hover:text-physio-500 transition-colors">
            <?= e(L($p, 'category_name')) ?>
        </a>
        <h3 class="mt-2 text-lg font-bold text-physio-950 leading-snug group-hover:text-physio-600 transition-colors">
            <a href="<?= e($pUrl) ?>"><?= e($pTitle) ?></a>
        </h3>
        <p class="mt-2.5 text-sm text-slate-500 leading-relaxed line-clamp-2 flex-1"><?= e(L($p, 'short_desc')) ?></p>
        <div class="mt-4 flex flex-wrap gap-1.5">
            <?php foreach ($pTags as $tag): ?>
                <span class="px-2 py-1 bg-slate-100 rounded-md text-[11px] font-medium text-slate-600"><?= e($tag) ?></span>
            <?php endforeach; ?>
        </div>
        <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
            <a href="<?= e($pUrl) ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-physio-600 group-hover:gap-2.5 transition-all">
                <?= e(t('projects.view')) ?>
                <i data-lucide="arrow-right" class="w-4 h-4 rtl:rotate-180"></i>
            </a>
            <a href="<?= e($pTg) ?>" data-tg-link="<?= e(cta_tg_scheme()) ?>" title="<?= e(t('projects.order')) ?>" aria-label="<?= e(t('projects.order')) ?>"
               class="w-9 h-9 rounded-full bg-physio-900 text-white flex items-center justify-center hover:bg-physio-500 transition-colors">
                <i data-lucide="send" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</article>
