<?php
declare(strict_types=1);

final class AboutController
{
    public static function index(): void
    {
        $lang = lang();
        $seo = [
            'title'       => t('meta.about'),
            'description' => t('about.subtitle'),
            'url'         => url($lang, 'about'),
        ];
        view('about', ['seo' => $seo]);
    }
}
