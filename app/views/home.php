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
<section id="capabilities" class="py-24 md:py-32 bg-white relative overflow-hidden overflow-x-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:24px_24px] opacity-40 z-0"></div>
    <div class="absolute top-1/2 left-0 w-[600px] h-[600px] bg-physio-50 rounded-full blur-[100px] -translate-y-1/2 -translate-x-1/4 z-0 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
        <div class="mb-16 md:mb-24 reveal">
            <h2 class="text-3xl md:text-5xl font-bold tracking-tight text-physio-950"><?= e(t('cap.title')) ?></h2>
            <p class="mt-4 text-lg text-slate-500 max-w-2xl"><?= e(t('cap.subtitle')) ?></p>
        </div>

        <div class="space-y-32">
            <!-- 01: Core Tech (ویدیو پلی شونده با اسکرول) -->
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-24">
                <div class="w-full lg:w-1/2 reveal-from-right">
                    <span class="text-physio-500 font-bold text-xl mb-2 block">01</span>
                    <h3 class="text-2xl md:text-3xl font-bold text-physio-950 mb-4"><?= e(t('cap.c0.title')) ?></h3>
                    <p class="text-slate-600 mb-8 text-lg leading-relaxed"><?= e(t('cap.c0.desc')) ?></p>
                    <a href="<?= e(url(lang(), 'projects')) ?>" class="inline-flex items-center text-physio-600 font-semibold group">
                        <?= e(t('cap.c0.link')) ?> <i data-lucide="arrow-right" class="w-4 h-4 mx-1 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>
                    </a>
                </div>
                <div class="w-full lg:w-1/2 reveal-from-left">
                     <div class="bg-slate-950 rounded-2xl h-[300px] flex items-center justify-center relative overflow-hidden shadow-premium border border-slate-800">
                        <video src="/assets/videos/code.mp4" muted loop playsinline class="scroll-play-vid absolute inset-0 w-full h-full object-cover opacity-50 transition-opacity duration-700"></video>
                        <div class="play-icon-overlay absolute inset-0 flex items-center justify-center pointer-events-none z-10 transition-all duration-700">
                            <div class="w-16 h-16 rounded-full bg-slate-900/50 backdrop-blur-sm border border-white/20 flex items-center justify-center shadow-lg">
                                <i data-lucide="play" class="w-6 h-6 text-white/90 translate-x-0.5"></i>
                            </div>
                        </div>
                        <div class="absolute bottom-4 left-0 w-full flex flex-wrap justify-center gap-2 z-10 pointer-events-none opacity-90">
                            <span class="px-2 py-1 bg-slate-900/60 backdrop-blur rounded text-xs text-slate-200 border border-slate-700/50">Python</span>
                            <span class="px-2 py-1 bg-slate-900/60 backdrop-blur rounded text-xs text-slate-200 border border-slate-700/50">C++</span>
                            <span class="px-2 py-1 bg-slate-900/60 backdrop-blur rounded text-xs text-slate-200 border border-slate-700/50">OpenCV</span>
                        </div>
                     </div>
                </div>
            </div>

            <!-- 02: Web Dev (عکس بلند اسکرول شونده) -->
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-24">
                <div class="w-full lg:w-1/2 order-2 lg:order-1 reveal-from-right">
                    <div class="mock-browser w-full h-[300px] flex flex-col transform transition-transform hover:scale-[1.02] duration-500 shadow-premium overflow-hidden group border border-slate-200">
                        <div class="mock-browser-header shrink-0 relative z-10 bg-slate-100 border-b border-slate-200 flex items-center px-4 gap-2 h-8">
                            <div class="w-2.5 h-2.5 rounded-full bg-rose-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-amber-400"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-400"></div>
                        </div>
                        <!-- عکس بلند که با موس اسکرول می‌شود -->
                        <div class="w-full h-full bg-cover bg-top group-hover:bg-bottom transition-all ease-in-out cursor-ns-resize" 
                             style="background-image: url('/assets/images/web.jpg'); transition-duration: 7s;">
                        </div>
                    </div>
                </div>
                <div class="w-full lg:w-1/2 order-1 lg:order-2 reveal-from-left">
                    <span class="text-physio-500 font-bold text-xl mb-2 block">02</span>
                    <h3 class="text-2xl md:text-3xl font-bold text-physio-950 mb-4"><?= e(t('cap.c3.title')) ?></h3>
                    <p class="text-slate-600 mb-8 text-lg leading-relaxed"><?= e(t('cap.c3.desc')) ?></p>
                    <a href="<?= e(url(lang(), 'projects/web-development')) ?>" class="inline-flex items-center text-physio-600 font-semibold group">
                        <?= e(t('cap.c3.link')) ?> <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>
                    </a>
                </div>
            </div>

            <!-- 03: MATLAB/COMSOL (ویدیو پلی شونده با اسکرول) -->
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-24">
                <div class="w-full lg:w-1/2 reveal-from-right">
                    <span class="text-physio-500 font-bold text-xl mb-2 block">03</span>
                    <h3 class="text-2xl md:text-3xl font-bold text-physio-950 mb-4"><?= e(t('cap.c1.title')) ?></h3>
                    <p class="text-slate-600 mb-8 text-lg leading-relaxed"><?= e(t('cap.c1.desc')) ?></p>
                    <a href="<?= e(url(lang(), 'projects/simulation')) ?>" class="inline-flex items-center text-physio-600 font-semibold group">
                        <?= e(t('cap.c1.link')) ?> <i data-lucide="arrow-right" class="w-4 h-4 mx-1 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>
                    </a>
                </div>
                <div class="w-full lg:w-1/2 reveal-from-left">
                    <div class="bg-slate-950 rounded-2xl h-[300px] border border-slate-800 flex items-center justify-center overflow-hidden relative shadow-premium">
                        <video src="/assets/videos/sim.mp4" muted loop playsinline class="scroll-play-vid absolute inset-0 w-full h-full object-cover opacity-50 transition-opacity duration-700"></video>
                        <div class="play-icon-overlay absolute inset-0 flex items-center justify-center pointer-events-none z-10 transition-all duration-700">
                            <div class="w-16 h-16 rounded-full bg-slate-900/50 backdrop-blur-sm border border-white/20 flex items-center justify-center shadow-lg">
                                <i data-lucide="play" class="w-6 h-6 text-white/90 translate-x-0.5"></i>
                            </div>
                        </div>
                        <div class="absolute top-4 right-4 flex flex-col gap-1 rtl:right-auto rtl:left-4 z-10 pointer-events-none">
                            <div class="w-16 h-1 bg-gradient-to-r from-red-500 to-blue-500 rounded"></div>
                            <div class="text-[10px] text-white/90 font-mono text-right rtl:text-left drop-shadow-md">Mesh: Fine</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 04: AI & Programming (ویدیو پلی شونده با اسکرول) -->
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-24">
                <div class="w-full lg:w-1/2 order-2 lg:order-1 reveal-from-right">
                     <div class="bg-slate-950 rounded-2xl h-[300px] border border-slate-800 flex items-center justify-center overflow-hidden relative shadow-premium">
                        <video src="/assets/videos/ai-programming.mp4" muted loop playsinline class="scroll-play-vid absolute inset-0 w-full h-full object-cover opacity-50 transition-opacity duration-700"></video>
                        <div class="play-icon-overlay absolute inset-0 flex items-center justify-center pointer-events-none z-10 transition-all duration-700">
                            <div class="w-16 h-16 rounded-full bg-slate-900/50 backdrop-blur-sm border border-white/20 flex items-center justify-center shadow-lg">
                                <i data-lucide="play" class="w-6 h-6 text-white/90 translate-x-0.5"></i>
                            </div>
                        </div>
                        <div class="absolute bottom-4 left-0 w-full flex flex-wrap justify-center gap-2 z-10 pointer-events-none opacity-90">
                            <span class="px-2 py-1 bg-slate-900/60 backdrop-blur rounded text-xs text-slate-200 border border-slate-700/50">Deep Learning</span>
                            <span class="px-2 py-1 bg-slate-900/60 backdrop-blur rounded text-xs text-slate-200 border border-slate-700/50">Computer Vision</span>
                        </div>
                     </div>
                </div>
                <div class="w-full lg:w-1/2 order-1 lg:order-2 reveal-from-left">
                    <span class="text-physio-500 font-bold text-xl mb-2 block">04</span>
                    <h3 class="text-2xl md:text-3xl font-bold text-physio-950 mb-4"><?= e(t('cap.c2.title')) ?></h3>
                    <p class="text-slate-600 mb-8 text-lg leading-relaxed"><?= e(t('cap.c2.desc')) ?></p>
                    <a href="<?= e(url(lang(), 'projects/programming')) ?>" class="inline-flex items-center text-physio-600 font-semibold group">
                        <?= e(t('cap.c2.link')) ?> <i data-lucide="arrow-right" class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>
                    </a>
                </div>
            </div>

            <!-- 05: Embedded / IoT (ویدیو پلی شونده با اسکرول) -->
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-24">
                <div class="w-full lg:w-1/2 reveal-from-right">
                    <span class="text-physio-500 font-bold text-xl mb-2 block">05</span>
                    <h3 class="text-2xl md:text-3xl font-bold text-physio-950 mb-4"><?= e(t('cap.c4.title')) ?></h3>
                    <p class="text-slate-600 mb-8 text-lg leading-relaxed"><?= e(t('cap.c4.desc')) ?></p>
                    <a href="<?= e(url(lang(), 'projects/iot')) ?>" class="inline-flex items-center text-physio-600 font-semibold group">
                        <?= e(t('cap.c4.link')) ?> <i data-lucide="arrow-right" class="w-4 h-4 mx-1 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 rtl:rotate-180"></i>
                    </a>
                </div>
                <div class="w-full lg:w-1/2 reveal-from-left">
                    <div class="bg-slate-950 rounded-2xl h-[300px] border border-slate-800 flex items-center justify-center overflow-hidden relative shadow-premium">
                        <video src="/assets/videos/iot-embedded.mp4" muted loop playsinline class="scroll-play-vid absolute inset-0 w-full h-full object-cover opacity-50 transition-opacity duration-700"></video>
                        <div class="play-icon-overlay absolute inset-0 flex items-center justify-center pointer-events-none z-10 transition-all duration-700">
                            <div class="w-16 h-16 rounded-full bg-slate-900/50 backdrop-blur-sm border border-white/20 flex items-center justify-center shadow-lg">
                                <i data-lucide="play" class="w-6 h-6 text-white/90 translate-x-0.5"></i>
                            </div>
                        </div>
                        <div class="absolute bottom-4 left-0 w-full flex flex-wrap justify-center gap-2 z-10 pointer-events-none opacity-90">
                            <span class="px-2 py-1 bg-slate-900/60 backdrop-blur rounded text-xs text-slate-200 border border-slate-700/50">STM32 / ESP32</span>
                            <span class="px-2 py-1 bg-slate-900/60 backdrop-blur rounded text-xs text-slate-200 border border-slate-700/50">MQTT / IoT</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- ============ FEATURED PROJECTS ============ -->
<section id="projects" class="py-24 bg-slate-950 text-white overflow-hidden relative border-y border-slate-800">
    
    <!-- Abstract Background: Grid + Safe Terminal -->
    <div class="absolute inset-0 z-0 pointer-events-none flex items-center justify-center overflow-hidden">
        <!-- Glow effects -->
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-physio-600/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-purple-600/10 rounded-full blur-[120px]"></div>
        
        <!-- Engineering Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#1e293b_1px,transparent_1px),linear-gradient(to_bottom,#1e293b_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_50%,#000_70%,transparent_100%)] opacity-40"></div>
    </div>

    <!-- Animated Terminal Box (Confined to avoid text overlap) -->
    <div class="absolute right-[5%] rtl:right-auto rtl:left-[5%] top-[15%] w-[350px] md:w-[450px] h-[280px] bg-slate-900/30 backdrop-blur-sm border border-slate-800/50 rounded-xl overflow-hidden z-0 hidden lg:flex flex-col opacity-30 pointer-events-none shadow-2xl">
        <div class="h-8 bg-slate-900/80 border-b border-slate-800/50 flex items-center px-4 gap-2">
            <div class="w-2.5 h-2.5 rounded-full bg-rose-500/40"></div>
            <div class="w-2.5 h-2.5 rounded-full bg-amber-500/40"></div>
            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500/40"></div>
            <!-- تنظیم حاشیه برای فارسی و انگلیسی -->
            <span class="ml-2 rtl:mr-2 rtl:ml-0 text-[10px] font-mono text-slate-500">physio_core ~ bash</span>
        </div>
        <div class="p-5 flex-1 text-left" dir="ltr">
            <span id="terminal-bg-text" class="text-physio-400/80 font-mono text-sm md:text-base whitespace-pre-wrap leading-relaxed"></span><span class="inline-block w-2 h-4 bg-physio-400/80 ml-1 animate-pulse align-middle"></span>
        </div>
    </div>
        <div class="p-5 flex-1 text-left" dir="ltr">
            <span id="terminal-bg-text" class="text-physio-400/80 font-mono text-sm md:text-base whitespace-pre-wrap leading-relaxed"></span><span class="inline-block w-2 h-4 bg-physio-400/80 ml-1 animate-pulse align-middle"></span>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 mb-12 flex flex-col md:flex-row justify-between items-start md:items-end gap-6 relative z-10 reveal">
        <div class="max-w-2xl">
            <h2 class="text-3xl md:text-5xl font-bold tracking-tight"><?= e(t('home.projects.title')) ?></h2>
            <p class="mt-4 text-lg text-slate-400"><?= e(t('home.projects.subtitle')) ?></p>
        </div>
        <div class="flex flex-wrap items-center gap-4 shrink-0">
            <!-- Header Controls -->
            <div class="hidden md:flex p-1.5 bg-slate-800/80 backdrop-blur border border-slate-700 rounded-full items-center gap-1 shadow-lg">
                <button id="projPrevBtn" class="w-12 h-12 rounded-full flex items-center justify-center hover:bg-physio-600 hover:shadow-[0_0_15px_rgba(14,165,233,0.4)] text-slate-400 hover:text-white transition-all">
                    <i data-lucide="chevron-left" class="w-5 h-5 rtl:rotate-180"></i>
                </button>
                <div class="w-px h-5 bg-slate-700"></div>
                <button id="projNextBtn" class="w-12 h-12 rounded-full flex items-center justify-center hover:bg-physio-600 hover:shadow-[0_0_15px_rgba(14,165,233,0.4)] text-slate-400 hover:text-white transition-all">
                    <i data-lucide="chevron-right" class="w-5 h-5 rtl:rotate-180"></i>
                </button>
            </div>
            <a href="<?= e(url(lang(), 'projects')) ?>" class="inline-flex items-center gap-2 text-physio-400 font-semibold text-sm hover:text-white transition-colors bg-slate-800/50 px-5 py-2.5 rounded-full border border-slate-700/50 backdrop-blur shadow-sm hover:shadow-[0_0_15px_rgba(14,165,233,0.3)]">
                <?= e(t('home.projects.viewAll')) ?> <i data-lucide="arrow-right" class="w-4 h-4 rtl:rotate-180"></i>
            </a>
        </div>
    </div>

    <!-- ============ SLIDER AREA WITH FLOATING BUTTONS ============ -->
    <div class="relative z-10 w-full max-w-[100vw] group/slider mt-4">
        
        <!-- Floating Right Button (Previous in RTL) -->
        <button id="slideRightBtn" class="absolute right-2 md:right-6 top-1/2 -translate-y-1/2 z-40 w-12 h-12 md:w-14 md:h-14 bg-slate-900/80 backdrop-blur-xl border border-slate-600 rounded-full flex items-center justify-center text-white shadow-[0_0_20px_rgba(14,165,233,0.4)] hover:bg-physio-600 hover:scale-110 hover:border-physio-400 transition-all duration-300 opacity-90 md:opacity-0 md:group-hover/slider:opacity-100">
            <i data-lucide="chevron-right" class="w-6 h-6 rtl:rotate-180"></i>
        </button>

        <!-- Floating Left Button (Next in RTL) -->
        <button id="slideLeftBtn" class="absolute left-2 md:left-6 top-1/2 -translate-y-1/2 z-40 w-12 h-12 md:w-14 md:h-14 bg-slate-900/80 backdrop-blur-xl border border-slate-600 rounded-full flex items-center justify-center text-white shadow-[0_0_20px_rgba(14,165,233,0.4)] hover:bg-physio-600 hover:scale-110 hover:border-physio-400 transition-all duration-300 opacity-90 md:opacity-0 md:group-hover/slider:opacity-100">
            <i data-lucide="chevron-left" class="w-6 h-6 rtl:rotate-180"></i>
        </button>

        <div class="slider-container flex gap-6 px-6 md:px-12 overflow-x-auto hide-scrollbar snap-x snap-mandatory py-8 cursor-grab" id="projectSlider">
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
                <div class="min-w-[85vw] md:min-w-[600px] flex-shrink-0 snap-center group p-2">
                    <a href="<?= e($pUrl) ?>" class="block">
                        <div class="neon-glass-wrapper aspect-[16/9] transition-transform duration-500 group-hover:scale-[1.02]">
                            <div class="neon-glass-inner">
                                <?php if ($pImg !== ''): ?>
                                    <img src="<?= e($pImg) ?>" alt="<?= e($pTitle) ?>" class="absolute inset-0 w-full h-full object-cover pe-img-zoom opacity-80" loading="lazy">
                                <?php else: ?>
                                    <div class="absolute inset-0 bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center">
                                        <div class="w-32 h-32 border-4 border-physio-500/30 rounded-full flex items-center justify-center animate-spin-slow">
                                            <div class="w-16 h-16 bg-physio-500/20 rounded-full blur-xl"></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/60 to-transparent opacity-95"></div>
                                <div class="absolute bottom-0 start-0 p-8 w-full z-20">
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
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
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
                <!-- استفاده از dir="ltr" و مختصات درصدی برای تطابق کامل خطوط و باکس‌ها در موبایل و دسکتاپ -->
                <div class="relative w-full max-w-[400px] h-[400px] flex items-center justify-center" dir="ltr">
                    
                    <!-- Glow Background (هاله نورانی زیر چیپ) -->
                    <div class="absolute w-[160px] h-[160px] bg-physio-500/20 blur-2xl rounded-full z-0 pointer-events-none"></div>

                    <!-- Concentric Rings (حلقه‌های پس‌زمینه) -->
                    <div class="w-[340px] h-[340px] border border-slate-200 border-dashed rounded-full absolute z-0 pointer-events-none"></div>
                    <div class="w-[260px] h-[260px] border border-slate-200 rounded-full absolute z-0 pointer-events-none"></div>
                    <div class="w-[180px] h-[180px] border border-physio-400 rounded-full absolute z-0 pointer-events-none shadow-[0_0_15px_rgba(14,165,233,0.1)]"></div>
                    
                    <!-- Connection Lines (SVG) - خطوط ۴ گانه اتصال به تخصص‌ها -->
                    <svg class="absolute inset-0 w-full h-full pointer-events-none z-0" viewBox="0 0 400 400">
                        <!-- Top Left (طراحی وب) -->
                        <path d="M 160 160 L 110 110 L 40 110" fill="none" stroke="#0ea5e9" stroke-width="1.5" stroke-dasharray="4 4" />
                        <circle cx="110" cy="110" r="3.5" fill="#0ea5e9" />
                        <circle cx="40" cy="110" r="3.5" fill="#0ea5e9" />

                        <!-- Bottom Left (اینترنت اشیا) -->
                        <path d="M 160 240 L 110 290 L 40 290" fill="none" stroke="#64748b" stroke-width="1.5" stroke-dasharray="4 4" />
                        <circle cx="110" cy="290" r="3.5" fill="#64748b" />
                        <circle cx="40" cy="290" r="3.5" fill="#64748b" />

                        <!-- Top Right (متلب / کامسول) -->
                        <path d="M 240 160 L 290 110 L 360 110" fill="none" stroke="#64748b" stroke-width="1.5" stroke-dasharray="4 4" />
                        <circle cx="290" cy="110" r="3.5" fill="#64748b" />
                        <circle cx="360" cy="110" r="3.5" fill="#64748b" />

                        <!-- Bottom Right (هوش مصنوعی) -->
                        <path d="M 240 240 L 290 290 L 360 290" fill="none" stroke="#0ea5e9" stroke-width="1.5" stroke-dasharray="4 4" />
                        <circle cx="290" cy="290" r="3.5" fill="#0ea5e9" />
                        <circle cx="360" cy="290" r="3.5" fill="#0ea5e9" />
                    </svg>
                    
                    <!-- Central Chip (چیپست مرکزی PE) -->
                    <div class="relative z-10 w-[120px] h-[120px] bg-[#0f172a] rounded-[24px] shadow-[0_15px_40px_rgba(14,165,233,0.35)] flex items-center justify-center border border-slate-800">
                        <!-- Top Pins (پایه‌های بالا) -->
                        <div class="absolute -top-1.5 w-full flex justify-center gap-3">
                            <div class="w-3 h-2 bg-physio-500 rounded-t-sm shadow-[0_-2px_5px_rgba(14,165,233,0.5)]"></div>
                            <div class="w-3 h-2 bg-physio-500 rounded-t-sm shadow-[0_-2px_5px_rgba(14,165,233,0.5)]"></div>
                            <div class="w-3 h-2 bg-physio-500 rounded-t-sm shadow-[0_-2px_5px_rgba(14,165,233,0.5)]"></div>
                        </div>
                        <!-- Bottom Pins (پایه‌های پایین) -->
                        <div class="absolute -bottom-1.5 w-full flex justify-center gap-3">
                            <div class="w-3 h-2 bg-physio-500 rounded-b-sm shadow-[0_2px_5px_rgba(14,165,233,0.5)]"></div>
                            <div class="w-3 h-2 bg-physio-500 rounded-b-sm shadow-[0_2px_5px_rgba(14,165,233,0.5)]"></div>
                            <div class="w-3 h-2 bg-physio-500 rounded-b-sm shadow-[0_2px_5px_rgba(14,165,233,0.5)]"></div>
                        </div>
                        
                        <!-- Inner Screen -->
                        <div class="w-[92px] h-[92px] bg-[#020617] rounded-[18px] flex items-center justify-center border border-slate-800 shadow-inner">
                            <span class="font-bold text-white text-4xl tracking-tighter drop-shadow-[0_0_10px_rgba(255,255,255,0.6)]">PE</span>
                        </div>
                    </div>
                    
                    <!-- Labels (۴ شاخه تخصصی با مختصات دقیق درصدی) -->
                    
                    <!-- 1. Web Design (طراحی وب) -->
                    <div class="absolute top-[27.5%] left-[18.75%] -translate-x-1/2 -translate-y-1/2 bg-white shadow-xl border border-slate-100 px-4 py-2 rounded-xl text-[12px] md:text-sm font-bold text-slate-800 z-20 whitespace-nowrap">
                        <?= lang() === 'fa' ? 'طراحی وب' : 'Web Design' ?>
                    </div>

                    <!-- 2. IoT (اینترنت اشیا) -->
                    <div class="absolute top-[72.5%] left-[18.75%] -translate-x-1/2 -translate-y-1/2 bg-slate-800 shadow-xl border border-slate-700 px-4 py-2 rounded-xl text-[12px] md:text-sm font-bold text-slate-200 z-20 whitespace-nowrap">
                        <?= lang() === 'fa' ? 'اینترنت اشیا' : 'IoT' ?>
                    </div>

                    <!-- 3. MATLAB / COMSOL -->
                    <div class="absolute top-[27.5%] left-[81.25%] -translate-x-1/2 -translate-y-1/2 bg-[#0f172a] shadow-[0_10px_30px_rgba(14,165,233,0.25)] border border-slate-800 px-4 py-2 rounded-xl text-[12px] md:text-sm font-bold text-physio-400 z-20 whitespace-nowrap text-center">
                        <?= lang() === 'fa' ? 'متلب / کامسول' : 'MATLAB / COMSOL' ?>
                    </div>

                    <!-- 4. AI (هوش مصنوعی) -->
                    <div class="absolute top-[72.5%] left-[81.25%] -translate-x-1/2 -translate-y-1/2 bg-physio-600 shadow-[0_10px_30px_rgba(14,165,233,0.3)] border border-physio-500 px-4 py-2 rounded-xl text-[12px] md:text-sm font-bold text-white z-20 whitespace-nowrap">
                        <?= lang() === 'fa' ? 'هوش مصنوعی' : 'AI' ?>
                    </div>

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
<section id="articles" class="py-24 bg-slate-900 text-white relative z-20 border-t border-slate-800 overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-6 reveal">
        <div class="max-w-2xl">
            <h2 class="text-3xl md:text-5xl font-bold tracking-tight"><?= e(t('home.blog.title')) ?></h2>
            <p class="mt-4 text-lg text-slate-400"><?= e(t('home.blog.subtitle')) ?></p>
        </div>
        <div class="flex items-center gap-4 shrink-0">
            <div class="hidden md:flex p-1.5 bg-slate-800 border border-slate-700 rounded-full items-center gap-1 shadow-lg">
                <button id="blogPrevBtn" class="w-12 h-12 rounded-full flex items-center justify-center hover:bg-slate-700 text-slate-400 hover:text-white transition-all"><i data-lucide="chevron-left" class="w-5 h-5 rtl:rotate-180"></i></button>
                <div class="w-px h-5 bg-slate-700"></div>
                <button id="blogNextBtn" class="w-12 h-12 rounded-full flex items-center justify-center hover:bg-slate-700 text-slate-400 hover:text-white transition-all"><i data-lucide="chevron-right" class="w-5 h-5 rtl:rotate-180"></i></button>
            </div>
            <a href="<?= e(url(lang(), 'blog')) ?>" class="inline-flex items-center gap-2 text-physio-400 font-semibold text-sm hover:text-physio-300 transition-colors">
                <?= e(t('home.blog.viewAll')) ?> <i data-lucide="arrow-right" class="w-4 h-4 rtl:rotate-180"></i>
            </a>
        </div>
    </div>

    <!-- اسلایدر مطالب -->
    <div class="slider-container flex gap-6 px-6 md:px-8 lg:px-12 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-8 cursor-grab" id="blogSlider">
        <?php if (empty($latestPosts)): ?>
            <div class="w-full text-center text-slate-500 py-10"><?= e(t('blog.noPosts')) ?></div>
        <?php else: ?>
        <?php foreach ($latestPosts as $index => $post): ?>
            <?php
            $postTitle = L($post, 'title');
            $postImg   = (string) ($post['image'] ?? '');
            $postUrl   = url(lang(), 'blog/' . e(slugOf($post)));
            ?>
            <div class="w-[85vw] md:w-[400px] flex-shrink-0 snap-center group p-2">
                <a href="<?= e($postUrl) ?>" class="block h-full">
                    <div class="neon-glass-wrapper h-full transition-transform duration-500 group-hover:scale-[1.03]">
                        <div class="neon-glass-inner flex flex-col p-4 h-full">
                            <div class="relative overflow-hidden rounded-xl mb-4 aspect-[4/3] bg-slate-800">
                                <?php if ($postImg !== ''): ?>
                                    <img src="<?= e($postImg) ?>" alt="<?= e($postTitle) ?>" class="absolute inset-0 w-full h-full object-cover pe-img-zoom opacity-80" loading="lazy">
                                <?php else: ?>
                                    <div class="absolute inset-0 bg-slate-900 flex items-center justify-center">
                                        <div class="text-physio-500/50 font-mono text-4xl font-bold">&lt;/&gt;</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-col px-2 pb-2 grow">
                                <span class="text-sm text-slate-400 mb-2 font-mono"><?= e(format_date((string) ($post['published_at'] ?? $post['created_at']))) ?></span>
                                <h3 class="text-xl font-bold text-white mb-4 leading-snug group-hover:text-physio-400 transition-colors"><?= e($postTitle) ?></h3>
                                <div class="mt-auto flex items-center text-sm font-semibold text-physio-500 group-hover:text-physio-400 transition-all duration-300">
                                    <?= e(t('blog.readMore')) ?> <i data-lucide="arrow-right" class="w-4 h-4 ml-1 rtl:ml-0 rtl:mr-1 rtl:rotate-180"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
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