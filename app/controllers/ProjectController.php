<?php
declare(strict_types=1);

final class ProjectController
{
    /** /{lang}/projects — all projects with category filter chips. */
    public static function index(): void
    {
        $lang = lang();
        $projects   = ProjectModel::allPublished();
        $categories = CategoryModel::active();
        $seo = [
            'title'       => t('meta.projects'),
            'description' => t('projects.subtitle'),
            'url'         => url($lang, 'projects'),
        ];
        view('projects/index', [
            'seo'        => $seo,
            'projects'   => $projects,
            'categories' => $categories,
        ]);
    }

    /** /{lang}/projects/{category-slug} — one category archive. */
    public static function category(string $categorySlug): void
    {
        $lang = lang();
        $cat  = CategoryModel::bySlug($categorySlug);
        if ($cat === null) {
            not_found();
        }
        $projects = ProjectModel::byCategory($categorySlug);
        $allCats  = CategoryModel::active();

        $name = L($cat, 'name');
        $desc = L($cat, 'description', $name);

        $jsonld = [
            '@context'   => 'https://schema.org',
            '@type'      => 'CollectionPage',
            'name'       => $name,
            'description'=> $desc,
            'inLanguage' => $lang,
            'url'        => Config::baseUrl() . url($lang, 'projects/' . $categorySlug),
        ];

        $seo = [
            'title'       => $name . ' | ' . (string) setting('site_name', 'PhysioElectric'),
            'description' => $desc,
            'url'         => url($lang, 'projects/' . $categorySlug),
            'jsonld'      => [$jsonld],
        ];

        view('projects/category', [
            'seo'        => $seo,
            'cat'        => $cat,
            'projects'   => $projects,
            'categories' => $allCats,
        ]);
    }

    /** /{lang}/projects/{category-slug}/{project-slug} — single project. */
    public static function show(string $categorySlug, string $projectSlug): void
    {
        $lang = lang();
        $cat  = CategoryModel::bySlug($categorySlug);
        if ($cat === null) {
            not_found();
        }
        $project = ProjectModel::bySlug($projectSlug);
        if ($project === null
            || $project['status'] !== 'published'
            || (int) $project['category_id'] !== (int) $cat['id']) {
            not_found();
        }

        $related = ProjectModel::related((int) $project['id'], (int) $cat['id'], 3);
        $title   = L($project, 'title');
        $desc    = L($project, 'meta_desc', L($project, 'short_desc'));
        $image   = (string) ($project['image'] ?? '');
        $site    = (string) setting('site_name', 'PhysioElectric');

        $jsonld = [
            '@context'    => 'https://schema.org',
            '@type'       => 'CreativeWork',
            'name'        => $title,
            'description' => $desc,
            'image'       => $image !== '' ? Config::baseUrl() . $image : Config::baseUrl() . '/assets/images/og-default.svg',
            'inLanguage'  => $lang,
            'url'         => Config::baseUrl() . url($lang, 'projects/' . $categorySlug . '/' . slugOf($project)),
            'about'       => ['@type' => 'Thing', 'name' => L($cat, 'name')],
            'provider'    => [
                '@type'  => 'Organization',
                'name'   => $site,
                'sameAs' => ['https://t.me/' . rawurlencode(telegram_user())],
            ],
        ];

        $seo = [
            'title'    => (string) ($project['meta_title_' . $lang] ?? '') !== ''
                ? (string) $project['meta_title_' . $lang]
                : $title,
            'description' => $desc,
            'url'         => url($lang, 'projects/' . $categorySlug . '/' . slugOf($project)),
            'image'       => $image,
            'jsonld'      => [$jsonld],
        ];

        view('projects/show', [
            'seo'        => $seo,
            'project'    => $project,
            'cat'        => $cat,
            'related'    => $related,
            'categories' => CategoryModel::active(),
            'ctaTg'      => cta_telegram_url($title),
            'ctaTgScheme'=> cta_tg_scheme(),
            'ctaMailto'  => cta_mailto_url($title),
        ]);
    }
}
