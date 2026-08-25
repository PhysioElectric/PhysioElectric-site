<?php
/**
 * Contact page - Multi-step project request form.
 * Expects: $telegram, $tgScheme, $email, $emailAddr, $phone, $address
 */
$lang = lang();
?>

<!-- Page Hero -->
<header class="pt-32 pb-16 relative overflow-hidden">
    <div class="tech-bg bg-grid-pattern"></div>
    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
        <nav class="text-xs text-slate-400 mb-8 flex items-center gap-2" aria-label="Breadcrumb">
            <a href="<?= e(url($lang)) ?>" class="hover:text-physio-600 transition-colors"><?= e(t('nav.home')) ?></a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 rtl:rotate-180"></i>
            <span class="text-slate-600"><?= e(t('nav.contact')) ?></span>
        </nav>

        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-white border border-slate-200 text-slate-700 text-xs font-mono font-medium uppercase tracking-wider mb-6 shadow-sm">
                <i data-lucide="cpu" class="w-3.5 h-3.5 text-physio-500"></i>
                <span><?= e(t('contact.badge')) ?></span>
            </div>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight text-physio-950 leading-[1.1] mb-6">
                <?= t('contact.heroTitle') ?>
            </h1>
            <p class="text-lg text-slate-500 leading-relaxed max-w-2xl text-justify">
                <?= e(t('contact.heroSub')) ?>
            </p>
        </div>
    </div>
</header>

<!-- Main Form -->
<main class="max-w-7xl mx-auto px-6 lg:px-8 pb-32">
    
    <!-- Mobile Progress -->
    <div class="lg:hidden mb-8 bg-white p-4 rounded-xl border border-slate-200 shadow-sm sticky top-20 z-40">
        <div class="flex justify-between items-end mb-2">
            <span class="text-sm font-bold text-slate-800" id="mobile-step-text"><?= e(t('contact.step')) ?> 1 / 6</span>
            <span class="text-xs font-mono text-slate-500" id="mobile-percent-text">16%</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-1.5">
            <div class="bg-gradient-to-r from-physio-400 to-physio-600 h-1.5 rounded-full transition-all duration-500" id="mobile-progress-bar" style="width: 16%"></div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-12 items-start relative">
        
        <!-- Blueprint Sidebar -->
        <aside class="hidden lg:block w-1/3 sticky top-32">
            <div class="bg-white/60 backdrop-blur-xl rounded-3xl border border-slate-200 p-8 shadow-sm">
                <h3 class="font-mono text-sm font-bold text-slate-400 uppercase tracking-widest mb-8 flex items-center gap-2">
                    <i data-lucide="map" class="w-4 h-4"></i>
                    <span><?= e(t('contact.blueprint')) ?></span>
                </h3>
                
                <div class="relative">
                    <div class="blueprint-line"></div>
                    <ul class="space-y-8 relative z-10" id="blueprint-list" data-progress="0"></ul>
                </div>

                <div class="mt-12 pt-6 border-t border-slate-100 flex items-center gap-3 bg-slate-50/50 p-4 rounded-xl">
                    <div class="bg-green-100 text-green-600 p-1.5 rounded-full">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                    <span class="text-xs text-slate-600 font-medium leading-relaxed"><?= t('contact.secure') ?></span>
                </div>
            </div>
        </aside>

        <!-- Form Container -->
        <section class="w-full lg:w-2/3 bg-white rounded-3xl ring-1 ring-slate-200 p-6 sm:p-10 min-h-[500px] relative overflow-hidden" id="form-container">
            
            <!-- ====== STEP 1: Category Selection ====== -->
            <div id="step-1" class="form-step active">
                <h2 class="text-2xl sm:text-3xl font-bold text-physio-950 mb-3"><?= e(t('contact.s1.title')) ?></h2>
                <p class="text-slate-500 mb-8 text-lg"><?= e(t('contact.s1.sub')) ?></p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8" id="category-grid">
                    <?php
                    $cats = [
                        ['val' => 'web', 'icon' => 'layout', 't' => t('contact.cat.web'), 'd' => t('contact.cat.webDesc')],
                        ['val' => 'matlab', 'icon' => 'function-square', 't' => t('contact.cat.matlab'), 'd' => t('contact.cat.matlabDesc')],
                        ['val' => 'comsol', 'icon' => 'box', 't' => t('contact.cat.comsol'), 'd' => t('contact.cat.comsolDesc')],
                        ['val' => 'ai', 'icon' => 'brain-circuit', 't' => t('contact.cat.ai'), 'd' => t('contact.cat.aiDesc')],
                        ['val' => 'wordpress', 'icon' => 'monitor', 't' => t('contact.cat.wp'), 'd' => t('contact.cat.wpDesc')],
                        ['val' => 'custom', 'icon' => 'code-2', 't' => t('contact.cat.custom'), 'd' => t('contact.cat.customDesc')],
                    ];
                    foreach ($cats as $c):
                    ?>
                    <div class="selection-card rounded-2xl p-6" data-value="<?= e($c['val']) ?>">
                        <div class="flex justify-between items-start mb-4">
                            <div class="icon-wrap w-12 h-12 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center transition-all duration-300 border border-slate-100">
                                <i data-lucide="<?= e($c['icon']) ?>" class="w-6 h-6"></i>
                            </div>
                            <div class="check-circle w-6 h-6 rounded-full border-2 border-slate-200 flex items-center justify-center bg-white transition-all">
                                <i data-lucide="check" class="w-3.5 h-3.5 text-white" style="display: none;"></i>
                            </div>
                        </div>
                        <h4 class="font-bold text-slate-800 mb-2 text-lg"><?= e($c['t']) ?></h4>
                        <p class="text-sm text-slate-500 leading-relaxed"><?= e($c['d']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div id="step-1-error" class="bg-red-50 p-3 rounded-lg border border-red-100 text-red-600 text-sm font-medium flex items-center gap-2" style="display: none;">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    <span><?= e(t('contact.err.selectOne')) ?></span>
                </div>
            </div>

            <!-- ====== STEP 2: Client Info ====== -->
            <div id="step-2" class="form-step" style="display: none;">
                <h2 class="text-2xl sm:text-3xl font-bold text-physio-950 mb-3"><?= e(t('contact.s2.title')) ?></h2>
                <p class="text-slate-500 mb-8 text-lg"><?= e(t('contact.s2.sub')) ?></p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2"><?= t('contact.lbl.name') ?></label>
                        <input type="text" id="inp_name" class="custom-input w-full px-5 py-3.5 rounded-xl text-slate-800" placeholder="<?= $lang === 'fa' ? 'نام و نام خانوادگی شما' : 'Your full name' ?>">
                        <div class="error-message" id="err_name" style="display: none;"><?= e(t('contact.err.req')) ?></div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2"><?= e(t('contact.lbl.company')) ?></label>
                        <input type="text" id="inp_company" class="custom-input w-full px-5 py-3.5 rounded-xl text-slate-800" placeholder="<?= $lang === 'fa' ? 'نام شرکت یا سازمان' : 'Company or organization' ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2"><?= t('contact.lbl.email') ?></label>
                        <input type="email" id="inp_email" class="custom-input w-full px-5 py-3.5 rounded-xl text-slate-800 text-left" dir="ltr" placeholder="name@company.com">
                        <div class="error-message" id="err_email" style="display: none;"><?= e(t('contact.err.email')) ?></div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2"><?= t('contact.lbl.phone') ?></label>
                        <input type="tel" id="inp_phone" class="custom-input w-full px-5 py-3.5 rounded-xl text-slate-800 text-left" dir="ltr" placeholder="+98 ...">
                        <div class="error-message" id="err_phone" style="display: none;"><?= e(t('contact.err.req')) ?></div>
                    </div>
                    <div class="md:col-span-2 pt-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-3"><?= e(t('contact.lbl.method')) ?></label>
                        <div class="flex flex-wrap gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="contactMethod" value="Email" class="peer sr-only" checked>
                                <div class="px-6 py-3 rounded-full border-2 border-slate-200 text-sm font-semibold text-slate-600 peer-checked:bg-physio-600 peer-checked:border-physio-600 peer-checked:text-white transition-all flex items-center gap-2">
                                    <i data-lucide="mail" class="w-4 h-4"></i> <?= e(t('contact.opt.email')) ?>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="contactMethod" value="Phone" class="peer sr-only">
                                <div class="px-6 py-3 rounded-full border-2 border-slate-200 text-sm font-semibold text-slate-600 peer-checked:bg-physio-600 peer-checked:border-physio-600 peer-checked:text-white transition-all flex items-center gap-2">
                                    <i data-lucide="phone" class="w-4 h-4"></i> <?= e(t('contact.opt.phone')) ?>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="contactMethod" value="Telegram" class="peer sr-only">
                                <div class="px-6 py-3 rounded-full border-2 border-slate-200 text-sm font-semibold text-slate-600 peer-checked:bg-[#229ED9] peer-checked:border-[#229ED9] peer-checked:text-white transition-all flex items-center gap-2">
                                    <i data-lucide="send" class="w-4 h-4"></i> Telegram
                                </div>
                            </label>
                        </div>
                        
                        <div id="dynamic-contact-wrapper" style="display: none;" class="mt-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-2"><?= t('contact.lbl.tgId') ?></label>
                            <input type="text" id="inp_contact_id" class="custom-input w-full md:w-1/2 px-5 py-3.5 rounded-xl text-slate-800 text-left" dir="ltr" placeholder="@username">
                            <div class="error-message" id="err_contact_id" style="display: none;"><?= e(t('contact.err.req')) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ====== STEP 3: Description ====== -->
            <div id="step-3" class="form-step" style="display: none;">
                <h2 class="text-2xl sm:text-3xl font-bold text-physio-950 mb-3"><?= e(t('contact.s3.title')) ?></h2>
                <p class="text-slate-500 mb-8 text-lg"><?= e(t('contact.s3.sub')) ?></p>
                
                <label class="block text-sm font-semibold text-slate-700 mb-3"><?= t('contact.lbl.desc') ?></label>
                <textarea id="inp_desc" rows="12" class="custom-input w-full px-5 py-4 rounded-xl text-slate-800 resize-none leading-relaxed" maxlength="3000" placeholder="<?= $lang === 'fa' ? 'ایده، مشکل، نیازمندی‌ها را توضیح دهید...' : 'Describe your idea, problem, requirements...' ?>"></textarea>
                
                <div class="flex justify-between items-center mt-3">
                    <div class="error-message" id="err_desc" style="display: none;"><?= e(t('contact.err.desc')) ?></div>
                    <span class="text-xs font-mono text-slate-400 bg-slate-100 px-2 py-1 rounded-md" id="char-counter">0 / 3000</span>
                </div>
            </div>

            <!-- ====== STEP 4: Scope ====== -->
            <div id="step-4" class="form-step" style="display: none;">
                <h2 class="text-2xl sm:text-3xl font-bold text-physio-950 mb-3"><?= e(t('contact.s4.title')) ?></h2>
                <p class="text-slate-500 mb-10 text-lg"><?= e(t('contact.s4.sub')) ?></p>
                
                <label class="block text-base font-bold text-slate-800 mb-4"><?= e(t('contact.lbl.timeline')) ?></label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <?php
                    $times = [t('contact.time.1'), t('contact.time.2'), t('contact.time.3'), t('contact.time.4'), t('contact.time.5'), t('contact.time.6')];
                    $vals = ['ASAP', '1-2 Weeks', '1 Month', '1-3 Months', '3+ Months', 'Not sure'];
                    foreach ($times as $i => $tl):
                    ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="timeline" value="<?= e($vals[$i]) ?>" class="peer sr-only" <?= $i === 0 ? 'checked' : '' ?>>
                        <div class="px-4 py-4 text-center rounded-xl border-2 border-slate-200 text-sm font-semibold text-slate-600 peer-checked:bg-slate-700 peer-checked:border-slate-700 peer-checked:text-white transition-all hover:border-slate-400">
                            <?= e($tl) ?>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ====== STEP 5: Additional ====== -->
            <div id="step-5" class="form-step" style="display: none;">
                <h2 class="text-2xl sm:text-3xl font-bold text-physio-950 mb-3"><?= e(t('contact.s5.title')) ?></h2>
                <p class="text-slate-500 mb-8 text-lg"><?= e(t('contact.s5.sub')) ?></p>
                
                <label class="block text-sm font-semibold text-slate-700 mb-2"><?= e(t('contact.lbl.notes')) ?></label>
                <input type="text" id="inp_notes" class="custom-input w-full px-5 py-4 rounded-xl mb-8" placeholder="<?= $lang === 'fa' ? 'لینک‌ها، رقبا، یا نیازمندی‌های خاص...' : 'Links, references, or specific requirements...' ?>">
                
                <label class="block text-sm font-semibold text-slate-700 mb-2"><?= e(t('contact.lbl.upload')) ?></label>
                <div class="dropzone rounded-2xl flex flex-col items-center justify-center p-10 text-center cursor-pointer relative" id="dropzone">
                    <input type="file" id="file_input" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" multiple accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.zip">
                    <i data-lucide="upload-cloud" class="w-12 h-12 text-physio-500 mb-4"></i>
                    <span class="text-base font-bold text-slate-700 mb-2"><?= e(t('contact.upload.main')) ?></span>
                    <span class="text-sm text-slate-400"><?= e(t('contact.upload.sub')) ?></span>
                </div>
                <div id="file-list" class="mt-4 space-y-3"></div>
            </div>

            <!-- ====== STEP 6: Review ====== -->
            <div id="step-6" class="form-step" style="display: none;">
                <h2 class="text-2xl sm:text-3xl font-bold text-physio-950 mb-3"><?= e(t('contact.s6.title')) ?></h2>
                <p class="text-slate-500 mb-8 text-lg"><?= e(t('contact.s6.sub')) ?></p>
                
                <div class="bg-slate-50/80 rounded-2xl border border-slate-200 p-8 space-y-6 mb-8">
                    <div class="flex justify-between border-b border-slate-200 pb-5">
                        <div>
                            <span class="block text-xs font-bold text-slate-400 uppercase mb-2"><?= e(t('contact.rev.type')) ?></span>
                            <div class="font-bold text-physio-700" id="rev_type"></div>
                        </div>
                        <button class="edit-btn text-physio-600 text-sm font-bold" data-step="0"><?= e(t('contact.btn.edit')) ?></button>
                    </div>
                    <div class="flex justify-between border-b border-slate-200 pb-5">
                        <div>
                            <span class="block text-xs font-bold text-slate-400 uppercase mb-2"><?= e(t('contact.rev.client')) ?></span>
                            <div class="text-sm" id="rev_client"></div>
                        </div>
                        <button class="edit-btn text-physio-600 text-sm font-bold" data-step="1"><?= e(t('contact.btn.edit')) ?></button>
                    </div>
                    <div class="flex justify-between border-b border-slate-200 pb-5">
                        <div>
                            <span class="block text-xs font-bold text-slate-400 uppercase mb-2"><?= e(t('contact.rev.desc')) ?></span>
                            <div class="text-sm line-clamp-3" id="rev_desc"></div>
                        </div>
                        <button class="edit-btn text-physio-600 text-sm font-bold" data-step="2"><?= e(t('contact.btn.edit')) ?></button>
                    </div>
                    <div class="flex justify-between border-b border-slate-200 pb-5">
                        <div>
                            <span class="block text-xs font-bold text-slate-400 uppercase mb-2"><?= e(t('contact.rev.scope')) ?></span>
                            <div class="font-semibold" id="rev_time"></div>
                        </div>
                        <button class="edit-btn text-physio-600 text-sm font-bold" data-step="3"><?= e(t('contact.btn.edit')) ?></button>
                    </div>
                    <div class="flex justify-between">
                        <div>
                            <span class="block text-xs font-bold text-slate-400 uppercase mb-2"><?= e(t('contact.lbl.notes')) ?></span>
                            <div class="text-sm" id="rev_notes"></div>
                        </div>
                        <button class="edit-btn text-physio-600 text-sm font-bold" data-step="4"><?= e(t('contact.btn.edit')) ?></button>
                    </div>
                </div>

                <label class="flex items-start gap-4 cursor-pointer p-4 rounded-xl border border-slate-100 bg-white hover:border-physio-200">
                    <input type="checkbox" id="consent_check" class="sr-only peer">
                    <div class="w-6 h-6 rounded-md border-2 border-slate-300 bg-white peer-checked:bg-physio-600 peer-checked:border-physio-600 flex items-center justify-center mt-1">
                        <i data-lucide="check" class="w-4 h-4 text-white opacity-0 peer-checked:opacity-100"></i>
                    </div>
                    <span class="text-sm text-slate-600"><?= e(t('contact.rev.consent')) ?></span>
                </label>
                <div class="error-message" id="err_consent" style="display: none;"><?= e(t('contact.err.consent')) ?></div>
            </div>

            <!-- ====== SUCCESS ====== -->
            <div id="step-success" class="form-step text-center py-16" style="display: none;">
                <div class="w-28 h-28 bg-green-50 rounded-full mx-auto flex items-center justify-center mb-8">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                        <i data-lucide="check" class="w-10 h-10 text-green-600"></i>
                    </div>
                </div>
                <h2 class="text-3xl font-bold text-physio-950 mb-4"><?= e(t('contact.succ.title')) ?></h2>
                <p class="text-slate-500 mb-8"><?= e(t('contact.succ.desc')) ?></p>
                <div class="inline-flex items-center gap-3 bg-slate-50 border rounded-xl px-5 py-3 mb-8">
                    <span class="text-xs text-slate-400 uppercase"><?= e(t('contact.succ.id')) ?></span>
                    <span class="font-mono font-bold text-physio-700" id="req-id">#PE-<?= strtoupper(substr(uniqid(), -5)) ?></span>
                </div>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="<?= e(url($lang)) ?>" class="px-8 py-3.5 bg-white border-2 border-slate-200 font-bold rounded-full"><?= e(t('contact.succ.btn1')) ?></a>
                    <a href="<?= e(url($lang, 'projects')) ?>" class="px-8 py-3.5 bg-physio-900 text-white font-bold rounded-full"><?= e(t('contact.succ.btn2')) ?></a>
                </div>
            </div>

            <!-- ====== NAVIGATION ====== -->
            <div class="mt-12 pt-8 border-t border-slate-100 flex justify-between items-center" id="form-navigation">
                <button id="btn-prev" class="px-6 py-3.5 text-sm font-bold text-slate-500 hover:text-slate-800 rounded-full flex items-center gap-2" style="display: none;">
                    <i data-lucide="arrow-left" class="w-4 h-4 rtl:rotate-180"></i>
                    <span><?= e(t('contact.btn.prev')) ?></span>
                </button>
                <div class="flex-1"></div>
                <button id="btn-next" class="px-8 py-3.5 bg-physio-600 text-white text-sm font-bold rounded-full hover:bg-physio-700 transition-all flex items-center gap-2">
                    <span id="btn-next-text"><?= e(t('contact.btn.next')) ?></span>
                    <i data-lucide="arrow-right" class="w-4 h-4 rtl:rotate-180"></i>
                </button>
            </div>

        </section>
    </div>
</main>

<!-- Direct Contact Links -->
<section class="py-20 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 text-center">
        <h3 class="text-3xl font-bold text-physio-950 mb-3"><?= e(t('contact.direct.title')) ?></h3>
        <p class="text-slate-500 text-lg mb-8"><?= e(t('contact.direct.sub')) ?></p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="<?= e($telegram) ?>" data-tg-link="<?= e($tgScheme) ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-physio-50 border border-physio-200 rounded-full font-semibold text-physio-700 hover:bg-physio-100 transition-all">
                <i data-lucide="send" class="w-4 h-4"></i> @<?= e(telegram_user()) ?>
            </a>
            <a href="<?= e($email) ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-50 border border-slate-200 rounded-full font-semibold text-slate-700 hover:bg-slate-100 transition-all">
                <i data-lucide="mail" class="w-4 h-4"></i> <?= e($emailAddr) ?>
            </a>
        </div>
    </div>
</section>

<!-- Pass data to JS -->
<script>
var PE_CONTACT_LANG = '<?= e($lang) ?>';
var PE_CONTACT_DICT = {
    bpLabels: [
        '<?= e(t('contact.bp.1')) ?>',
        '<?= e(t('contact.bp.2')) ?>',
        '<?= e(t('contact.bp.3')) ?>',
        '<?= e(t('contact.bp.4')) ?>',
        '<?= e(t('contact.bp.5')) ?>',
        '<?= e(t('contact.bp.6')) ?>'
    ],
    ready: '<?= e(t('contact.bp.ready')) ?>',
    stepWord: '<?= e(t('contact.step')) ?>',
    review: '<?= e(t('contact.btn.review')) ?>',
    submit: '<?= e(t('contact.btn.submit')) ?>',
    next: '<?= e(t('contact.btn.next')) ?>'
};
</script>