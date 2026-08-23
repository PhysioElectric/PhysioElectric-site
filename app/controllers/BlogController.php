<?php
declare(strict_types=1);

final class BlogController
{
    public static function index(): void
    {
        $lang = lang();
        $posts = PostModel::allPublished();
        $seo = [
            'title'       => t('meta.blog'),
            'description' => t('blog.subtitle'),
            'url'         => url($lang, 'blog'),
        ];
        view('blog/index', ['seo' => $seo, 'posts' => $posts]);
    }

    public static function show(string $slug): void
    {
        $lang = lang();
        $post = PostModel::bySlug($slug);
        if ($post === null || $post['status'] !== 'published') {
            not_found();
        }

        $related = PostModel::related((int) $post['id'], 3);
        $title   = L($post, 'title');
        $desc    = L($post, 'meta_desc', L($post, 'excerpt'));
        $image   = (string) ($post['image'] ?? '');
        $site    = (string) setting('site_name', 'PhysioElectric');

        $jsonld = [
            '@context'     => 'https://schema.org',
            '@type'        => 'BlogPosting',
            'headline'     => $title,
            'description'  => $desc,
            'image'        => $image !== '' ? Config::baseUrl() . $image : Config::baseUrl() . '/assets/images/og-default.svg',
            'datePublished'=> (string) ($post['published_at'] ?? $post['created_at']),
            'dateModified' => (string) $post['updated_at'],
            'inLanguage'   => $lang,
            'mainEntityOfPage' => Config::baseUrl() . url($lang, 'blog/' . slugOf($post)),
            'author'       => ['@type' => 'Organization', 'name' => $site],
            'publisher'    => [
                '@type' => 'Organization',
                'name'  => $site,
                'logo'  => ['@type' => 'ImageObject', 'url' => Config::baseUrl() . '/assets/images/logo.svg'],
            ],
        ];

        $seo = [
            'title'    => (string) ($post['meta_title_' . $lang] ?? '') !== ''
                ? (string) $post['meta_title_' . $lang]
                : $title,
            'description' => $desc,
            'url'         => url($lang, 'blog/' . slugOf($post)),
            'type'        => 'article',
            'image'       => $image,
            'jsonld'      => [$jsonld],
        ];

        view('blog/show', [
            'seo'     => $seo,
            'post'    => $post,
            'related' => $related,
        ]);
    }
}
