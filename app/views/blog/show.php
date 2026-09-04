<?php
/**
 * Single blog post.
 * Expects: $post, $related
 */
$postTitle = L($post, 'title');
$postImg   = (string) ($post['image'] ?? '');
$postCat   = t('blog.title');
$shareUrl  = Config::baseUrl() . url(lang(), 'blog/' . e(slugOf($post)));
$shareTg   = 'https://t.me/share/url?url=' . rawurlencode($shareUrl) . '&text=' . rawurlencode($postTitle);
?>
<!-- Post hero -->
<section class="pt-36 pb-10 bg-white border-b border-slate-100">
    <div class="max-w-4xl mx-auto px-6 lg:px-8">
        <nav class="text-xs text-slate-400 mb-6 flex items-center gap-2" aria-label="Breadcrumb">
            <a href="<?= e(url(lang())) ?>" class="hover:text-physio-600 transition-colors"><?= e(t('nav.home')) ?></a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 rtl:rotate-180"></i>
            <a href="<?= e(url(lang(), 'blog')) ?>" class="hover:text-physio-600 transition-colors"><?= e(t('blog.title')) ?></a>
        </nav>
        <h1 class="text-3xl md:text-5xl font-bold tracking-tight text-physio-950 leading-tight"><?= e($postTitle) ?></h1>
        <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-500">
            <span class="inline-flex items-center gap-1.5">
                <i data-lucide="calendar" class="w-4 h-4 text-physio-500"></i>
                <?= e(t('blog.published')) ?> <?= e(format_date((string) ($post['published_at'] ?? $post['created_at']))) ?>
            </span>
            <span class="inline-flex items-center gap-1.5">
                <i data-lucide="clock" class="w-4 h-4 text-physio-500"></i>
                <?= e(reading_time(L($post, 'content'))) ?> <?= e(t('blog.minRead')) ?>
            </span>
            <a href="<?= e($shareTg) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-physio-600 hover:text-physio-500 font-medium transition-colors">
                <i data-lucide="share-2" class="w-4 h-4"></i>
                <?= e(t('blog.share')) ?>
            </a>
        </div>
    </div>
</section>

<?php if ($postImg !== ''): ?>
<section class="max-w-5xl mx-auto px-6 lg:px-8 -mt-2">
    <img src="<?= e($postImg) ?>" alt="<?= e($postTitle) ?>" class="w-full max-h-[520px] object-cover rounded-2xl shadow-premium">
</section>
<?php endif; ?>

<!-- Content -->
<section class="py-12">
    <div class="max-w-3xl mx-auto px-6 lg:px-8">
        <div class="prose-pe">
            <?= L($post, 'content') /* sanitized on save */ ?>
        </div>

        <div class="mt-12 flex flex-wrap items-center justify-between gap-4 border-t border-slate-200 pt-8">
            <a href="<?= e(url(lang(), 'blog')) ?>" class="inline-flex items-center gap-2 text-physio-600 font-semibold hover:gap-3 transition-all">
                <i data-lucide="arrow-right" class="w-4 h-4 rtl:rotate-180"></i>
                <?= e(t('blog.back')) ?>
            </a>
            <a href="<?= e($shareTg) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-physio-900 hover:bg-physio-950 rounded-full transition-all">
                <i data-lucide="send" class="w-4 h-4"></i>
                <?= e(t('blog.share')) ?>
            </a>
        </div>
    </div>
</section>

<?php if (!empty($related)): ?>
<!-- Related -->
<section class="py-16 bg-white border-t border-slate-100">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-physio-950 mb-10 reveal"><?= e(t('blog.related')) ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($related as $post): ?>
                <?php
                $rTitle = L($post, 'title');
                $rImg   = (string) ($post['image'] ?? '');
                $rUrl   = url(lang(), 'blog/' . e(slugOf($post)));
                ?>
                <article class="glass-card rounded-2xl overflow-hidden group pe-card">
                    <a href="<?= e($rUrl) ?>" class="block">
                        <div class="aspect-[16/9] overflow-hidden">
                            <?php if ($rImg !== ''): ?>
                                <img src="<?= e($rImg) ?>" alt="<?= e($rTitle) ?>" class="w-full h-full object-cover pe-img-zoom" loading="lazy">
                            <?php else: ?>
                                <div class="pe-cover w-full h-full flex items-center justify-center">
                                    <span class="relative z-10 text-physio-400/70 font-mono text-2xl">&lt;/&gt;</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-5">
                            <time class="text-xs font-semibold text-physio-600 uppercase tracking-wider"><?= e(format_date((string) ($post['published_at'] ?? $post['created_at']))) ?></time>
                            <h3 class="mt-2 text-base font-bold text-physio-950 leading-snug group-hover:text-physio-600 transition-colors line-clamp-2"><?= e($rTitle) ?></h3>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
