<?php
declare(strict_types=1);

namespace Admin;

final class DashboardController
{
    public static function index(): void
    {
        $posts        = \PostModel::all();
        $projects     = \ProjectModel::allWithCategory();
        $published    = \PostModel::countPublished() + \ProjectModel::countPublished();
        $uploadCount  = self::uploadsCount();

        admin_view('dashboard', [
            'stats'    => [
                'posts'     => count($posts),
                'projects'  => count($projects),
                'published' => $published,
                'uploads'   => $uploadCount,
            ],
            'recentPosts'    => array_slice($posts, 0, 5),
            'recentProjects' => array_slice($projects, 0, 5),
        ]);
    }

    /** Count of stored *images* (index.html used to be counted as an upload). */
    private static function uploadsCount(): int
    {
        $dir = BASE_PATH . '/uploads';
        if (!is_dir($dir)) {
            return 0;
        }
        $n = 0;
        foreach (scandir($dir) ?: [] as $f) {
            if (!is_string($f) || $f === '.' || $f === '..' || str_starts_with($f, '.')) {
                continue;
            }
            if (preg_match('/\.(jpe?g|png|webp)$/i', $f) && is_file($dir . '/' . $f)) {
                $n++;
            }
        }
        return $n;
    }
}
