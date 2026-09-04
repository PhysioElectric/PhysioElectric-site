<?php
/**
 * Admin: site settings.
 * Expects: $values (map of settings)
 */
$adminTitle  = t('admin.settings');
$adminActive = 'settings';
$heroTitleFa = (string) ($values['hero_title_fa'] ?? '');
$heroTitleEn = (string) ($values['hero_title_en'] ?? '');

$groups = [
    'identity' => [
        'title' => t('admin.set.identity'),
        'fields' => [
            ['key' => 'site_name', 'label' => t('admin.set.siteName'), 'type' => 'text', 'dir' => 'ltr'],
            ['key' => 'telegram_user', 'label' => t('admin.set.telegram'), 'type' => 'text', 'dir' => 'ltr', 'placeholder' => 'my_support_id'],
        ],
    ],
    'contact' => [
        'title' => t('admin.set.contact'),
        'fields' => [
            ['key' => 'contact_email', 'label' => t('admin.set.email'), 'type' => 'email', 'dir' => 'ltr'],
            ['key' => 'contact_phone', 'label' => t('admin.set.phone'), 'type' => 'text', 'dir' => 'ltr', 'placeholder' => '+98 912 000 0000'],
            ['key' => 'address_fa', 'label' => t('admin.set.addressFa'), 'type' => 'text'],
            ['key' => 'address_en', 'label' => t('admin.set.addressEn'), 'type' => 'text', 'dir' => 'ltr'],
        ],
    ],
    'hero' => [
        'title' => t('admin.set.hero'),
        'fields' => [
            ['key' => 'hero_badge_fa', 'label' => t('admin.set.heroBadge') . ' (FA)', 'type' => 'text'],
            ['key' => 'hero_badge_en', 'label' => t('admin.set.heroBadge') . ' (EN)', 'type' => 'text', 'dir' => 'ltr'],
            ['key' => 'hero_title_fa', 'label' => t('admin.set.heroTitle') . ' (FA) — برای خط جدید از <br> استفاده کنید', 'type' => 'textarea'],
            ['key' => 'hero_title_en', 'label' => t('admin.set.heroTitle') . ' (EN) — use <br> for line breaks', 'type' => 'textarea', 'dir' => 'ltr'],
            ['key' => 'hero_subtitle_fa', 'label' => t('admin.set.heroSub') . ' (FA)', 'type' => 'textarea'],
            ['key' => 'hero_subtitle_en', 'label' => t('admin.set.heroSub') . ' (EN)', 'type' => 'textarea', 'dir' => 'ltr'],
        ],
    ],
    'footer' => [
        'title' => t('admin.set.footer'),
        'fields' => [
            ['key' => 'footer_desc_fa', 'label' => t('admin.set.footerDesc') . ' (FA)', 'type' => 'textarea'],
            ['key' => 'footer_desc_en', 'label' => t('admin.set.footerDesc') . ' (EN)', 'type' => 'textarea', 'dir' => 'ltr'],
        ],
    ],
];
?>
<p class="text-sm text-slate-500 mb-5 max-w-2xl leading-relaxed">
    این تنظیمات روی کل سایت (فارسی و انگلیسی) اعمال می‌شوند. نام کاربری تلگرام، بدون @ وارد شود
    و در دکمه‌های «سفارش پروژه» و بخش تماس استفاده می‌شود.
</p>

<form method="post" action="/admin/settings" class="space-y-6">
    <?= Csrf::field() ?>

    <?php foreach ($groups as $group): ?>
        <div class="admin-card p-6">
            <h2 class="text-sm font-bold text-slate-700 mb-5"><?= e($group['title']) ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <?php foreach ($group['fields'] as $f): ?>
                    <?php $val = (string) ($values[$f['key']] ?? ''); ?>
                    <div class="<?= $f['type'] === 'textarea' ? 'md:col-span-2' : '' ?>">
                        <label class="admin-label" for="set_<?= e($f['key']) ?>"><?= $f['label'] /* label contains safe static <br> text */ ?></label>
                        <?php if ($f['type'] === 'textarea'): ?>
                            <textarea id="set_<?= e($f['key']) ?>" name="<?= e($f['key']) ?>" rows="2"
                                      class="admin-textarea" dir="<?= e($f['dir'] ?? 'auto') ?>"><?= e($val) ?></textarea>
                        <?php else: ?>
                            <input type="<?= e($f['type']) ?>" id="set_<?= e($f['key']) ?>" name="<?= e($f['key']) ?>"
                                   class="admin-input" dir="<?= e($f['dir'] ?? 'auto') ?>"
                                   <?= isset($f['placeholder']) ? 'placeholder="' . e($f['placeholder']) . '"' : '' ?>>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="flex items-center gap-3">
        <button type="submit" class="admin-btn admin-btn-primary !px-8 !py-3">
            <i data-lucide="save" class="w-4 h-4"></i>
            <?= e(t('admin.set.save')) ?>
        </button>
        <a href="/admin/dashboard" class="admin-btn admin-btn-ghost"><?= e(t('admin.cancel')) ?></a>
    </div>
</form>
