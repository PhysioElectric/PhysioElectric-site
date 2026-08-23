<?php
declare(strict_types=1);

namespace Admin;

final class DashboardController
{
    public static function index(): void
    {
        $pdo          = \Database::pdo();
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

    private static function uploadsCount(): int
    {
        $dir = BASE_PATH . '/uploads';
        if (!is_dir($dir)) {
            return 0;
        }
        $n = 0;
        foreach (scandir($dir) ?: [] as $f) {
            if ($f === '.' || $f === '..' || str_starts_with($f, '.')) {
                continue;
            }
            if (is_file($dir . '/' . $f)) {
                $n++;
            }
        }
        return $n;
    }
}
