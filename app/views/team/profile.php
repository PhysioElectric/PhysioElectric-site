<?php
/**
 * Dedicated Member Profile Page
 * Variable $member is provided by the controller.
 */
$currentLang = lang();
$name = $currentLang === 'fa' ? $member['name_fa'] : $member['name_en'];
$role = $currentLang === 'fa' ? $member['role_fa'] : $member['role_en'];
$bio  = $currentLang === 'fa' ? $member['bio_fa']  : $member['bio_en'];
$expertise = $currentLang === 'fa' ? $member['expertise_fa'] : $member['expertise_en'];
?>

<div class="min-h-screen bg-slate-50/50 pt-28 pb-32">
    <div class="max-w-6xl mx-auto px-6 lg:px-8">

        <!-- Breadcrumbs -->
        <nav class="text-xs text-slate-400 mb-8 flex items-center gap-2" aria-label="Breadcrumb">
            <a href="<?= e(url($currentLang)) ?>" class="hover:text-physio-600 transition-colors"><?= e(t('nav.home')) ?></a>
            <i data-lucide="chevron-right" class="w-3 h-3 rtl:rotate-180"></i>
            <a href="<?= e(url($currentLang, 'about')) ?>" class="hover:text-physio-600 transition-colors"><?= e(t('nav.about')) ?></a>
            <i data-lucide="chevron-right" class="w-3 h-3 rtl:rotate-180"></i>
            <span class="text-slate-700 font-medium"><?= e($name) ?></span>
        </nav>

        <!-- Profile Hero: Clean Minimalist White Card -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-8 sm:p-12 shadow-sm mb-12 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-80 h-80 bg-gradient-to-br from-sky-50 to-indigo-50 rounded-full blur-3xl -z-0 pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-8">
                
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-sky-50 border border-sky-100 text-physio-600 text-xs font-mono font-bold mb-4">
                        <span>MEMBER // <?= e($member['num']) ?></span>
                    </div>
                    <h1 class="text-3xl sm:text-5xl font-black text-slate-900 tracking-tight mb-3">
                        <?= e($name) ?>
                    </h1>
                    <p class="text-base sm:text-lg font-semibold text-physio-600 mb-6">
                        <?= e($role) ?>
                    </p>
                    <p class="text-slate-600 text-base leading-relaxed text-justify">
                        <?= e($bio) ?>
                    </p>
                </div>

                <!-- Resume Action Buttons & Socials -->
                <div class="w-full md:w-auto flex flex-col sm:flex-row md:flex-col gap-3 min-w-[220px]">
                    <a href="<?= e($member['resume_url']) ?>" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-slate-900 text-white font-semibold text-sm hover:bg-physio-600 transition-all duration-300 shadow-md">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        <span><?= e(t('team.viewResume')) ?></span>
                    </a>
                    
                    <a href="<?= e($member['resume_url']) ?>" download class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-all duration-300">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span><?= e(t('team.downloadResume')) ?></span>
                    </a>

                    <!-- Social Icons -->
                    <div class="flex items-center justify-center gap-2 pt-3 border-t border-slate-100">
                        <?php if (!empty($member['socials']['github'])): ?>
                            <a href="<?= e($member['socials']['github']) ?>" target="_blank" class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200 flex items-center justify-center transition-colors" title="GitHub">
                                <i data-lucide="github" class="w-4 h-4"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($member['socials']['linkedin'])): ?>
                            <a href="<?= e($member['socials']['linkedin']) ?>" target="_blank" class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 hover:text-physio-600 hover:bg-sky-50 flex items-center justify-center transition-colors" title="LinkedIn">
                                <i data-lucide="linkedin" class="w-4 h-4"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($member['socials']['telegram'])): ?>
                            <a href="<?= e($member['socials']['telegram']) ?>" target="_blank" class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 hover:text-sky-500 hover:bg-sky-50 flex items-center justify-center transition-colors" title="Telegram">
                                <i data-lucide="send" class="w-4 h-4"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($member['socials']['email'])): ?>
                            <a href="<?= e($member['socials']['email']) ?>" class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 hover:text-red-500 hover:bg-rose-50 flex items-center justify-center transition-colors" title="Email">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <!-- Left Column: Skills & Core Expertise -->
            <div class="space-y-10">
                
                <!-- Interactive Skills -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-7 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                        <i data-lucide="cpu" class="w-5 h-5 text-physio-600"></i>
                        <span><?= e(t('team.skillsTitle')) ?></span>
                    </h2>
                    <div class="space-y-4">
                        <?php foreach ($member['skills'] as $s): ?>
                            <div>
                                <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1.5">
                                    <span><?= e($s['name']) ?></span>
                                    <span class="font-mono text-physio-600"><?= e($s['level']) ?>%</span>
                                </div>
                                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-physio-500 to-indigo-500 rounded-full transition-all duration-1000" style="width: <?= (int)$s['level'] ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Domains of Expertise -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-7 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-5 h-5 text-physio-600"></i>
                        <span><?= e(t('team.expertiseTitle')) ?></span>
                    </h2>
                    <ul class="space-y-3">
                        <?php foreach ($expertise as $exp): ?>
                            <li class="flex items-start gap-2.5 text-xs text-slate-600 leading-relaxed">
                                <span class="w-1.5 h-1.5 rounded-full bg-physio-500 mt-1.5 flex-shrink-0"></span>
                                <span><?= e($exp) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            </div>

            <!-- Right Column: Projects, Experience & Education -->
            <div class="lg:col-span-2 space-y-10">
                
                <!-- Projects -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-8 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                        <i data-lucide="folder-git-2" class="w-5 h-5 text-physio-600"></i>
                        <span><?= e(t('team.projectsTitle')) ?></span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <?php foreach ($member['projects'] as $p): 
                            $pTitle = $currentLang === 'fa' ? $p['title_fa'] : $p['title_en'];
                            $pDesc  = $currentLang === 'fa' ? $p['desc_fa']  : $p['desc_en'];
                        ?>
                            <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-physio-400 hover:shadow-md transition-all duration-300">
                                <h3 class="font-bold text-slate-900 text-sm mb-2"><?= e($pTitle) ?></h3>
                                <p class="text-xs text-slate-600 leading-relaxed mb-4 text-justify"><?= e($pDesc) ?></p>
                                <span class="inline-block px-2.5 py-1 rounded bg-slate-200/60 font-mono text-[0.68rem] text-slate-700">
                                    <?= e($p['tech']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Experience & Education Split -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                    
                    <!-- Experience -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-7 shadow-sm">
                        <h2 class="text-base font-bold text-slate-900 mb-5 flex items-center gap-2">
                            <i data-lucide="briefcase" class="w-4 h-4 text-physio-600"></i>
                            <span><?= e(t('team.experienceTitle')) ?></span>
                        </h2>
                        <div class="space-y-4">
                            <?php foreach ($member['experience'] as $exp): 
                                $eRole = $currentLang === 'fa' ? $exp['role_fa'] : $exp['role_en'];
                                $eDesc = $currentLang === 'fa' ? $exp['desc_fa'] : $exp['desc_en'];
                            ?>
                                <div class="border-s-2 border-slate-200 ps-4">
                                    <h3 class="text-xs font-bold text-slate-900"><?= e($eRole) ?></h3>
                                    <p class="text-[0.7rem] font-semibold text-physio-600"><?= e($exp['org']) ?> · <?= e($exp['period']) ?></p>
                                    <p class="text-xs text-slate-500 mt-1"><?= e($eDesc) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Education -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-7 shadow-sm">
                        <h2 class="text-base font-bold text-slate-900 mb-5 flex items-center gap-2">
                            <i data-lucide="graduation-cap" class="w-4 h-4 text-physio-600"></i>
                            <span><?= e(t('team.educationTitle')) ?></span>
                        </h2>
                        <div class="space-y-4">
                            <?php foreach ($member['education'] as $edu): 
                                $degree = $currentLang === 'fa' ? $edu['degree_fa'] : $edu['degree_en'];
                            ?>
                                <div class="border-s-2 border-slate-200 ps-4">
                                    <h3 class="text-xs font-bold text-slate-900"><?= e($degree) ?></h3>
                                    <p class="text-[0.7rem] font-semibold text-slate-500"><?= e($edu['school']) ?></p>
                                    <span class="text-[0.65rem] font-mono text-slate-400"><?= e($edu['period']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>
</div>