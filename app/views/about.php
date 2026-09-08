<?php
/**
 * About page - Standalone & Bulletproof version
 */
$currentLang = lang();

// داده‌های ۴ عضو به صورت مستقیم داخل ویو (بدون وابستگی به فایل خارجی)
$membersData = [
    [
        'num' => '01',
        'slug' => 'mehrab-mahmoudi',
        'avatar_type' => 'ai_security',
        'name_fa' => 'محمد محراب محمودی',
        'name_en' => 'Mohammad Mehrab Mahmoudi',
        'role_fa' => 'مدیر ارشد تیم، معمار امنیت و سیستم‌های هوش مصنوعی',
        'role_en' => 'Team Lead & Systems Architect / Security & AI',
        'desc_fa' => 'راهبری معماری سیستم‌های توزیع‌شده، انتخاب زیرساخت‌های اجرایی، ارزیابی امنیت شبکه و نظارت بر توسعه سیستم‌های هوش مصنوعی و اتوماسیون سازمانی.',
        'desc_en' => 'Leading distributed systems architecture, tech-stack orchestration, deployment infrastructure security, and the development of intelligent AI automation workflows.',
        'tags' => ['AI Systems', 'DevSecOps', 'Cloud Architecture'],
    ],
    [
        'num' => '02',
        'slug' => 'mohammadreza-afraz',
        'avatar_type' => 'vision_multiphysics',
        'name_fa' => 'محمدرضا افراز',
        'name_en' => 'Mohammad Reza Afraz',
        'role_fa' => 'مدیر فرانت‌اند و شبیه‌سازی / بینایی ماشین و پردازش تصویر',
        'role_en' => 'Lead Front-End & Simulation / Computer Vision',
        'desc_fa' => 'توسعه فرانت‌اند تعاملی و اتصال APIها، پردازش تصویر بلادرنگ با OpenCV، الگوریتم‌های هوش مصنوعی و شبیه‌سازی‌های پیشرفته در متلب و کامسول.',
        'desc_en' => 'Front-end system orchestration, real-time computer vision with OpenCV, machine learning models, and high-precision multiphysics modeling in MATLAB & COMSOL.',
        'tags' => ['Computer Vision', 'COMSOL', 'MATLAB'],
    ],
    [
        'num' => '03',
        'slug' => 'parsa-ahadi',
        'avatar_type' => 'backend_iot',
        'name_fa' => 'پارسا احدی',
        'name_en' => 'Parsa Ahadi',
        'role_fa' => 'مهندس ارشد بک‌اند، اینترنت اشیا (IoT) و امنیت API',
        'role_en' => 'Back-End & IoT Systems Engineer',
        'desc_fa' => 'طراحی سیستم‌های سرور مقیاس‌پذیر، معماری میکروسرویس، پیاده‌سازی پروتکل‌های مخابراتی سخت‌افزار (IoT) و استانداردهای سخت‌گیرانه امنیت داده.',
        'desc_en' => 'Engineering scalable server architectures, microservice backends, embedded hardware network protocols, and robust API cryptographic defenses.',
        'tags' => ['Backend APIs', 'Embedded IoT', 'Security'],
    ],
    [
        'num' => '04',
        'slug' => 'amirreza-hashemi',
        'avatar_type' => 'data_architecture',
        'name_fa' => 'سید امیررضا هاشمی',
        'name_en' => 'Seyed Amirreza Hashemi',
        'role_fa' => 'معمار پایگاه داده و تحلیل‌گر داده‌های ساختاریافته',
        'role_en' => 'Database Architect & Data Strategist',
        'desc_fa' => 'مدل‌سازی اسکیماهای رابطه‌ای و غیررابطه‌ای، بهینه‌سازی کوئری‌های پیچیده، پایداری تراکنش‌ها و معماری خطوط پردازش داده در مقیاس بالا.',
        'desc_en' => 'Architecting relational and distributed database schemata, query execution optimization, ACID transaction guarantees, and high-volume data pipeline engineering.',
        'tags' => ['Database Architecture', 'Query Tuning', 'PostgreSQL'],
    ]
];
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

<!-- ============ TEAM SECTION (CLEAN TECH MINIMAL) ============ -->
<section class="py-24 bg-white relative z-20 border-t border-slate-100">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="max-w-3xl mb-16 reveal text-start">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 mb-4">
                <span class="w-2 h-2 rounded-full bg-physio-500"></span>
                <span class="text-xs font-semibold text-slate-700 font-mono">Core Team</span>
            </div>
            <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-slate-900">
                <?= e(t('team.title')) ?>
            </h2>
            <p class="mt-3 text-base md:text-lg text-slate-600 leading-relaxed text-justify">
                <?= e(t('team.subtitle')) ?>
            </p>
        </div>

        <!-- Team Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
            <?php foreach ($membersData as $member): 
                $name = $currentLang === 'fa' ? $member['name_fa'] : $member['name_en'];
                $role = $currentLang === 'fa' ? $member['role_fa'] : $member['role_en'];
                $desc = $currentLang === 'fa' ? $member['desc_fa'] : $member['desc_en'];
                $profileUrl = url($currentLang, 'team/' . $member['slug']);
            ?>
                <!-- Member Card -->
                <div class="pe-card group bg-white rounded-2xl p-6 border border-slate-200 hover:border-physio-500 shadow-sm flex flex-col justify-between transition-all duration-300">
                    
                    <div>
                        <!-- Top Header: Avatar + Number Badge -->
                        <div class="flex items-center justify-between mb-5">
                            <!-- Geometric Abstract Avatar -->
                            <div class="w-14 h-14 rounded-xl bg-sky-50 border border-sky-100 flex items-center justify-center text-physio-600 group-hover:bg-physio-500 group-hover:text-white transition-colors duration-300">
                                <?php if ($member['avatar_type'] === 'ai_security'): ?>
                                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                        <path d="M12 8v4"/>
                                        <path d="M12 16h.01"/>
                                    </svg>
                                <?php elseif ($member['avatar_type'] === 'vision_multiphysics'): ?>
                                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="3"/>
                                        <path d="M3 7V5a2 2 0 0 1 2-2h2"/>
                                        <path d="M17 3h2a2 2 0 0 1 2 2v2"/>
                                        <path d="M21 17v2a2 2 0 0 1-2 2h-2"/>
                                        <path d="M7 21H5a2 2 0 0 1-2-2v-2"/>
                                    </svg>
                                <?php elseif ($member['avatar_type'] === 'backend_iot'): ?>
                                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="4" y="4" width="16" height="16" rx="2"/>
                                        <rect x="9" y="9" width="6" height="6"/>
                                        <path d="M9 1v3M15 1v3M9 20v3M15 20v3M20 9h3M20 14h3M1 9h3M1 14h3"/>
                                    </svg>
                                <?php else: ?>
                                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <ellipse cx="12" cy="5" rx="9" ry="3"/>
                                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
                                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                                    </svg>
                                <?php endif; ?>
                            </div>

                            <!-- Crisp Number Badge -->
                            <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-md bg-slate-100 text-slate-500 border border-slate-200/80 group-hover:bg-sky-50 group-hover:text-physio-600 group-hover:border-sky-200 transition-colors">
                                #<?= e($member['num']) ?>
                            </span>
                        </div>

                        <!-- Info -->
                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-physio-600 transition-colors mb-1.5 text-start">
                            <?= e($name) ?>
                        </h3>
                        
                        <p class="text-xs font-semibold text-physio-600 mb-3 text-start leading-snug">
                            <?= e($role) ?>
                        </p>
                        
                        <p class="text-sm text-slate-600 leading-relaxed text-justify mb-5">
                            <?= e($desc) ?>
                        </p>

                        <!-- Tech Tags -->
                        <div class="flex flex-wrap gap-1.5 mb-6">
                            <?php foreach ($member['tags'] as $tag): ?>
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-slate-50 text-slate-700 border border-slate-200/70">
                                    <?= e($tag) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Bottom Link CTA -->
                    <div class="pt-4 border-t border-slate-100">
                        <a href="<?= e($profileUrl) ?>" class="inline-flex items-center justify-between w-full text-xs font-bold text-slate-800 group-hover:text-physio-600 transition-colors">
                            <span><?= e($currentLang === 'fa' ? 'مشاهده کامل پروفایل' : 'View Full Profile') ?></span>
                            <i data-lucide="arrow-right" class="w-4 h-4 rtl:rotate-180 transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-transform"></i>
                        </a>
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
    <div class="max-w-6xl mx-auto px-6 lg:px-8 mb-32 reveal reveal-delay-1">
        <div class="relative w-full py-8 flex items-center justify-center">
            
            <!-- Foolproof Animated Pipeline Track (Black Dashed + Red/Black Laser) -->
            <div class="hidden md:block absolute left-4 right-4 z-0 pointer-events-none" style="top: 50%; transform: translateY(-50%);">
                <!-- خط‌چین مشکی تیره با استایل مستقیم -->
                <div style="width: 100%; border-top: 2px dashed #0f172a; opacity: 0.65;"></div>
                <!-- پرتو نوری متحرک قرمز-مشکی -->
                <div class="pipeline-laser-beam"></div>
            </div>

            <?php
            $nodes = [
                ['num' => '01', 'label' => 'node.problem'],
                ['num' => '02', 'label' => 'node.analysis'],
                ['num' => '03', 'label' => 'node.model'],
                ['num' => '04', 'label' => 'node.implementation'],
                ['num' => '05', 'label' => 'node.validation'],
                ['num' => '06', 'label' => 'node.solution'],
            ];
            ?>

            <!-- Nodes Container -->
            <div class="relative z-10 w-full flex flex-col md:flex-row items-center justify-between gap-4 md:gap-2">
                <?php foreach ($nodes as $i => $node): ?>
                    
                    <!-- Uniform Glowing Nodes -->
                    <div class="group bg-white border border-slate-100 rounded-2xl px-6 py-3 shadow-[0_0_15px_rgba(14,165,233,0.12)] hover:shadow-[0_0_25px_rgba(14,165,233,0.25)] hover:border-physio-300 flex items-center gap-2.5 transition-all duration-300 hover:-translate-y-1">
                        <span class="font-mono text-xs font-bold text-slate-400 group-hover:text-physio-600 transition-colors"><?= $node['num'] ?>.</span>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-slate-900 transition-colors"><?= e(t($node['label'])) ?></span>
                    </div>

                    <!-- فلش موبایل -->
                    <?php if ($i < count($nodes) - 1): ?>
                        <div class="md:hidden flex items-center justify-center my-1 text-slate-300">
                            <i data-lucide="arrow-down" class="w-4 h-4"></i>
                        </div>
                    <?php endif; ?>

                <?php endforeach; ?>
            </div>
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