<?php
/**
 * Home page - Premium Corporate & Interactive Design
 */
$heroTitle = setting(lang() === 'fa' ? 'hero_title_fa' : 'hero_title_en', t('hero.title'));
$heroSub = setting(lang() === 'fa' ? 'hero_sub_fa' : 'hero_sub_en', t('hero.subtitle'));
$heroBadge = setting(lang() === 'fa' ? 'hero_badge_fa' : 'hero_badge_en', t('hero.badge'));
?>
<!-- ============ HERO ============ -->
<section id="home" class="relative min-h-screen flex items-center pt-24 pb-16 overflow-hidden bg-slate-50/60">
    <div class="absolute top-1/4 left-1/4 w-[500px] h-[500px] bg-physio-500/15 rounded-full blur-[140px] z-0 pointer-events-none animate-pulse" style="animation-duration: 9s;"></div>
    <div class="absolute bottom-1/4 right-1/4 w-[450px] h-[450px] bg-blue-400/10 rounded-full blur-[130px] z-0 pointer-events-none"></div>

    <canvas id="hero-canvas" class="absolute inset-0 w-full h-full z-0 opacity-100 pointer-events-none"></canvas>
    
    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10 w-full text-center">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/90 backdrop-blur border border-physio-200 text-physio-600 text-xs font-semibold uppercase tracking-wider mb-8 shadow-sm reveal">
            <span class="w-2 h-2 rounded-full bg-physio-500 animate-pulse"></span>
            <span><?= e($heroBadge) ?></span>
        </div>
        
        <h1 class="text-5xl md:text-7xl lg:text-8xl font-bold tracking-tight text-physio-950 max-w-5xl mx-auto leading-[1.1] reveal reveal-delay-1">
            <?= $heroTitle ?>
        </h1>
        
        <p class="mt-8 text-lg md:text-xl text-slate-600 max-w-2xl mx-auto leading-relaxed font-medium reveal reveal-delay-2">
            <?= e($heroSub) ?>
        </p>
        
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4 reveal reveal-delay-3">
            <a href="#capabilities" class="btn-shine w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-base font-medium text-white bg-physio-900 hover:bg-physio-950 rounded-full shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 overflow-hidden">
                <?= e(t('hero.ctaPrimary')) ?>
            </a>
            <a href="<?= e(url(lang(), 'projects')) ?>" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-base font-medium text-physio-900 bg-white/90 backdrop-blur hover:bg-white border border-slate-200 rounded-full shadow-sm hover:shadow-md transition-all">
                <?= e(t('hero.ctaSecondary')) ?>
            </a>
        </div>
    </div>
</section>
<!-- ============ CAPABILITIES ============ -->
<section id="capabilities" class="py-24 md:py-32 bg-white relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:24px_24px] opacity-40 z-0"></div>
    <div class="absolute top-1/2 left-0 w-[600px] h-[600px] bg-physio-50 rounded-full blur-[100px] -translate-y-1/2 -translate-x-1/4 z-0 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
        <div class="mb-16 md:mb-24 reveal">
            <h2 class="text-3xl md:text-5xl font-bold tracking-tight text-physio-950"><?= e(t('cap.title')) ?></h2>
            <p class="mt-4 text-lg text-slate-500 max-w-2xl"><?= e(t('cap.subtitle')) ?></p>
        </div>

        <div class="space-y-32">
            <!-- 01: Core Tech -->
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-24 reveal">
                <div class="w-full lg:w-1/2">
                    <span class="text-physio-500 font-bold text-xl mb-2 block">01</span>
                    <h3 class="text-2xl md:text-3xl font-bold text-physio-950 mb-4"><?= e(t('cap.c0.title')) ?></h3>
                    <p class="text-slate-600 mb-8 text-lg leading-relaxed"><?= e(t('cap.c0.desc')) ?></p>
                    <a href="<?= e(url(lang(), 'projects')) ?>" class="inline-flex items-center text-physio-600 font-semibold group">
                        <?= e(t('cap.c0.link')) ?> <i data-lucide="arrow-right" class="w-4 h-4 mx-1 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>
                    </a>
                </div>
                <div class="w-full lg:w-1/2">
                     <div class="bg-slate-900 rounded-2xl h-[300px] flex items-center justify-center relative overflow-hidden shadow-premium p-6 border border-slate-800">
                        <div class="absolute inset-0 bg-gradient-radial from-physio-600/20 to-transparent"></div>
                        <div class="relative z-10 flex flex-wrap justify-center gap-3">
                            <span class="tech-tag" style="animation-delay: 0s;">Python</span>
                            <span class="tech-tag" style="animation-delay: 0.5s;">C++</span>
                            <span class="tech-tag" style="animation-delay: 1.2s;">React</span>
                            <span class="tech-tag" style="animation-delay: 0.8s;">Django</span>
                            <span class="tech-tag" style="animation-delay: 0.2s;">OpenCV</span>
                            <span class="tech-tag" style="animation-delay: 1.5s;">PHP 8.3</span>
                            <span class="tech-tag" style="animation-delay: 0.6s;">Tailwind</span>
                        </div>
                     </div>
                </div>
            </div>

            <!-- 02: Web Dev -->
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-24 reveal">
                <div class="w-full lg:w-1/2 order-2 lg:order-1">
                    <div class="mock-browser w-full h-[300px] flex flex-col transform transition-transform hover:scale-[1.02] duration-500 shadow-premium">
                        <div class="mock-browser-header">
                            <div class="mock-dot"></div><div class="mock-dot"></div><div class="mock-dot"></div>
                        </div>
                        <div class="p-6 flex-1 bg-slate-50 flex flex-col gap-4">
                            <div class="w-1/3 h-4 bg-slate-200 rounded"></div>
                            <div class="w-full h-32 bg-white border border-slate-100 rounded-lg shadow-sm flex items-center justify-center">
                                <div class="w-1/2 h-2/3 flex flex-col gap-2">
                                    <div class="w-full h-2 bg-physio-100 rounded"></div>
                                    <div class="w-3/4 h-2 bg-physio-100 rounded"></div>
                                    <div class="w-5/6 h-2 bg-physio-100 rounded"></div>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="w-1/2 h-16 bg-white border border-slate-100 rounded shadow-sm"></div>
                                <div class="w-1/2 h-16 bg-white border border-slate-100 rounded shadow-sm"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-full lg:w-1/2 order-1 lg:order-2">
                    <span class="text-physio-500 font-bold text-xl mb-2 block">02</span>
                    <h3 class="text-2xl md:text-3xl font-bold text-physio-950 mb-4"><?= e(t('cap.c3.title')) ?></h3>
                    <p class="text-slate-600 mb-8 text-lg leading-relaxed"><?= e(t('cap.c3.desc')) ?></p>
                    <a href="<?= e(url(lang(), 'projects/web-development')) ?>" class="inline-flex items-center text-physio-600 font-semibold group">
                        <?= e(t('cap.c3.link')) ?> <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>
                    </a>
                </div>
            </div>

            <!-- 03: MATLAB/COMSOL -->
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-24 reveal">
                <div class="w-full lg:w-1/2">
                    <span class="text-physio-500 font-bold text-xl mb-2 block">03</span>
                    <h3 class="text-2xl md:text-3xl font-bold text-physio-950 mb-4"><?= e(t('cap.c1.title')) ?></h3>
                    <p class="text-slate-600 mb-8 text-lg leading-relaxed"><?= e(t('cap.c1.desc')) ?></p>
                    <a href="<?= e(url(lang(), 'projects/simulation')) ?>" class="inline-flex items-center text-physio-600 font-semibold group">
                        <?= e(t('cap.c1.link')) ?> <i data-lucide="arrow-right" class="w-4 h-4 mx-1 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>
                    </a>
                </div>
                <div class="w-full lg:w-1/2">
                    <div class="bg-white rounded-2xl h-[300px] border border-slate-200 flex items-center justify-center overflow-hidden relative shadow-premium">
                        <div class="absolute inset-0 bg-gradient-radial from-physio-50 to-slate-100"></div>
                        <div class="cube-container z-10">
                            <div class="cube">
                                <div class="cube-face face-front"></div>
                                <div class="cube-face face-back"></div>
                                <div class="cube-face face-right"></div>
                                <div class="cube-face face-left"></div>
                                <div class="cube-face face-top"></div>
                                <div class="cube-face face-bottom"></div>
                            </div>
                        </div>
                        <div class="absolute top-4 right-4 flex flex-col gap-1 rtl:right-auto rtl:left-4">
                            <div class="w-16 h-1 bg-gradient-to-r from-red-500 to-blue-500 rounded"></div>
                            <div class="text-[10px] text-slate-400 font-mono text-right rtl:text-left">Mesh: Fine</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 04: AI & Programming -->
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-24 reveal">
                <div class="w-full lg:w-1/2 order-2 lg:order-1">
                     <div class="bg-slate-900 rounded-2xl p-8 h-[300px] flex items-center justify-center relative shadow-premium border border-slate-800">
                        <svg class="w-full h-full" viewBox="0 0 200 100">
                            <path d="M30 50 L80 50 L120 20 L170 20" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="2" stroke-dasharray="4,4" class="animate-[dash_2s_linear_infinite]" />
                            <path d="M80 50 L120 80 L170 80" fill="none" stroke="#0ea5e9" stroke-width="2" />
                            <circle cx="30" cy="50" r="8" fill="#fff" />
                            <text x="30" y="70" fill="#94a3b8" font-size="8" text-anchor="middle" font-family="monospace">Input</text>
                            <rect x="70" y="40" width="20" height="20" rx="4" fill="#0284c7" />
                            <text x="80" y="70" fill="#94a3b8" font-size="8" text-anchor="middle" font-family="monospace">Agent</text>
                            <circle cx="120" cy="20" r="6" fill="#64748b" />
                            <circle cx="120" cy="80" r="6" fill="#fff" />
                            <rect x="160" y="10" width="20" height="20" rx="2" fill="none" stroke="#64748b" stroke-width="2"/>
                            <circle cx="170" cy="80" r="8" fill="#0ea5e9" />
                            <text x="170" y="100" fill="#0ea5e9" font-size="8" text-anchor="middle" font-family="monospace">Output</text>
                        </svg>
                     </div>
                </div>
                <div class="w-full lg:w-1/2 order-1 lg:order-2">
                    <span class="text-physio-500 font-bold text-xl mb-2 block">04</span>
                    <h3 class="text-2xl md:text-3xl font-bold text-physio-950 mb-4"><?= e(t('cap.c2.title')) ?></h3>
                    <p class="text-slate-600 mb-8 text-lg leading-relaxed"><?= e(t('cap.c2.desc')) ?></p>
                    <a href="<?= e(url(lang(), 'projects/programming')) ?>" class="inline-flex items-center text-physio-600 font-semibold group">
                        <?= e(t('cap.c2.link')) ?> <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>
                    </a>
                </div>
            </div>

            <!-- 05: Embedded / IoT -->
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-24 reveal">
                <div class="w-full lg:w-1/2">
                    <span class="text-physio-500 font-bold text-xl mb-2 block">05</span>
                    <h3 class="text-2xl md:text-3xl font-bold text-physio-950 mb-4"><?= e(t('cap.c4.title')) ?></h3>
                    <p class="text-slate-600 mb-8 text-lg leading-relaxed"><?= e(t('cap.c4.desc')) ?></p>
                    <a href="<?= e(url(lang(), 'projects/iot')) ?>" class="inline-flex items-center text-physio-600 font-semibold group">
                        <?= e(t('cap.c4.link')) ?> <i data-lucide="arrow-right" class="w-4 h-4 mx-1 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>
                    </a>
                </div>
                <div class="w-full lg:w-1/2">
                    <div class="bg-slate-900 rounded-2xl h-[300px] flex items-center justify-center relative overflow-hidden shadow-premium border border-slate-800">
                        <svg class="absolute inset-0 w-full h-full opacity-30" viewBox="0 0 200 200" preserveAspectRatio="none">
                            <path d="M0 50 L50 50 L70 70 L100 70" fill="none" stroke="#0ea5e9" stroke-width="1.5" />
                            <path d="M200 150 L150 150 L130 130 L100 130" fill="none" stroke="#0ea5e9" stroke-width="1.5" />
                            <path d="M50 200 L50 150 L70 130 L100 130" fill="none" stroke="#0284c7" stroke-width="1.5" />
                            <path d="M150 0 L150 50 L130 70 L100 70" fill="none" stroke="#0284c7" stroke-width="1.5" />
                            <circle cx="100" cy="100" r="45" fill="none" stroke="rgba(14,165,233,0.3)" stroke-width="1" stroke-dasharray="4,4"/>
                        </svg>
                        <div class="relative z-10 w-32 h-32 bg-slate-800 rounded-lg border-2 border-slate-700 shadow-[0_0_25px_rgba(14,165,233,0.2)] flex items-center justify-center flex-col transform hover:scale-105 transition-transform duration-500">
                            <div class="absolute -top-2.5 w-full flex justify-around px-3"><div class="w-2.5 h-3 bg-slate-500 rounded-sm"></div><div class="w-2.5 h-3 bg-slate-500 rounded-sm"></div><div class="w-2.5 h-3 bg-slate-500 rounded-sm"></div><div class="w-2.5 h-3 bg-slate-500 rounded-sm"></div></div>
                            <div class="absolute -bottom-2.5 w-full flex justify-around px-3"><div class="w-2.5 h-3 bg-slate-500 rounded-sm"></div><div class="w-2.5 h-3 bg-slate-500 rounded-sm"></div><div class="w-2.5 h-3 bg-slate-500 rounded-sm"></div><div class="w-2.5 h-3 bg-slate-500 rounded-sm"></div></div>
                            <div class="absolute -left-2.5 h-full flex flex-col justify-around py-3"><div class="w-3 h-2.5 bg-slate-500 rounded-sm"></div><div class="w-3 h-2.5 bg-slate-500 rounded-sm"></div><div class="w-3 h-2.5 bg-slate-500 rounded-sm"></div><div class="w-3 h-2.5 bg-slate-500 rounded-sm"></div></div>
                            <div class="absolute -right-2.5 h-full flex flex-col justify-around py-3"><div class="w-3 h-2.5 bg-slate-500 rounded-sm"></div><div class="w-3 h-2.5 bg-slate-500 rounded-sm"></div><div class="w-3 h-2.5 bg-slate-500 rounded-sm"></div><div class="w-3 h-2.5 bg-slate-500 rounded-sm"></div></div>
                            <div class="text-physio-500 font-mono text-lg font-bold tracking-wider mb-1">STM32</div>
                            <div class="w-12 h-px bg-slate-600 my-1"></div>
                            <div class="text-slate-400 font-mono text-sm">ESP32</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ FEATURED PROJECTS ============ -->
<section id="projects" class="py-24 bg-slate-900 text-white overflow-hidden relative border-y border-slate-800">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 mb-12 flex flex-col md:flex-row justify-between items-start md:items-end gap-6 reveal">
        <div class="max-w-2xl">
            <h2 class="text-3xl md:text-5xl font-bold tracking-tight"><?= e(t('home.projects.title')) ?></h2>
            <p class="mt-4 text-lg text-slate-400"><?= e(t('home.projects.subtitle')) ?></p>
        </div>
        <div class="flex items-center gap-4 shrink-0">
            <div class="hidden md:flex p-1.5 bg-slate-800 border border-slate-700 rounded-full items-center gap-1 shadow-lg">
                <button id="prevBtn" class="w-12 h-12 rounded-full flex items-center justify-center hover:bg-slate-700 text-slate-400 hover:text-white transition-all">
                    <i data-lucide="chevron-left" class="w-5 h-5 rtl:rotate-180"></i>
                </button>
                <div class="w-px h-5 bg-slate-700"></div>
                <button id="nextBtn" class="w-12 h-12 rounded-full flex items-center justify-center hover:bg-slate-700 text-slate-400 hover:text-white transition-all">
                    <i data-lucide="chevron-right" class="w-5 h-5 rtl:rotate-180"></i>
                </button>
            </div>
            <a href="<?= e(url(lang(), 'projects')) ?>" class="inline-flex items-center gap-2 text-physio-400 font-semibold text-sm hover:text-physio-300 transition-colors">
                <?= e(t('home.projects.viewAll')) ?> <i data-lucide="arrow-right" class="w-4 h-4 rtl:rotate-180"></i>
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
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/50 to-transparent opacity-90"></div>
                        <div class="absolute bottom-0 start-0 p-8 w-full">
                            <div class="flex justify-between items-end">
                                <div>
                                    <span class="text-physio-400 text-sm font-semibold uppercase tracking-wider mb-2 block"><?= e($pCat) ?></span>
                                    <h3 class="text-2xl md:text-3xl font-bold text-white mb-3"><?= e($pTitle) ?></h3>
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach ($pTags as $tag): ?>
                                            <span class="px-2 py-1 bg-slate-800/80 rounded text-xs text-slate-300 backdrop-blur border border-slate-600/50"><?= e($tag) ?></span>
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

<!-- ============ ABOUT ============ -->
<section id="about" class="py-24 md:py-32 bg-slate-50 relative overflow-hidden border-b border-slate-200">
    <div class="absolute right-0 bottom-0 w-[800px] h-[800px] bg-[radial-gradient(circle_at_center,rgba(14,165,233,0.05)_0,transparent_50%)] pointer-events-none"></div>
    <div class="absolute -left-40 top-1/4 w-[400px] h-[400px] border-[40px] border-white rounded-full z-0 pointer-events-none opacity-50"></div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="reveal">
                <h2 class="text-4xl md:text-6xl font-bold tracking-tight text-physio-950 leading-tight mb-8">
                    <span class="block"><?= e(t('about.title_part1')) ?></span>
                    <span class="block text-slate-400 mt-4"><?= e(t('about.title_part2')) ?></span>
                </h2>
                
                <p class="text-xl text-slate-700 mb-6 font-medium leading-relaxed"><?= e(t('about.story.p1')) ?></p>
                <p class="text-lg text-slate-500 mb-8 leading-relaxed"><?= e(t('about.story.p2')) ?></p>
                
                <blockquote class="border-l-4 border-physio-500 pl-6 rtl:border-l-0 rtl:border-r-4 rtl:pl-0 rtl:pr-6 italic text-slate-700 font-medium text-lg bg-white shadow-sm py-4 rounded-r-lg rtl:rounded-r-none rtl:rounded-l-lg mb-10">
                    <?= e(t('phil.desc')) ?>
                </blockquote>

                <div class="pt-2">
                    <a href="<?= e(url(lang(), 'about')) ?>" class="btn-shine inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-physio-600 to-physio-900 text-white font-bold rounded-full shadow-lg hover:shadow-glow hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
                        <span class="relative z-10"><?= e(t('about.cta')) ?></span>
                        <i data-lucide="arrow-right" class="w-5 h-5 relative z-10 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>
                    </a>
                </div>
            </div>
            
            <div class="flex justify-center reveal reveal-delay-2 h-[400px] relative w-full">
                <div class="relative w-full max-w-[400px] h-[400px] flex items-center justify-center">
                    <svg class="absolute inset-0 w-full h-full pointer-events-none z-0" viewBox="0 0 400 400">
                        <line x1="200" y1="200" x2="110" y2="110" stroke="#cbd5e1" stroke-width="2" class="line-dashed" />
                        <line x1="200" y1="200" x2="300" y2="130" stroke="#cbd5e1" stroke-width="2" class="line-dashed-reverse" />
                        <line x1="200" y1="200" x2="290" y2="290" stroke="#cbd5e1" stroke-width="2" class="line-dashed" />
                        <line x1="200" y1="200" x2="100" y2="270" stroke="#0ea5e9" stroke-width="2" class="line-dashed-reverse opacity-60" />
                    </svg>
                    <div class="w-64 h-64 border border-slate-300 rounded-full absolute animate-[spin_20s_linear_infinite] z-0"></div>
                    <div class="w-48 h-48 border border-physio-200 rounded-full absolute animate-[spin_15s_linear_infinite_reverse] z-0"></div>
                    
                    <div class="w-32 h-32 bg-white rounded-3xl rotate-45 shadow-premium flex items-center justify-center overflow-hidden z-10 relative border border-slate-100">
                        <div class="w-full h-full bg-physio-50/50 flex items-center justify-center -rotate-45 font-bold text-physio-900 text-3xl tracking-tighter">PE</div>
                    </div>
                    
                    <div class="absolute bg-white shadow-md border border-slate-100 px-4 py-2 rounded-xl text-sm font-bold text-slate-700 z-20" style="top: 110px; left: 110px; transform: translate(-50%, -50%);"><?= e(t('domain.sw')) ?></div>
                    <div class="absolute bg-white shadow-md border border-slate-100 px-4 py-2 rounded-xl text-sm font-bold text-slate-700 z-20" style="top: 130px; left: 300px; transform: translate(-50%, -50%);"><?= e(t('domain.matlab')) ?></div>
                    <div class="absolute bg-white shadow-md border border-slate-100 px-4 py-2 rounded-xl text-sm font-bold text-slate-700 z-20" style="top: 290px; left: 290px; transform: translate(-50%, -50%);"><?= e(t('domain.math')) ?></div>
                    <div class="absolute bg-white shadow-md border border-physio-200 px-4 py-2 rounded-xl text-sm font-bold text-physio-600 z-20" style="top: 270px; left: 100px; transform: translate(-50%, -50%);"><?= e(t('domain.ai')) ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ PROCESS TIMELINE ============ -->
<section id="process" class="py-24 bg-white relative overflow-hidden">
    <div class="max-w-4xl mx-auto px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16 reveal">
            <h2 class="text-3xl md:text-5xl font-bold tracking-tight text-physio-950"><?= e(t('process.title')) ?></h2>
            <p class="mt-4 text-lg text-slate-500 max-w-2xl mx-auto"><?= e(t('process.subtitle')) ?></p>
        </div>
        
        <div class="pe-timeline-container" id="processTimeline">
            <div class="pe-timeline-line"></div>
            <div class="pe-timeline-progress" id="timelineProgress"></div>

            <div class="pe-process-wrapper">
                <?php
                $steps = [
                    ['num' => '01', 'title' => t('process.s1.t'), 'desc' => t('process.s1.d')],
                    ['num' => '02', 'title' => t('process.s2.t'), 'desc' => t('process.s2.d')],
                    ['num' => '03', 'title' => t('process.s3.t'), 'desc' => t('process.s3.d')],
                    ['num' => '04', 'title' => t('process.s4.t'), 'desc' => t('process.s4.d')],
                    ['num' => '05', 'title' => t('process.s5.t'), 'desc' => t('process.s5.d')],
                ];
                ?>
                <?php foreach ($steps as $step): ?>
                <div class="pe-step reveal">
                    <div class="pe-dot"></div>
                    <span class="text-physio-500 font-bold text-sm mb-1 block"><?= e($step['num']) ?> — <?= e(t('process.phase')) ?></span>
                    <h3 class="text-2xl font-bold text-physio-950 mb-2"><?= e($step['title']) ?></h3>
                    <p class="text-slate-600"><?= e($step['desc']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ============ LATEST ARTICLES ============ -->
<section id="articles" class="py-24 bg-slate-50 relative z-20 border-t border-slate-200">
    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-blue-100/50 rounded-full blur-[120px] z-0 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-16 gap-6 reveal">
            <div class="max-w-2xl">
                <h2 class="text-3xl md:text-5xl font-bold tracking-tight text-physio-950"><?= e(t('home.blog.title')) ?></h2>
                <p class="mt-4 text-lg text-slate-500"><?= e(t('home.blog.subtitle')) ?></p>
            </div>
            <a href="<?= e(url(lang(), 'blog')) ?>" class="hidden md:inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white hover:bg-slate-100 border border-slate-200 text-physio-900 font-medium transition-all group shrink-0 shadow-sm">
                <?= e(t('home.blog.viewAll')) ?> <i data-lucide="arrow-right" class="w-4 h-4 text-physio-500 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php if (empty($latestPosts)): ?>
                <div class="md:col-span-3 text-center text-slate-400 py-10"><?= e(t('blog.noPosts')) ?></div>
            <?php else: ?>
            <?php foreach ($latestPosts as $index => $post): ?>
                <?php
                $postTitle = L($post, 'title');
                $postImg   = (string) ($post['image'] ?? '');
                $postUrl   = url(lang(), 'blog/' . e(slugOf($post)));
                $delayClass = $index == 1 ? 'reveal-delay-1' : ($index == 2 ? 'reveal-delay-2' : '');
                ?>
                <a href="<?= e($postUrl) ?>" class="group block reveal <?= $delayClass ?> bg-white rounded-2xl p-3 shadow-sm hover:shadow-md transition-shadow border border-slate-100">
                    <div class="relative overflow-hidden rounded-xl mb-5 aspect-[4/3] bg-slate-100 transition-all duration-500">
                        <?php if ($postImg !== ''): ?>
                            <img src="<?= e($postImg) ?>" alt="<?= e($postTitle) ?>" class="w-full h-full object-cover pe-img-zoom" loading="lazy">
                        <?php else: ?>
                            <div class="absolute inset-0 bg-slate-900 transition-transform duration-700 group-hover:scale-105 flex items-center justify-center">
                                <div class="absolute inset-0" style="background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 16px 16px;"></div>
                                <div class="text-physio-500/50 font-mono text-4xl font-bold">&lt;/&gt;</div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-col px-2">
                        <span class="text-sm text-slate-400 mb-2 font-mono"><?= e(format_date((string) ($post['published_at'] ?? $post['created_at']))) ?></span>
                        <h3 class="text-xl font-bold text-physio-950 mb-3 leading-snug group-hover:text-physio-600 transition-colors"><?= e($postTitle) ?></h3>
                        <div class="mt-2 flex items-center text-sm font-semibold text-physio-600 opacity-0 -translate-x-2 rtl:translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 rtl:group-hover:translate-x-0 transition-all duration-300 pb-2">
                            <?= e(t('blog.readMore')) ?> <i data-lucide="arrow-right" class="w-4 h-4 ml-1 rtl:ml-0 rtl:mr-1 rtl:rotate-180"></i>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="mt-10 flex justify-center md:hidden reveal">
            <a href="<?= e(url(lang(), 'blog')) ?>" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-white border border-slate-200 text-physio-900 font-medium transition-all group w-full justify-center shadow-sm">
                <?= e(t('home.blog.viewAll')) ?> <i data-lucide="arrow-right" class="w-4 h-4 text-physio-500 rtl:rotate-180"></i>
            </a>
        </div>
    </div>
</section>

<!-- ============ FAQ ============ -->
<section id="faq" class="py-24 bg-white border-t border-slate-100">
    <div class="max-w-3xl mx-auto px-6 lg:px-8">
        <h2 class="text-3xl md:text-4xl font-bold text-center text-physio-950 mb-12 reveal"><?= e(t('faq.title')) ?></h2>
        <div class="space-y-4 reveal reveal-delay-1" id="faqContainer">
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
                <div class="border-b border-slate-200 pb-4">
                    <button class="faq-btn w-full flex justify-between items-center py-4 text-left rtl:text-right font-semibold text-lg text-slate-800 hover:text-physio-600 transition-colors">
                        <span><?= e($q[0]) ?></span>
                        <i data-lucide="plus" class="w-5 h-5 text-slate-400 transition-transform duration-300"></i>
                    </button>
                    <div class="faq-content h-0 overflow-hidden transition-all duration-300 opacity-0">
                        <p class="text-slate-600 pb-4 pr-8 rtl:pr-0 rtl:pl-8"><?= e($q[1]) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ BANNER ============ -->
<section id="banner" class="w-full h-[50vh] md:h-[70vh] relative overflow-hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-[url('/assets/images/banner-bg.jpg')] bg-cover bg-center bg-fixed bg-no-repeat transition-transform duration-[10s] hover:scale-105"></div>
    <div class="absolute inset-0 bg-physio-950/70 backdrop-blur-[2px] mix-blend-multiply"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-physio-950 via-transparent to-physio-950/40"></div>
    
    <div class="relative z-10 text-center px-6 reveal">
        <h2 class="text-3xl md:text-5xl lg:text-7xl font-bold text-white tracking-widest uppercase mb-6"><?= e(t('banner.title')) ?></h2>
        <p class="text-physio-100/80 text-lg md:text-xl font-light tracking-wide max-w-2xl mx-auto mb-8"><?= e(t('banner.subtitle')) ?></p>
        <div class="w-24 h-1 bg-gradient-to-r from-physio-500 to-blue-500 mx-auto rounded-full shadow-glow"></div>
    </div>
</section>

<!-- ============ FINAL CTA ============ -->
<section id="contact" class="py-32 relative overflow-hidden bg-physio-950 text-white">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute w-[500px] h-[500px] bg-physio-500 rounded-full blur-[120px] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 mix-blend-screen"></div>
        <div class="absolute inset-0" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 40px 40px;"></div>
    </div>
    
    <div class="max-w-4xl mx-auto px-6 text-center relative z-10 reveal">
       <h2 class="text-4xl md:text-6xl font-bold tracking-tight mb-6"><?= t('cta.title') ?></h2>
        <p class="text-xl text-slate-400 mb-10 max-w-2xl mx-auto"><?= e(t('cta.desc')) ?></p>
        
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="<?= e(url(lang(), 'contact')) ?>" class="btn-shine relative overflow-hidden px-8 py-4 bg-white text-physio-950 font-bold rounded-full hover:bg-slate-100 transition-colors shadow-glow">
                <?= e(t('cta.btnPrimary')) ?>
            </a>
            <a href="<?= e(cta_telegram_url()) ?>" data-tg-link="<?= e(cta_tg_scheme()) ?>" class="px-8 py-4 border border-slate-700 bg-slate-900/50 backdrop-blur text-white font-medium rounded-full hover:bg-slate-800 transition-colors inline-flex items-center justify-center gap-2">
                <i data-lucide="send" class="w-5 h-5 rtl:scale-x-[-1]"></i> <?= e(t('cta.telegram')) ?>
            </a>
        </div>
    </div>
</section>