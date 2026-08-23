<?php
declare(strict_types=1);

/**
 * PhysioElectric - Front Controller (router)
 *
 * URL map (public, language prefixed):
 *   /                     -> 301 /fa   (Persian is the default language)
 *   /{lang}               -> Home
 *   /{lang}/about         -> About
 *   /{lang}/contact       -> Contact
 *   /{lang}/blog          -> Blog archive
 *   /{lang}/blog/{slug}   -> Single post
 *   /{lang}/projects      -> All projects
 *   /{lang}/projects/{cat}             -> Category archive
 *   /{lang}/projects/{cat}/{slug}      -> Single project
 *
 * URL map (admin, secured):
 *   /admin, /admin/login, /admin/logout, /admin/dashboard,
 *   /admin/posts[...], /admin/projects[...], /admin/settings,
 *   /admin/upload (POST, JSON), /admin/media (JSON)
 */

define('BASE_PATH', __DIR__);

require BASE_PATH . '/config.php';
require BASE_PATH . '/core/Database.php';
require BASE_PATH . '/core/Csrf.php';
require BASE_PATH . '/core/RateLimiter.php';
require BASE_PATH . '/core/Auth.php';
require BASE_PATH . '/core/HtmlSanitizer.php';
require BASE_PATH . '/core/functions.php';
require BASE_PATH . '/models/CategoryModel.php';
require BASE_PATH . '/models/ProjectModel.php';
require BASE_PATH . '/models/PostModel.php';
require BASE_PATH . '/models/SettingsModel.php';
require BASE_PATH . '/controllers/HomeController.php';
require BASE_PATH . '/controllers/AboutController.php';
require BASE_PATH . '/controllers/ContactController.php';
require BASE_PATH . '/controllers/BlogController.php';
require BASE_PATH . '/controllers/ProjectController.php';
require BASE_PATH . '/controllers/admin/AuthController.php';
require BASE_PATH . '/controllers/admin/DashboardController.php';
require BASE_PATH . '/controllers/admin/PostController.php';
require BASE_PATH . '/controllers/admin/ProjectController.php';
require BASE_PATH . '/controllers/admin/SettingsController.php';
require BASE_PATH . '/controllers/admin/UploadController.php';

// ---------------- dev server static passthrough ----------------
// (Apache handles static files via .htaccess; this is for `php -S` dev mode)
if (PHP_SAPI === 'cli-server') {
    $staticPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if ($staticPath !== '/' && is_file(__DIR__ . $staticPath)) {
        return false;
    }
}

// ---------------- error handling -------------------------------
error_reporting(E_ALL);
if (Config::isProduction()) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

set_exception_handler(function (Throwable $e): void {
    error_log('[PE] Uncaught ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo '<!doctype html><html><head><meta charset="utf-8"></head><body style="font-family:sans-serif;padding:3rem;background:#f8fafc;color:#0f172a"><h1>500</h1><p>Server error. Please try again later.</p></body></html>';
});

// ---------------- session hardening ----------------------------
session_name('PESESS');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// ---------------- security headers ------------------------------
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');
header_remove('X-Powered-By');

// ---------------- request context ------------------------------
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = $_SERVER['REQUEST_URI'] ?? '/';
$path   = rawurldecode((string) parse_url($uri, PHP_URL_PATH));
$path   = '/' . trim($path, '/');
if ($path !== '/') {
    $path = '/' . rtrim(trim($path, '/'), '/');
}
if ($path === '/index.php') {
    $path = '/';
}

$SLUG = '[A-Za-z0-9\-]+';

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
}

function pop_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($f) ? $f : null;
}

function not_found(): never
{
    http_response_code(404);
    $lang = lang();
    seo_head([
        'title'       => $lang === 'fa' ? 'صفحه یافت نشد' : 'Page Not Found',
        'description' => t('error.404.text'),
        'url'         => url($lang),
        'noindex'     => true,
    ]);
    require BASE_PATH . '/views/errors/404.php';
    require BASE_PATH . '/views/layouts/footer.php';
    exit;
}

function is_valid_slug(string $s): bool
{
    return (bool) preg_match('/^[A-Za-z0-9\-]{2,150}$/', $s);
}

// =================================================================
//  ROUTING
// =================================================================

// 1) Root -> default language (Persian)
if ($path === '/') {
    redirect('/fa', 301);
}

// 2) Admin panel (no language prefix)
if ($path === '/admin' || str_starts_with($path, '/admin/')) {
    $adminSub = trim(substr($path, strlen('/admin')), '/'); // '' | 'login' | 'posts/3/edit' ...

    // Public endpoints
    if ($adminSub === 'login') {
        if ($method === 'POST') {
            Csrf::protect();
            Admin\AuthController::loginPost();
        }
        Admin\AuthController::loginForm();
        exit;
    }

    if ($adminSub === 'logout') {
        if ($method === 'POST') {
            Csrf::protect();
            Admin\AuthController::logoutPost();
        }
        redirect('/admin/login');
    }

    // Everything else requires authentication
    Auth::requireLogin();

    if ($method === 'POST') {
        Csrf::protect();
    }

    if ($adminSub === '' || $adminSub === 'dashboard') {
        Admin\DashboardController::index();
        exit;
    }

    // Posts CRUD
    if ($adminSub === 'posts') {
        if ($method === 'POST') {
            Admin\PostController::create();
        }
        Admin\PostController::index();
        exit;
    }
    if ($adminSub === 'posts/create') {
        if ($method === 'POST') {
            Admin\PostController::create();
        }
        Admin\PostController::createForm();
        exit;
    }
    if (preg_match('#^posts/(\d+)/edit$#', $adminSub, $m)) {
        Admin\PostController::editForm((int) $m[1]);
        exit;
    }
    if (preg_match('#^posts/(\d+)$#', $adminSub, $m) && $method === 'POST') {
        Admin\PostController::update((int) $m[1]);
        exit;
    }
    if ($adminSub === 'posts/delete' && $method === 'POST') {
        Admin\PostController::delete();
        exit;
    }

    // Projects CRUD
    if ($adminSub === 'projects') {
        if ($method === 'POST') {
            Admin\ProjectController::create();
        }
        Admin\ProjectController::index();
        exit;
    }
    if ($adminSub === 'projects/create') {
        if ($method === 'POST') {
            Admin\ProjectController::create();
        }
        Admin\ProjectController::createForm();
        exit;
    }
    if (preg_match('#^projects/(\d+)/edit$#', $adminSub, $m)) {
        Admin\ProjectController::editForm((int) $m[1]);
        exit;
    }
    if (preg_match('#^projects/(\d+)$#', $adminSub, $m) && $method === 'POST') {
        Admin\ProjectController::update((int) $m[1]);
        exit;
    }
    if ($adminSub === 'projects/delete' && $method === 'POST') {
        Admin\ProjectController::delete();
        exit;
    }

    // Settings
    if ($adminSub === 'settings') {
        if ($method === 'POST') {
            Admin\SettingsController::save();
        }
        Admin\SettingsController::form();
        exit;
    }

    // Uploads (AJAX JSON endpoints)
    if ($adminSub === 'upload' && $method === 'POST') {
        Admin\UploadController::upload();
        exit;
    }
    if ($adminSub === 'media' && $method === 'GET') {
        Admin\UploadController::media();
        exit;
    }

    not_found();
}

// 3) Public, language-prefixed routes
if (preg_match('#^/(fa|en)(?:/([A-Za-z0-9\-]+(?:/[A-Za-z0-9\-]+)*))?$#', $path, $m)) {
    setLang($m[1]);
    $sub = $m[2] ?? '';

    try {
        if ($sub === '' || $sub === 'index') {
            HomeController::index();
            exit;
        }
        if ($sub === 'about') {
            AboutController::index();
            exit;
        }
        if ($sub === 'contact') {
            ContactController::index();
            exit;
        }

        if ($sub === 'blog') {
            BlogController::index();
            exit;
        }
        if (preg_match('#^blog/(' . $SLUG . ')$#', $sub, $sm)) {
            BlogController::show($sm[1]);
            exit;
        }

        if ($sub === 'projects') {
            ProjectController::index();
            exit;
        }
        if (preg_match('#^projects/(' . $SLUG . ')$#', $sub, $sm)) {
            ProjectController::category($sm[1]);
            exit;
        }
        if (preg_match('#^projects/(' . $SLUG . ')/(' . $SLUG . ')$#', $sub, $sm)) {
            ProjectController::show($sm[1], $sm[2]);
            exit;
        }
    } catch (Throwable $e) {
        error_log('[PE] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        exit('500 - Internal server error.');
    }
}

// 4) Anything else
not_found();
