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
        // Team members managed from the admin panel. Falls back to the
        // translated defaults when the DB is empty / not migrated yet.
        view('about', ['seo' => $seo, 'members' => \TeamModel::all()]);
    }
}
