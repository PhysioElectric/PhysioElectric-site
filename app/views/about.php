<?php
/**
 * About page - Merged version with team, purpose, motivation, and domains.
 */
?>

<!-- ============ ABOUT HERO ============ -->
<section class="relative min-h-[90vh] flex flex-col justify-center pt-24 overflow-hidden">
    <canvas id="hero-canvas"></canvas>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10 w-full">
        <!-- Breadcrumb -->
        <nav class="text-xs text-slate-400 mb-8 flex items-center gap-2 reveal" aria-label="Breadcrumb">
            <a href="<?= e(url(lang())) ?>" class="hover:text-physio-600 transition-colors"><?= e(t('nav.home')) ?></a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 rtl:rotate-180"></i>
            <span class="text-slate-600"><?= e(t('nav.about')) ?></span>
        </nav>

        <div class="max-w-4xl reveal reveal-delay-1">
            <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold tracking-tight text-physio-950 leading-[1.1] mb-6">
                <?= t('about.hero.title') ?>
            </h1>
            <p class="text-xl md:text-2xl text-slate-500 font-light leading-relaxed max-w-2xl text-justify">
                <?= e(t('about.hero.subtitle')) ?>
            </p>
        </div>
    </div>
</section>

<!-- ============ TEAM SECTION ============ -->
<section class="py-24 bg-white relative z-20">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="mb-16 md:mb-24 reveal">
            <h2 class="text-3xl md:text-5xl font-bold tracking-tight text-physio-950"><?= e(t('team.title')) ?></h2>
            <p class="mt-4 text-lg text-slate-500 max-w-2xl text-justify"><?= e(t('team.subtitle')) ?></p>
        </div>

        <!-- Team Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php
            $team = [
                [
                    'name' => 'team.m1.name',
                    'role' => 'team.m1.role',
                    'desc' => 'team.m1.desc',
                    'img'  => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=800&auto=format&fit=crop',
                    'delay' => ''
                ],
                [
                    'name' => 'team.m2.name',
                    'role' => 'team.m2.role',
                    'desc' => 'team.m2.desc',
                    'img'  => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=800&auto=format&fit=crop',
                    'delay' => 'reveal-delay-1'
                ],
                [
                    'name' => 'team.m3.name',
                    'role' => 'team.m3.role',
                    'desc' => 'team.m3.desc',
                    'img'  => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=800&auto=format&fit=crop',
                    'delay' => 'reveal-delay-2'
                ],
                [
                    'name' => 'team.m4.name',
                    'role' => 'team.m4.role',
                    'desc' => 'team.m4.desc',
                    'img'  => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=800&auto=format&fit=crop',
                    'delay' => 'reveal-delay-3'
                ],
            ];
            ?>
            <?php foreach ($team as $member): ?>
                <div class="group cursor-pointer reveal <?= e($member['delay']) ?>">
                    <div class="relative overflow-hidden rounded-2xl aspect-[3/4] bg-slate-100 mb-6">
                        <img src="<?= e($member['img']) ?>" alt="<?= e(t($member['name'])) ?>" class="w-full h-full object-cover filter grayscale opacity-90 transition-all duration-700 group-hover:grayscale-0 group-hover:scale-105 group-hover:opacity-100" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    </div>
                    <div class="transform transition-transform duration-300 group-hover:-translate-y-1">
                        <h3 class="text-xl font-bold text-physio-950 group-hover:text-physio-600 transition-colors"><?= e(t($member['name'])) ?></h3>
                        <p class="text-sm font-semibold text-physio-500 uppercase tracking-wider mt-1 mb-3"><?= e(t($member['role'])) ?></p>
                        <p class="text-slate-600 text-sm leading-relaxed mb-4 line-clamp-2 text-justify"><?= e(t($member['desc'])) ?></p>
                        <div class="flex items-center text-sm font-semibold text-slate-400 group-hover:text-physio-600 transition-colors">
                            <span><?= e(t('team.viewProfile')) ?></span>
                            <i data-lucide="arrow-right" class="w-4 h-4 ml-1 rtl:ml-0 rtl:mr-1 rtl:rotate-180 transform opacity-0 -translate-x-2 rtl:translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300"></i>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ COMPANY PURPOSE ============ -->
<section class="py-24 bg-slate-50 border-y border-slate-200/50">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <span class="text-physio-500 font-bold text-sm uppercase tracking-widest mb-6 block reveal"><?= e(t('purpose.tag')) ?></span>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="reveal">
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight text-physio-950 leading-tight">
                    <?= e(t('purpose.title')) ?>
                </h2>
            </div>
            <div class="reveal reveal-delay-1 space-y-6 text-lg text-slate-600 leading-relaxed font-light">
                <p class="text-justify"><?= e(t('purpose.p1')) ?></p>
                <p class="text-justify"><?= e(t('purpose.p2')) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ============ MOTIVATION (What Drives Us) ============ -->
<section class="py-32 bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <h2 class="text-3xl md:text-5xl font-bold tracking-tight text-physio-950 mb-20 reveal"><?= e(t('motivation.title')) ?></h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-20 gap-x-16">
            <?php
            $motivations = [
                ['num' => '01', 'title' => 'motivation.p1.title', 'desc' => 'motivation.p1.desc', 'delay' => ''],
                ['num' => '02', 'title' => 'motivation.p2.title', 'desc' => 'motivation.p2.desc', 'delay' => 'reveal-delay-1'],
                ['num' => '03', 'title' => 'motivation.p3.title', 'desc' => 'motivation.p3.desc', 'delay' => ''],
                ['num' => '04', 'title' => 'motivation.p4.title', 'desc' => 'motivation.p4.desc', 'delay' => 'reveal-delay-1'],
            ];
            ?>
            <?php foreach ($motivations as $m): ?>
                <div class="reveal <?= e($m['delay']) ?>">
                    <div class="text-physio-100 text-7xl md:text-8xl font-bold mb-4 -ml-2 rtl:-ml-0 rtl:-mr-2 tracking-tighter"><?= e($m['num']) ?></div>
                    <h3 class="text-2xl font-bold text-physio-950 mb-4"><?= e(t($m['title'])) ?></h3>
                    <p class="text-slate-600 text-lg leading-relaxed text-justify"><?= e(t($m['desc'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ ENGINEERING MINDSET & TECH DOMAINS ============ -->
<section class="py-32 bg-slate-50 relative border-b border-slate-200/50 overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 text-center mb-20 reveal">
        <h2 class="text-4xl md:text-6xl font-bold tracking-tight text-physio-950 mb-6">
            <?= e(t('mindset.title')) ?>
        </h2>
        <p class="text-xl text-slate-500 max-w-3xl mx-auto font-light leading-relaxed text-justify">
            <?= e(t('mindset.subtitle')) ?>
        </p>
    </div>

    <!-- Mindset Flowchart -->
    <div class="max-w-5xl mx-auto px-6 lg:px-8 mb-32 reveal reveal-delay-1">
        <div class="relative w-full h-[300px] md:h-[150px] flex flex-col md:flex-row items-center justify-between">
            <svg class="hidden md:block absolute top-1/2 left-0 w-full h-10 -translate-y-1/2 z-0" preserveAspectRatio="none">
                <line x1="10%" y1="50%" x2="90%" y2="50%" stroke="#cbd5e1" stroke-width="2" class="flow-line" />
            </svg>
            <svg class="md:hidden absolute left-1/2 top-0 w-10 h-full -translate-x-1/2 z-0" preserveAspectRatio="none">
                <line x1="50%" y1="10%" x2="50%" y2="90%" stroke="#cbd5e1" stroke-width="2" class="flow-line" />
            </svg>

            <?php
            $nodes = [
                ['label' => 'node.problem', 'color' => 'bg-white border-slate-200 text-slate-700'],
                ['label' => 'node.analysis', 'color' => 'bg-white border-slate-200 text-slate-700'],
                ['label' => 'node.model', 'color' => 'bg-white border-slate-200 text-slate-700'],
                ['label' => 'node.implementation', 'color' => 'bg-white border-slate-200 text-slate-700'],
                ['label' => 'node.validation', 'color' => 'bg-white border-slate-200 text-slate-700'],
                ['label' => 'node.solution', 'color' => 'bg-physio-900 border-physio-800 text-white shadow-glow'],
            ];
            ?>
            <?php foreach ($nodes as $i => $node): ?>
                <div class="<?= e($node['color']) ?> border shadow-sm rounded-xl px-6 py-3 z-10 text-sm font-bold">
                    <?= e(t($node['label'])) ?>
                </div>
                <?php if ($i < count($nodes) - 1): ?>
                    <i data-lucide="arrow-down" class="md:hidden w-4 h-4 text-slate-400 z-10 bg-slate-50"></i>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tech Domains -->
    <div class="max-w-7xl mx-auto px-6 lg:px-8 reveal reveal-delay-2">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php
            $domains = [
                ['icon' => 'braces', 'label' => 'domain.sw'],
                ['icon' => 'brain-circuit', 'label' => 'domain.ai'],
                ['icon' => 'function-square', 'label' => 'domain.math'],
                ['icon' => 'cpu', 'label' => 'domain.matlab'],
                ['icon' => 'layout', 'label' => 'domain.web'],
                ['icon' => 'workflow', 'label' => 'domain.automation'],
                ['icon' => 'satellite-dish', 'label' => 'domain.iot'],
                ['icon' => 'layers', 'label' => 'domain.digital'],
            ];
            ?>
            <?php foreach ($domains as $d): ?>
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow flex flex-col items-center justify-center text-center gap-3">
                    <i data-lucide="<?= e($d['icon']) ?>" class="w-6 h-6 text-physio-500"></i>
                    <span class="font-semibold text-slate-800 text-sm"><?= e(t($d['label'])) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ COMPANY PHILOSOPHY ============ -->
<section class="py-40 bg-white">
    <div class="max-w-5xl mx-auto px-6 lg:px-8 text-center reveal">
        <h2 class="text-4xl md:text-5xl lg:text-7xl font-bold tracking-tight text-physio-950 leading-[1.1] mb-10">
            <?= t('phil.title') ?>
        </h2>
        <p class="text-xl md:text-2xl text-slate-500 font-light leading-relaxed max-w-3xl mx-auto text-justify">
            <?= e(t('phil.desc')) ?>
        </p>
    </div>
</section>

<!-- ============ FINAL CTA ============ -->
<section class="py-32 relative overflow-hidden bg-physio-950 text-white border-t border-slate-800">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute w-[500px] h-[500px] bg-physio-500 rounded-full blur-[120px] top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 mix-blend-screen"></div>
        <div class="absolute inset-0" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 40px 40px;"></div>
    </div>
    
    <div class="max-w-4xl mx-auto px-6 text-center relative z-10 reveal">
        <h2 class="text-4xl md:text-6xl font-bold tracking-tight mb-6"><?= t('cta.title') ?></h2>
        <p class="text-xl text-slate-400 mb-10 max-w-2xl mx-auto text-justify"><?= e(t('cta.desc')) ?></p>
        
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="<?= e(cta_telegram_url()) ?>" data-tg-link="<?= e(cta_tg_scheme()) ?>" class="btn-shine relative overflow-hidden px-8 py-4 bg-white text-physio-950 font-bold rounded-full hover:bg-slate-100 transition-colors shadow-glow">
                <i data-lucide="send" class="w-5 h-5 inline-block ml-2 rtl:ml-0 rtl:mr-2"></i>
                <?= e(t('cta.btnPrimary')) ?>
            </a>
            <a href="<?= e(url(lang(), 'projects')) ?>" class="inline-flex items-center justify-center px-8 py-4 border border-slate-700 bg-slate-900/50 backdrop-blur text-white font-medium rounded-full hover:bg-slate-800 transition-colors">
                <?= e(t('cta.btnSecondary')) ?>
            </a>
        </div>
    </div>
</section>