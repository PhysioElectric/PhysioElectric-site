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
 *   /admin/posts[...], /admin/projects[...],
 *   /admin/upload (POST, JSON), /admin/media (JSON)
 *
 * Ops:
 *   /healthz              -> liveness probe (no database access)
 */

define('BASE_PATH', __DIR__);

require BASE_PATH . '/config.php';
Config::boot();

require BASE_PATH . '/core/Security.php';
require BASE_PATH . '/core/Database.php';
require BASE_PATH . '/core/Csrf.php';
require BASE_PATH . '/core/RateLimiter.php';
require BASE_PATH . '/core/Auth.php';
require BASE_PATH . '/core/Totp.php';
require BASE_PATH . '/core/Captcha.php';
require BASE_PATH . '/core/PasswordPolicy.php';
require BASE_PATH . '/core/HtmlSanitizer.php';
require BASE_PATH . '/core/functions.php';
require BASE_PATH . '/models/CategoryModel.php';
require BASE_PATH . '/models/ProjectModel.php';
require BASE_PATH . '/models/PostModel.php';
require BASE_PATH . '/models/TeamModel.php';
require BASE_PATH . '/models/MessageModel.php';
require BASE_PATH . '/models/UserModel.php';
require BASE_PATH . '/controllers/HomeController.php';
require BASE_PATH . '/controllers/AboutController.php';
require BASE_PATH . '/controllers/ContactController.php';
require BASE_PATH . '/controllers/BlogController.php';
require BASE_PATH . '/controllers/ProjectController.php';
require BASE_PATH . '/controllers/InquiryController.php';
require BASE_PATH . '/controllers/admin/AuthController.php';
require BASE_PATH . '/controllers/admin/DashboardController.php';
require BASE_PATH . '/controllers/admin/PostController.php';
require BASE_PATH . '/controllers/admin/ProjectController.php';
require BASE_PATH . '/controllers/admin/UploadController.php';
require BASE_PATH . '/controllers/admin/TeamController.php';
require BASE_PATH . '/controllers/admin/MessageController.php';
require BASE_PATH . '/controllers/admin/AccountController.php';
require BASE_PATH . '/controllers/admin/AdminUserController.php';

// ---------------- dev server static passthrough ----------------
// (Apache handles static files via .htaccess; this is for `php -S` dev mode)
if (PHP_SAPI === 'cli-server') {
    $staticPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_string($staticPath) && $staticPath !== '/' && $staticPath !== '') {
        $real = realpath(__DIR__ . $staticPath);
        $root = realpath(__DIR__);
        // Only serve real files that live inside the document root, and never
        // hand out PHP sources or dotfiles.
        if ($real !== false && $root !== false
            && str_starts_with($real, $root . DIRECTORY_SEPARATOR)
            && is_file($real)
            && !str_ends_with($real, '.php')
            && !str_contains($staticPath, '/.')
            // Mirror uploads/.htaccess under the dev server: /uploads only
            // ever serves images.
            && (!str_starts_with($staticPath, '/uploads/')
                || (bool) preg_match('/\.(jpe?g|png|webp)$/i', $staticPath))
        ) {
            return false;
        }
    }
}

// ---------------- request context ------------------------------
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
Security::guardMethod($method);
Security::guardBodySize(8 * 1024 * 1024);

$path = Security::requestPath((string) ($_SERVER['REQUEST_URI'] ?? '/'));
if ($path === '/index.php') {
    $path = '/';
}

// ---------------- error handling -------------------------------
set_exception_handler(static function (Throwable $e): void {
    $req = Security::requestId();
    error_log(sprintf(
        '[PE] req=%s Uncaught %s: %s @ %s:%d',
        $req,
        $e::class,
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ));
    Security::audit('uncaught_exception', ['class' => $e::class]);
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Request-Id: ' . $req);
    }
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">'
        . '<title>500 Internal Server Error</title></head>'
        . '<body style="font-family:sans-serif;padding:3rem;background:#f8fafc;color:#0f172a">'
        . '<h1>500</h1><p>Server error. Please try again later.</p>'
        . '<p style="color:#64748b;font-size:.85rem">request id: ' . htmlspecialchars($req, ENT_QUOTES, 'UTF-8') . '</p>'
        . '</body></html>';
});

// ---------------- session hardening ----------------------------
session_name('PESESS');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => Config::isHttps(),
    'httponly' => true,
    'samesite' => 'Lax',
]);
// PHP's default cache limiter stamps "Cache-Control: no-store, no-cache" on
// EVERY response as soon as a session starts — including the public, fully
// cacheable pages. Caching is decided explicitly per context below instead.
session_cache_limiter('');
session_start();

// ---------------- security headers ------------------------------
$isAdmin = ($path === '/admin' || str_starts_with($path, '/admin/'));
Security::sendHeaders($isAdmin);

// ---------------- helpers ---------------------------------------
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

/** 405 for a route that exists but not for this verb. */
function method_not_allowed(string $allow): never
{
    http_response_code(405);
    header('Allow: ' . $allow);
    exit('405 - Method Not Allowed');
}

// =================================================================
//  ROUTING
// =================================================================

// 0) Liveness probe — intentionally touches no database.
if ($path === '/healthz') {
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    exit(json_encode(['status' => 'ok', 'ts' => date('c')]));
}

// 1) Root -> default language (Persian)
if ($path === '/') {
    redirect('/fa', 301);
}

// 2) Admin panel (no language prefix)
if ($isAdmin) {
    $adminSub = trim(substr($path, strlen('/admin')), '/'); // '' | 'login' | 'posts/3/edit' ...

    // ---- public endpoints ----
    if ($adminSub === 'login') {
        if ($method === 'POST') {
            Csrf::protect();
            Admin\AuthController::loginPost();
        }
        Admin\AuthController::loginForm();
        exit;
    }

    // Second step of a 2FA login (password already verified). Only reachable
    // while a challenge is parked in the session.
    if ($adminSub === 'login/2fa') {
        if ($method === 'POST') {
            Csrf::protect();
            Admin\AuthController::twoFaPost();
        }
        Admin\AuthController::twoFaForm();
        exit;
    }

    if ($adminSub === 'logout') {
        if ($method !== 'POST') {
            redirect('/admin/login');
        }
        Csrf::protect();
        Admin\AuthController::logoutPost();
        exit;
    }

    // ---- everything else requires authentication ----
    Auth::requireLogin();

    if ($method === 'POST') {
        Csrf::protect();
    }

    // ---- Forced password change (first login / admin-created users) ----
    // While users.force_password_change = 1 the ONLY reachable admin page is
    // /admin/forced-password; everything else is bounced back to it.
    if (Auth::forcePasswordChange()) {
        if ($adminSub !== 'forced-password') {
            redirect('/admin/forced-password');
        }
    } elseif ($adminSub === 'forced-password') {
        redirect('/admin/dashboard');
    }

    // ---- Central RBAC gates -------------------------------------------
    //   * /admin/users/*             → super_admin only
    //   * every content mutation      → editor or super_admin
    //   * editing surfaces (create/edit/delete/upload/media forms) are
    //     refused for viewers even on GET
    //   * "account" and "forced-password" stay open to EVERY role — every
    //     admin (incl. viewers) must be able to rotate their own password.
    if (str_starts_with($adminSub, 'users')) {
        Auth::requireRole('super_admin');
    } elseif ($method === 'POST'
        && !str_starts_with($adminSub, 'account')
        && $adminSub !== 'forced-password') {
        Auth::requireRole('editor', 'super_admin');
    } elseif (!Auth::hasRole('editor', 'super_admin')
        && preg_match('#(?:^|/)(create|edit|delete|upload|media)(?:/|$)#', $adminSub) === 1) {
        Auth::requireRole('editor', 'super_admin');
    }

    if ($adminSub === '' || $adminSub === 'dashboard') {
        if ($method !== 'GET') {
            method_not_allowed('GET');
        }
        Admin\DashboardController::index();
        exit;
    }

    // ---- Posts CRUD ----
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
        if ($method !== 'GET') {
            method_not_allowed('GET');
        }
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

    // ---- Projects CRUD ----
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
        if ($method !== 'GET') {
            method_not_allowed('GET');
        }
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

    // ---- Uploads (AJAX JSON endpoints) ----
    if ($adminSub === 'upload') {
        if ($method !== 'POST') {
            method_not_allowed('POST');
        }
        Admin\UploadController::upload();
        exit;
    }
    if ($adminSub === 'media') {
        if ($method !== 'GET') {
            method_not_allowed('GET');
        }
        Admin\UploadController::media();
        exit;
    }

    // ---- Team members (About page) ----
    if ($adminSub === 'team') {
        if ($method !== 'GET') {
            method_not_allowed('GET');
        }
        Admin\TeamController::index();
        exit;
    }
    if ($adminSub === 'team/create') {
        if ($method === 'POST') {
            Admin\TeamController::create();
        }
        Admin\TeamController::createForm();
        exit;
    }
    if (preg_match('#^team/(\d+)/edit$#', $adminSub, $m)) {
        if ($method === 'POST') {
            method_not_allowed('GET');
        }
        Admin\TeamController::editForm((int) $m[1]);
        exit;
    }
    if (preg_match('#^team/(\d+)$#', $adminSub, $m) && $method === 'POST') {
        Admin\TeamController::update((int) $m[1]);
        exit;
    }
    if ($adminSub === 'team/delete' && $method === 'POST') {
        Admin\TeamController::delete();
        exit;
    }

    // ---- Received messages (public inquiries) ----
    if ($adminSub === 'messages') {
        if ($method !== 'GET') {
            method_not_allowed('GET');
        }
        Admin\MessageController::index();
        exit;
    }
    if (preg_match('#^messages/(\d+)/read$#', $adminSub, $m) && $method === 'POST') {
        Admin\MessageController::toggleRead((int) $m[1]);
        exit;
    }
    if (preg_match('#^messages/(\d+)/file/(\d+)$#', $adminSub, $m)) {
        if ($method !== 'GET') {
            method_not_allowed('GET');
        }
        Admin\MessageController::download((int) $m[1], (int) $m[2]);
        exit;
    }
    if (preg_match('#^messages/(\d+)$#', $adminSub, $m)) {
        if ($method !== 'GET') {
            method_not_allowed('GET');
        }
        Admin\MessageController::show((int) $m[1]);
        exit;
    }
    if ($adminSub === 'messages/delete' && $method === 'POST') {
        Admin\MessageController::delete();
        exit;
    }

    // ---- My account (own password / 2FA) ----
    if ($adminSub === 'account') {
        if ($method !== 'GET') {
            method_not_allowed('GET');
        }
        Admin\AccountController::index();
        exit;
    }
    if ($adminSub === 'account/password' && $method === 'POST') {
        Admin\AccountController::changePasswordPost();
        exit;
    }
    if ($adminSub === 'forced-password') {
        if ($method === 'POST') {
            Admin\AccountController::forcedPasswordPost();
        }
        Admin\AccountController::forcedPasswordForm();
        exit;
    }
    if ($adminSub === 'account/2fa/setup') {
        if ($method === 'POST') {
            Admin\AccountController::twofaSetupPost();
        }
        Admin\AccountController::twofaSetupForm();
        exit;
    }
    if ($adminSub === 'account/2fa/disable' && $method === 'POST') {
        Admin\AccountController::twofaDisablePost();
        exit;
    }

    // ---- Admin-user management (RBAC, super_admin only) ----
    if ($adminSub === 'users') {
        if ($method !== 'GET') {
            method_not_allowed('GET');
        }
        Admin\AdminUserController::index();
        exit;
    }
    if ($adminSub === 'users/create') {
        if ($method === 'POST') {
            Admin\AdminUserController::store();
        }
        Admin\AdminUserController::createForm();
        exit;
    }
    if (preg_match('#^users/(\d+)/edit$#', $adminSub, $m)) {
        if ($method !== 'GET') {
            method_not_allowed('GET');
        }
        Admin\AdminUserController::editForm((int) $m[1]);
        exit;
    }
    if (preg_match('#^users/(\d+)$#', $adminSub, $m) && $method === 'POST') {
        Admin\AdminUserController::update((int) $m[1]);
        exit;
    }
    if ($adminSub === 'users/toggle' && $method === 'POST') {
        Admin\AdminUserController::toggleActive();
        exit;
    }
    if ($adminSub === 'users/delete' && $method === 'POST') {
        Admin\AdminUserController::delete();
        exit;
    }

    not_found();
}

// 3) Public, language-prefixed routes
if (preg_match('#^/(fa|en)(?:/([A-Za-z0-9\-]+(?:/[A-Za-z0-9\-]+)*))?$#', $path, $m)) {
    setLang($m[1]);
    $sub = $m[2] ?? '';

    // Public inquiry submission from the contact / project-order wizard.
    if ($sub === 'inquiry' && $method === 'POST') {
        InquiryController::store();
        exit;
    }

    if ($method !== 'GET' && $method !== 'HEAD') {
        method_not_allowed('GET, HEAD');
    }

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
        error_log(sprintf(
            '[PE] req=%s route error %s: %s @ %s:%d',
            Security::requestId(),
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ));
        http_response_code(500);
        exit('500 - Internal server error.');
    }
}

// 4) Anything else
not_found();
