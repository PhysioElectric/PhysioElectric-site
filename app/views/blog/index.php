<?php
/**
 * Blog archive.
 * Expects: $posts
 */
?>
<!-- Page header -->
<section class="pt-36 pb-12 bg-white border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 reveal">
        <h1 class="text-4xl md:text-5xl font-bold tracking-tight text-physio-950"><?= e(t('blog.title')) ?></h1>
        <p class="mt-3 text-lg text-slate-500 max-w-2xl"><?= e(t('blog.subtitle')) ?></p>
    </div>
</section>

<section class="py-16">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <?php if (empty($posts)): ?>
            <div class="text-center text-slate-400 py-24"><?= e(t('blog.noPosts')) ?></div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($posts as $post): ?>
                <?php
                $postTitle = L($post, 'title');
                $postImg   = (string) ($post['image'] ?? '');
                $postUrl   = url(lang(), 'blog/' . e(slugOf($post)));
                ?>
                <article class="reveal glass-card rounded-2xl overflow-hidden group pe-card">
                    <a href="<?= e($postUrl) ?>" class="block">
                        <div class="aspect-[16/9] overflow-hidden">
                            <?php if ($postImg !== ''): ?>
                                <img src="<?= e($postImg) ?>" alt="<?= e($postTitle) ?>" class="w-full h-full object-cover pe-img-zoom" loading="lazy">
                            <?php else: ?>
                                <div class="pe-cover w-full h-full flex items-center justify-center">
                                    <span class="relative z-10 text-physio-400/70 font-mono text-2xl">&lt;/&gt;</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-6">
                            <time class="text-xs font-semibold text-physio-600 uppercase tracking-wider" datetime="<?= e((string) $post['published_at']) ?>"><?= e(format_date((string) ($post['published_at'] ?? $post['created_at']))) ?></time>
                            <h2 class="mt-2 text-xl font-bold text-physio-950 leading-snug group-hover:text-physio-600 transition-colors"><?= e($postTitle) ?></h2>
                            <p class="mt-3 text-sm text-slate-500 leading-relaxed line-clamp-3"><?= e(L($post, 'excerpt')) ?></p>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="inline-flex items-center text-sm font-semibold text-physio-600 group-hover:gap-2 gap-1.5 transition-all">
                                    <?= e(t('blog.readMore')) ?>
                                    <i data-lucide="arrow-right" class="w-4 h-4 rtl:rotate-180"></i>
                                </span>
                                <span class="text-xs text-slate-400"><?= e(reading_time(L($post, 'content'))) ?> <?= e(t('blog.minRead')) ?></span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
