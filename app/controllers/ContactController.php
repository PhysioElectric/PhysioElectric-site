<?php
declare(strict_types=1);

final class ContactController
{
    public static function index(): void
    {
        $lang = lang();
        $seo = [
            'title'       => t('meta.contact'),
            'description' => t('contact.subtitle'),
            'url'         => url($lang, 'contact'),
        ];
        view('contact', [
            'seo'     => $seo,
            'telegram'=> cta_telegram_url(),
            'tgScheme'=> cta_tg_scheme(),
            'email'   => cta_mailto_url(),
            'emailAddr' => (string) setting('contact_email', 'info@physioelectric.com'),
            'phone'   => (string) setting('contact_phone', ''),
            'address' => (string) setting($lang === 'fa' ? 'address_fa' : 'address_en', ''),
        ]);
    }
}
