<?php
/**
 * Contact page.
 * Expects: $telegram, $tgScheme, $email, $emailAddr, $phone, $address
 */
?>
<section class="pt-36 pb-20 bg-white min-h-[70vh]">
    <div class="max-w-5xl mx-auto px-6 lg:px-8">
        <div class="text-center reveal">
            <h1 class="text-4xl md:text-5xl font-bold tracking-tight text-physio-950"><?= e(t('contact.title')) ?></h1>
            <p class="mt-4 text-lg text-slate-500 max-w-2xl mx-auto"><?= e(t('contact.subtitle')) ?></p>
        </div>

        <div class="mt-14 grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Telegram -->
            <a href="<?= e($telegram) ?>" data-tg-link="<?= e($tgScheme) ?>" class="reveal glass-card rounded-2xl p-8 text-center group pe-card">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-physio-100 text-physio-600 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                    <i data-lucide="send" class="w-7 h-7"></i>
                </div>
                <h3 class="text-lg font-bold text-physio-950"><?= e(t('contact.telegram')) ?></h3>
                <p class="mt-2 text-sm text-slate-500" dir="ltr">@<?= e(telegram_user()) ?></p>
            </a>

            <!-- Email -->
            <a href="<?= e($email) ?>" class="reveal reveal-delay-1 glass-card rounded-2xl p-8 text-center group pe-card">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-physio-100 text-physio-600 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                    <i data-lucide="mail" class="w-7 h-7"></i>
                </div>
                <h3 class="text-lg font-bold text-physio-950"><?= e(t('contact.email')) ?></h3>
                <p class="mt-2 text-sm text-slate-500 break-words" dir="ltr"><?= e($emailAddr) ?></p>
            </a>

            <!-- Phone -->
            <?php if ($phone !== ''): ?>
            <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>" class="reveal reveal-delay-2 glass-card rounded-2xl p-8 text-center group pe-card">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-physio-100 text-physio-600 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                    <i data-lucide="phone" class="w-7 h-7"></i>
                </div>
                <h3 class="text-lg font-bold text-physio-950"><?= e(t('contact.phone')) ?></h3>
                <p class="mt-2 text-sm text-slate-500" dir="ltr"><?= e($phone) ?></p>
            </a>
            <?php else: ?>
            <div class="reveal reveal-delay-2 glass-card rounded-2xl p-8 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-physio-100 text-physio-600 flex items-center justify-center mb-5">
                    <i data-lucide="map-pin" class="w-7 h-7"></i>
                </div>
                <h3 class="text-lg font-bold text-physio-950"><?= e(t('contact.address')) ?></h3>
                <p class="mt-2 text-sm text-slate-500"><?= e($address !== '' ? $address : '—') ?></p>
            </div>
            <?php endif; ?>
        </div>

        <div class="mt-10 text-center text-sm text-slate-400 reveal">
            <p class="inline-flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4"></i>
                <?= e(t('contact.worktime')) ?>
                <?php if ($address !== ''): ?>
                    &nbsp;•&nbsp; <?= e($address) ?>
                <?php endif; ?>
            </p>
        </div>
    </div>
</section>
