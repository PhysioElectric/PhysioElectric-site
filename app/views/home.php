<?php
/**
 * Home page.
 * Expects: $featured (projects), $latestPosts, $categories.
 */
$site = setting('site_name', 'PhysioElectric');
?>

<!-- ============ HERO ============ -->
<section id="home" class="relative min-h-screen flex items-center pt-20 overflow-hidden">
    <canvas id="hero-canvas"></canvas>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10 w-full text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-physio-100/50 border border-physio-200 text-physio-600 text-xs font-semibold uppercase tracking-wider mb-8 reveal">
            <span class="w-2 h-2 rounded-full bg-physio-500 animate-pulse"></span>
            <span><?= e((string) setting(lang() === 'fa' ? 'hero_badge_fa' : 'hero_badge_en', t('hero.badge', []))) ?></span>
        </div>

        <h1 class="text-5xl md:text-7xl lg:text-8xl font-bold tracking-tight text-physio-950 max-w-5xl mx-auto leading-[1.15] reveal reveal-delay-1">
            <?= (string) setting(lang() === 'fa' ? 'hero_title_fa' : 'hero_title_en', t('meta.home')) /* contains <br> */ ?>
        </h1>

        <p class="mt-8 text-lg md:text-xl text-slate-500 max-w-2xl mx-auto leading-relaxed reveal reveal-delay-2">
            <?= e((string) setting(lang() === 'fa' ? 'hero_subtitle_fa' : 'hero_subtitle_en', '')) ?>
        </p>

        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4 reveal reveal-delay-3">
            <a href="<?= e(url(lang(), 'projects')) ?>" class="btn-shine w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-base font-medium text-white bg-physio-900 hover:bg-physio-950 rounded-full shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
                <?= e(t('hero.ctaPrimary')) ?>
            </a>
            <a href="<?= e(url(lang(), 'contact')) ?>" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-base font-medium text-physio-900 bg-white hover:bg-slate-50 border border-slate-200 rounded-full shadow-sm hover:shadow-md transition-all">
                <?= e(t('hero.ctaSecondary')) ?>
            </a>
        </div>
    </div>

    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 opacity-50 animate-bounce">
        <span class="text-xs font-medium tracking-widest uppercase text-slate-400"><?= e(t('hero.scroll')) ?></span>
        <i data-lucide="mouse" class="w-5 h-5 text-slate-400"></i>
    </div>
</section>

<!-- ============ CAPABILITIES ============ -->
<section id="capabilities" class="py-24 md:py-32 bg-white relative z-20">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="mb-16 md:mb-24 reveal max-w-2xl">
            <h2 class="text-3xl md:text-5xl font-bold tracking-tight text-physio-950"><?= e(t('cap.title')) ?></h2>
            <p class="mt-4 text-lg text-slate-500"><?= e(t('cap.subtitle')) ?></p>
        </div>

        <div class="space-y-32">
            <!-- 01 Core Tech -->
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-24 reveal">
                <div class="w-full lg:w-1/2">
                    <span class="text-physio-500 font-bold text-xl mb-2 block">01</span>
                    <h3 class="text-2xl md:text-3xl font-bold text-physio-950 mb-4"><?= e(t('cap.c0.title')) ?></h3>
                    <p class="text-slate-600 mb-8 text-lg leading-relaxed"><?= e(t('cap.c0.desc')) ?></p>
                    <a href="<?= e(url(lang(), 'projects')) ?>" class="inline-flex items-center text-physio-600 font-semibold group">
                        <?= e(t('cap.c0.link')) ?>
                        <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>
                    </a>
                </div>
                <div class="w-full lg:w-1/2">
                    <div class="bg-slate-900 rounded-2xl h-[300px] flex items-center justify-center relative overflow-hidden shadow-lg p-6">
                        <div class="absolute inset-0 bg-gradient-radial from-physio-600/20 to-transparent"></div>
                        <div class="relative z-10 flex flex-wrap justify-center gap-3">
                            <span class="tech-tag" style="animation-delay: 0s">Python</span>
                            <span class="tech-tag" style="animation-delay: 0.4s">C++</span>
                            <span class="tech-tag" style="animation-delay: 0.8s">React</span>
                            <span class="tech-tag" style="animation-delay: 1.2s">Django</span>
                            <span class="tech-tag" style="animation-delay: 1.6s">OpenCV</span>
                            <span class="tech-tag" style="animation-delay: 2s">PHP</span>
                            <span class="tech-tag" style="animation-delay: 2.4s">MySQL</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 02 Simulation -->
            <div class="flex flex-col lg:flex-row-reverse items-center gap-12 lg:gap-24 reveal">
                <div class="w-full lg:w-1/2">
                    <span class="text-physio-500 font-bold text-xl mb-2 block">02</span>
                    <h3 class="text-2xl md:text-3xl font-bold text-physio-950 mb-4"><?= e(t('cap.c1.title')) ?></h3>
                    <p class="text-slate-600 mb-8 text-lg leading-relaxed"><?= e(t('cap.c1.desc')) ?></p>
                    <a href="<?= e(url(lang(), 'projects/simulation')) ?>" class="inline-flex items-center text-physio-600 font-semibold group">
                        <?= e(t('cap.c1.link')) ?>
                        <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>
                    </a>
                </div>
                <div class="w-full lg:w-1/2">
                    <div class="bg-slate-900 rounded-2xl h-[300px] flex items-center justify-center relative overflow-hidden shadow-lg">
                        <div class="absolute inset-0 bg-gradient-radial from-physio-600/20 to-transparent"></div>
                        <div class="relative z-10 flex items-center justify-center gap-10">
                            <div class="cube-container">
                                <div class="cube">
                                    <div class="cube-face face-front"></div>
                                    <div class="cube-face face-back"></div>
                                    <div class="cube-face face-right"></div>
                                    <div class="cube-face face-left"></div>
                                    <div class="cube-face face-top"></div>
                                    <div class="cube-face face-bottom"></div>
                                </div>
                            </div>
                            <svg class="w-56 h-24" viewBox="0 0 200 80" fill="none">
                                <path class="wave-path" d="M0 40 Q 12.5 5, 25 40 T 50 40 T 75 40 T 100 40 T 125 40 T 150 40 T 175 40 T 200 40" stroke="#0ea5e9" stroke-width="2.5"/>
                                <path class="wave-path" d="M0 40 Q 12.5 70, 25 40 T 50 40 T 75 40 T 100 40 T 125 40 T 150 40 T 175 40 T 200 40" stroke="rgba(255,255,255,0.35)" stroke-width="1.5" style="animation-delay: 1s"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 03 Programming & AI -->
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-24 reveal">
                <div class="w-full lg:w-1/2">
                    <span class="text-physio-500 font-bold text-xl mb-2 block">03</span>
                    <h3 class="text-2xl md:text-3xl font-bold text-physio-950 mb-4"><?= e(t('cap.c2.title')) ?></h3>
                    <p class="text-slate-600 mb-8 text-lg leading-relaxed"><?= e(t('cap.c2.desc')) ?></p>
                    <a href="<?= e(url(lang(), 'projects/programming')) ?>" class="inline-flex items-center text-physio-600 font-semibold group">
                        <?= e(t('cap.c2.link')) ?>
                        <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>
                    </a>
                </div>
                <div class="w-full lg:w-1/2">
                    <div class="mock-browser h-[300px] flex flex-col">
                        <div class="mock-browser-header">
                            <span class="mock-dot"></span><span class="mock-dot"></span><span class="mock-dot"></span>
                            <span class="ms-3 text-[10px] text-slate-400 font-mono">signal_pipeline.py</span>
                        </div>
                        <div class="flex-1 p-5 font-mono text-[11px] leading-6 overflow-hidden text-slate-300" dir="ltr">
                            <p><span class="text-sky-400">import</span> numpy <span class="text-sky-400">as</span> np</p>
                            <p><span class="text-sky-400">from</span> scipy <span class="text-sky-400">import</span> signal</p>
                            <p>&nbsp;</p>
                            <p>b, a = signal.<span class="text-emerald-400">butter</span>(4, [5, 15], fs=256)</p>
                            <p>clean = signal.<span class="text-emerald-400">filtfilt</span>(b, a, ecg)</p>
                            <p>&nbsp;</p>
                            <p>beats = <span class="text-emerald-400">detect_qrs</span>(clean, fs=256)</p>
                            <p><span class="text-slate-500"># 98.6% accuracy on MIT-BIH</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 04 Web Development -->
            <div class="flex flex-col lg:flex-row-reverse items-center gap-12 lg:gap-24 reveal">
                <div class="w-full lg:w-1/2">
                    <span class="text-physio-500 font-bold text-xl mb-2 block">04</span>
                    <h3 class="text-2xl md:text-3xl font-bold text-physio-950 mb-4"><?= e(t('cap.c3.title')) ?></h3>
                    <p class="text-slate-600 mb-8 text-lg leading-relaxed"><?= e(t('cap.c3.desc')) ?></p>
                    <a href="<?= e(url(lang(), 'projects/web-development')) ?>" class="inline-flex items-center text-physio-600 font-semibold group">
                        <?= e(t('cap.c3.link')) ?>
                        <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>
                    </a>
                </div>
                <div class="w-full lg:w-1/2">
                    <div class="bg-slate-900 rounded-2xl h-[300px] flex items-center justify-center relative overflow-hidden shadow-lg">
                        <div class="absolute inset-0 bg-gradient-radial from-physio-600/20 to-transparent"></div>
                        <div class="relative z-10 flex items-end gap-3 h-40 items-center">
                            <div class="flex items-end gap-3">
                                <div class="w-3 h-16 bg-physio-500/80 rounded signal-bar-1"></div>
                                <div class="w-3 h-24 bg-physio-500/80 rounded signal-bar-2"></div>
                                <div class="w-3 h-20 bg-physio-500/80 rounded signal-bar-3"></div>
                                <div class="w-3 h-28 bg-physio-400 rounded signal-bar-2"></div>
                            </div>
                            <div class="ms-6 bg-slate-800/80 backdrop-blur border border-slate-700 rounded-xl px-5 py-4">
                                <p class="text-xs text-slate-400 mb-1">Uptime</p>
                                <p class="text-2xl font-bold text-white">99.98%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ FEATURED PROJECTS (dark slider) ============ -->
<section id="projects" class="py-24 bg-slate-900 text-white overflow-hidden relative">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 mb-4 flex flex-col md:flex-row justify-between items-start md:items-end gap-6 reveal">
        <div class="max-w-2xl">
            <h2 class="text-3xl md:text-5xl font-bold tracking-tight"><?= e(t('home.projects.title')) ?></h2>
            <p class="mt-4 text-lg text-slate-400"><?= e(t('home.projects.subtitle')) ?></p>
        </div>
        <div class="flex items-center gap-4 shrink-0">
            <!-- Slider Controls -->
            <div class="hidden md:flex p-1.5 bg-slate-800/60 border border-slate-700/80 rounded-full items-center gap-1 backdrop-blur-md shadow-lg">
                <button id="prevBtn" class="w-12 h-12 rounded-full flex items-center justify-center hover:bg-slate-700 text-slate-400 hover:text-white transition-all" aria-label="prev">
                    <i data-lucide="chevron-left" class="w-5 h-5 rtl:rotate-180"></i>
                </button>
                <div class="w-px h-5 bg-slate-700"></div>
                <button id="nextBtn" class="w-12 h-12 rounded-full flex items-center justify-center hover:bg-slate-700 text-slate-400 hover:text-white transition-all" aria-label="next">
                    <i data-lucide="chevron-right" class="w-5 h-5 rtl:rotate-180"></i>
                </button>
            </div>
            <a href="<?= e(url(lang(), 'projects')) ?>" class="inline-flex items-center gap-2 text-physio-400 font-semibold text-sm hover:text-physio-300 transition-colors">
                <?= e(t('home.projects.viewAll')) ?>
                <i data-lucide="arrow-right" class="w-4 h-4 rtl:rotate-180"></i>
            </a>
        </div>
    </div>

    <div class="slider-container flex gap-6 px-6 md:px-8 lg:px-12 overflow-x-auto hide-scrollbar snap-x snap-mandatory py-8 cursor-grab" id="projectSlider">
        <?php if (empty($featured)): ?>
            <div class="min-w-[85vw] md:min-w-[600px] flex-shrink-0 snap-center">
                <div class="bg-slate-800 rounded-2xl aspect-[16/9] border border-slate-700 flex items-center justify-center text-slate-500">
                    <?= e(t('projects.notFound')) ?>
                </div>
            </div>
        <?php else: ?>
        <?php foreach ($featured as $p): ?>
            <?php
            $pTitle = L($p, 'title');
            $pImg   = (string) ($p['image'] ?? '');
            $pCat   = L($p, 'category_name');
            $pUrl   = url(lang(), 'projects/' . e($p['category_slug']) . '/' . e(slugOf($p)));
            $pTags  = array_slice(tech_tags($p['tech_tags']), 0, 3);
            ?>
            <div class="min-w-[85vw] md:min-w-[600px] flex-shrink-0 snap-center group">
                <a href="<?= e($pUrl) ?>" class="block">
                    <div class="relative bg-slate-800 rounded-2xl overflow-hidden aspect-[16/9] border border-slate-700 transition-transform duration-500 group-hover:scale-[1.02]">
                        <?php if ($pImg !== ''): ?>
                            <img src="<?= e($pImg) ?>" alt="<?= e($pTitle) ?>" class="absolute inset-0 w-full h-full object-cover pe-img-zoom" loading="lazy">
                        <?php else: ?>
                            <div class="absolute inset-0 bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center">
                                <div class="w-32 h-32 border-4 border-physio-500/30 rounded-full flex items-center justify-center animate-spin-slow">
                                    <div class="w-16 h-16 bg-physio-500/20 rounded-full blur-xl"></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/50 to-transparent opacity-80"></div>
                        <div class="absolute bottom-0 start-0 p-8 w-full">
                            <div class="flex justify-between items-end">
                                <div>
                                    <span class="text-physio-400 text-sm font-semibold uppercase tracking-wider mb-2 block"><?= e($pCat) ?></span>
                                    <h3 class="text-2xl md:text-3xl font-bold text-white mb-3"><?= e($pTitle) ?></h3>
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach ($pTags as $tag): ?>
                                            <span class="px-2 py-1 bg-slate-800/80 rounded text-xs text-slate-300 backdrop-blur"><?= e($tag) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <span class="w-12 h-12 rounded-full bg-white text-slate-900 flex items-center justify-center opacity-0 group-hover:opacity-100 translate-y-4 group-hover:translate-y-0 transition-all duration-300 shadow-lg shrink-0 ms-4">
                                    <i data-lucide="arrow-up-right" class="w-5 h-5 rtl:-scale-x-100"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- ============ LATEST ARTICLES ============ -->
<section class="py-24 md:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="mb-12 flex flex-col md:flex-row justify-between items-start md:items-end gap-4 reveal">
            <div class="max-w-2xl">
                <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-physio-950"><?= e(t('home.blog.title')) ?></h2>
                <p class="mt-3 text-lg text-slate-500"><?= e(t('home.blog.subtitle')) ?></p>
            </div>
            <a href="<?= e(url(lang(), 'blog')) ?>" class="inline-flex items-center gap-2 text-physio-600 font-semibold group shrink-0">
                <?= e(t('home.blog.viewAll')) ?>
                <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php if (empty($latestPosts)): ?>
                <div class="md:col-span-3 text-center text-slate-400 py-10"><?= e(t('blog.noPosts')) ?></div>
            <?php else: ?>
            <?php foreach ($latestPosts as $post): ?>
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
                            <h3 class="mt-2 text-lg font-bold text-physio-950 leading-snug group-hover:text-physio-600 transition-colors"><?= e($postTitle) ?></h3>
                            <p class="mt-3 text-sm text-slate-500 leading-relaxed line-clamp-3"><?= e(L($post, 'excerpt')) ?></p>
                            <span class="mt-4 inline-flex items-center text-sm font-semibold text-physio-600 group-hover:gap-2 gap-1.5 transition-all">
                                <?= e(t('blog.readMore')) ?>
                                <i data-lucide="arrow-right" class="w-4 h-4 rtl:rotate-180"></i>
                            </span>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============ PROCESS TIMELINE ============ -->
<section class="py-24 bg-slate-50">
    <div class="max-w-4xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-16 reveal">
            <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-physio-950"><?= e(t('process.title')) ?></h2>
            <p class="mt-3 text-lg text-slate-500 max-w-2xl mx-auto"><?= e(t('process.subtitle')) ?></p>
        </div>

        <div class="relative" id="processTimeline">
            <div class="timeline-line"></div>
            <div class="timeline-progress" id="timelineProgress"></div>

            <?php
            $steps = [
                ['icon' => 'search',  'title' => t('process.s1.t'), 'desc' => t('process.s1.d')],
                ['icon' => 'box',     'title' => t('process.s2.t'), 'desc' => t('process.s2.d')],
                ['icon' => 'code-2',  'title' => t('process.s3.t'), 'desc' => t('process.s3.d')],
                ['icon' => 'rocket',  'title' => t('process.s4.t'), 'desc' => t('process.s4.d')],
            ];
            ?>
            <?php foreach ($steps as $i => $step): ?>
                <div class="relative flex gap-6 mb-12 last:mb-0 reveal">
                    <div class="w-16 h-16 rounded-full bg-white border-2 border-physio-500/40 flex items-center justify-center text-physio-600 shrink-0 shadow-sm z-10">
                        <i data-lucide="<?= e($step['icon']) ?>" class="w-6 h-6"></i>
                    </div>
                    <div class="pt-1.5">
                        <span class="text-physio-500 font-bold text-sm">0<?= $i + 1 ?></span>
                        <h3 class="text-xl font-bold text-physio-950 mt-1"><?= e($step['title']) ?></h3>
                        <p class="mt-2 text-slate-500 leading-relaxed"><?= e($step['desc']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ FAQ ============ -->
<section id="faq" class="py-24 bg-white">
    <div class="max-w-3xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-12 reveal">
            <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-physio-950"><?= e(t('faq.title')) ?></h2>
        </div>

        <div class="space-y-3">
            <?php
            $faqs = [
                [t('faq.q1'), t('faq.a1')],
                [t('faq.q2'), t('faq.a2')],
                [t('faq.q3'), t('faq.a3')],
                [t('faq.q4'), t('faq.a4')],
                [t('faq.q5'), t('faq.a5')],
            ];
            ?>
            <?php foreach ($faqs as $q): ?>
                <div class="glass-card rounded-xl overflow-hidden reveal">
                    <button class="faq-btn w-full flex items-center justify-between gap-4 p-5 text-start" type="button">
                        <span class="font-semibold text-physio-950"><?= e($q[0]) ?></span>
                        <i data-lucide="plus" class="w-5 h-5 text-physio-600 shrink-0 transition-transform"></i>
                    </button>
                    <div class="faq-content px-5">
                        <p class="pb-5 text-slate-500 leading-relaxed text-sm"><?= e($q[1]) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ FINAL CTA ============ -->
<section class="py-24 bg-slate-900 relative overflow-hidden">
    <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 15% 50%, rgba(14,165,233,0.35) 0, transparent 40%), radial-gradient(circle at 85% 20%, rgba(59,130,246,0.3) 0, transparent 40%);"></div>
    <div class="max-w-4xl mx-auto px-6 lg:px-8 text-center relative reveal">
        <h2 class="text-3xl md:text-5xl font-bold tracking-tight text-white"><?= e(t('cta.title')) ?></h2>
        <p class="mt-4 text-lg text-slate-400 max-w-2xl mx-auto"><?= e(t('cta.subtitle')) ?></p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="<?= e(cta_telegram_url()) ?>" data-tg-link="<?= e(cta_tg_scheme()) ?>" class="btn-shine w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-medium text-white bg-physio-500 hover:bg-physio-600 rounded-full shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
                <i data-lucide="send" class="w-5 h-5"></i>
                <?= e(t('contact.telegram')) ?>
            </a>
            <a href="<?= e(cta_mailto_url()) ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-medium text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-full shadow-lg transition-all">
                <i data-lucide="mail" class="w-5 h-5"></i>
                <?= e(t('contact.email')) ?>
            </a>
        </div>
    </div>
</section>
