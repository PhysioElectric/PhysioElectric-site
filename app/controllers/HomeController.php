<?php
declare(strict_types=1);

final class HomeController
{
    public static function index(): void
    {
        $lang = lang();
        $featured   = ProjectModel::allPublished(6);
        $latestPosts = PostModel::allPublished(3);
        $categories = CategoryModel::active();

        $title       = t('meta.home');
        $description = (string) setting($lang === 'fa' ? 'hero_subtitle_fa' : 'hero_subtitle_en', t('meta.home'));

        $seo = [
            'title'       => $title,
            'description' => $description,
            'url'         => url($lang),
            'type'        => 'website',
            'jsonld'      => [[
                '@context'     => 'https://schema.org',
                '@type'        => 'WebSite',
                'name'         => (string) setting('site_name', 'PhysioElectric'),
                'url'          => Config::baseUrl() . url($lang),
                'inLanguage'   => $lang,
            ]],
        ];

        view('home', [
            'seo'         => $seo,
            'featured'    => $featured,
            'latestPosts' => $latestPosts,
            'categories'  => $categories,
        ]);
    }
}
