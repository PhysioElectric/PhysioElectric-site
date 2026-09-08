<?php
declare(strict_types=1);

/**
 * Central configuration. All values come from environment variables
 * (see docker-compose.yml / .env.example) with safe defaults.
 *
 * SECURITY NOTES
 *  - Nothing here is secret-by-default: every credential must be supplied
 *    through the environment. The defaults are the *development* values
 *    shipped in .env.example and are refused in production (see entrypoint).
 *  - `baseUrl()` no longer trusts the Host header (host-header injection
 *    poisoned canonical / hreflang / OG / JSON-LD URLs).
 */
final class Config
{
    /** Every env var this application is allowed to read. */
    private const ALLOWED_KEYS = [
        'APP_ENV', 'APP_TIMEZONE', 'SITE_BASE_URL', 'TRUSTED_HOSTS',
        'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS',
        'ADMIN_NAME', 'ADMIN_EMAIL', 'ADMIN_PASSWORD',
        'ADMIN_PASSWORD_RESET', 'ADMIN_PASSWORD_GENERATED',
        'SESSION_IDLE_MINUTES', 'SESSION_ABSOLUTE_MINUTES',
        'LOGIN_MAX_ATTEMPTS', 'LOGIN_WINDOW_MINUTES',
        'MAX_UPLOAD_BYTES', 'MAX_IMAGE_EDGE', 'MAX_UPLOADS',
        'CSP_ENABLED', 'CSP_ALLOW_UNSAFE_INLINE', 'HSTS_MAX_AGE',
        'SECURITY_LOG_ENABLED',
        'CAPTCHA_PROVIDER', 'CAPTCHA_SITE_KEY', 'CAPTCHA_SECRET_KEY',
    ];

    /** @var array<string,string>|null */
    private static ?array $env = null;

    private static bool $booted = false;

    /** Read the environment once, whitelist-only. */
    public static function env(): array
    {
        if (self::$env === null) {
            // Hosting / bind-mount: pick up a dropped `.env` so operators do
            // not have to export variables by hand. Existing non-empty process
            // env (Docker compose, secrets, putenv from tests) always wins.
            self::loadDotenv();
            $env = [];
            foreach (self::ALLOWED_KEYS as $key) {
                $val = self::readRaw($key);
                if ($val !== null && $val !== '') {
                    $env[$key] = $val;
                }
            }
            self::$env = $env;
        }
        return self::$env;
    }

    /**
     * getenv() / $_ENV / $_SERVER, in that order. PHP-FPM and some Apache
     * SAPIs expose PassEnv values only in $_SERVER, so getenv() alone was
     * silently dropping ADMIN_* / SITE_BASE_URL on real hosts.
     */
    private static function readRaw(string $key): ?string
    {
        $val = getenv($key);
        if (is_string($val) && $val !== '') {
            return $val;
        }
        if (isset($_ENV[$key]) && is_string($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && is_string($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }
        return null;
    }

    /**
     * Parse `.env` files without executing them. Empty values never clobber
     * a non-empty process environment (so Docker secrets stay intact).
     */
    private static function loadDotenv(): void
    {
        $candidates = [
            __DIR__ . DIRECTORY_SEPARATOR . '.env',
            dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env',
        ];
        $seen = [];
        foreach ($candidates as $path) {
            if (!is_readable($path) || !is_file($path)) {
                continue;
            }
            $real = realpath($path) ?: $path;
            if (isset($seen[$real])) {
                continue;
            }
            $seen[$real] = true;
            self::applyDotenvFile($real);
        }
    }

    private static function applyDotenvFile(string $path): void
    {
        $raw = @file_get_contents($path);
        if (!is_string($raw) || $raw === '') {
            return;
        }
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }
        foreach (preg_split("/\r\n|\n|\r/", $raw) ?: [] as $line) {
            $trim = ltrim($line);
            if ($trim === '' || str_starts_with($trim, '#')) {
                continue;
            }
            if (preg_match('/^export\s+/', $trim) === 1) {
                $trim = ltrim((string) preg_replace('/^export\s+/', '', $trim));
            }
            $eq = strpos($trim, '=');
            if ($eq === false) {
                continue;
            }
            $key = trim(substr($trim, 0, $eq));
            if ($key === '' || !in_array($key, self::ALLOWED_KEYS, true)) {
                continue;
            }
            if (self::readRaw($key) !== null) {
                continue; // process env wins
            }
            $val = trim(substr($trim, $eq + 1));
            if ($val !== '' && (
                (str_starts_with($val, '"') && str_ends_with($val, '"'))
                || (str_starts_with($val, "'") && str_ends_with($val, "'"))
            )) {
                $val = substr($val, 1, -1);
            }
            if ($val === '') {
                continue;
            }
            putenv($key . '=' . $val);
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
        }
    }

    /**
     * Drop the cached environment. Used by the test suite and by long-running
     * CLI tools that change the environment after boot.
     */
    public static function flush(): void
    {
        self::$env = null;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $v = self::env()[$key] ?? null;
        return ($v === null || $v === '') ? $default : $v;
    }

    public static function getInt(string $key, int $default): int
    {
        $v = self::get($key);
        if ($v === null || !is_numeric($v)) {
            return $default;
        }
        return (int) $v;
    }

    public static function getBool(string $key, bool $default): bool
    {
        $v = self::get($key);
        if ($v === null) {
            return $default;
        }
        return in_array(strtolower($v), ['1', 'true', 'yes', 'on'], true);
    }

    public static function isProduction(): bool
    {
        return (self::get('APP_ENV', 'production') !== 'development');
    }

    /**
     * One-time runtime bootstrap: timezone, error visibility, PHP ini
     * hardening. Safe to call more than once.
     */
    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }
        self::$booted = true;

        // Keep PHP and MySQL on the same clock. docker-compose runs MySQL at
        // +03:30; a mismatch used to corrupt Jalali dates and lock countdowns.
        $tz = (string) self::get('APP_TIMEZONE', 'Asia/Tehran');
        if (@date_default_timezone_set($tz) === false) {
            date_default_timezone_set('UTC');
        }

        error_reporting(E_ALL);
        ini_set('log_errors', '1');
        if (self::isProduction()) {
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
        } else {
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
        }

        // Defence in depth for SAPIs that do not ship a hardening ini
        // (e.g. `php -S` used for local simulation / CI).
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        // NOTE: session.sid_length / sid_bits_per_character are deprecated as
        // of PHP 8.4; the defaults (26 chars x 5 bits = 130 bits of entropy)
        // are already well beyond what is needed.
        ini_set('expose_php', '0');
    }

    /**
     * Public base URL without trailing slash, e.g. https://example.com
     *
     * SITE_BASE_URL is authoritative. When it is missing we fall back to the
     * Host header, but only if that host is in TRUSTED_HOSTS (or the request
     * is a plain loopback/dev host). This closes host-header injection into
     * canonical, hreflang, Open Graph and JSON-LD URLs.
     */
    public static function baseUrl(): string
    {
        $base = self::get('SITE_BASE_URL');
        if ($base !== null && $base !== '') {
            return rtrim(self::sanitizeBaseUrl($base), '/');
        }

        $host = self::requestHost();
        if (!self::isTrustedHost($host)) {
            // Refuse to echo an attacker-controlled Host into the page.
            return 'http://localhost';
        }
        $scheme = self::isHttps() ? 'https' : 'http';
        return $scheme . '://' . $host;
    }

    private static function sanitizeBaseUrl(string $base): string
    {
        $base = trim($base);
        if (!preg_match('#^(https?)://[A-Za-z0-9._\-]+(?::\d{1,5})?(?:/[^\s]*)?$#i', $base)) {
            return 'http://localhost';
        }
        return rtrim($base, '/');
    }

    public static function requestHost(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (!is_string($host)) {
            return 'localhost';
        }
        $host = strtolower(trim($host));
        // Strip the port for comparison, keep it for the URL.
        $bare = preg_replace('/:\d+$/', '', $host) ?? $host;
        if (!preg_match('/^[a-z0-9._\-]+$/', $bare)) {
            return 'localhost';
        }
        return $host;
    }

    public static function isTrustedHost(string $host): bool
    {
        $bare = strtolower((string) preg_replace('/:\d+$/', '', trim($host)));
        if ($bare === '' || in_array($bare, ['localhost', '127.0.0.1', '::1', '[::1]'], true)) {
            return true;
        }
        $base = self::get('SITE_BASE_URL', '');
        if (is_string($base) && $base !== '') {
            $baseHost = parse_url($base, PHP_URL_HOST);
            if (is_string($baseHost) && strtolower($baseHost) === $bare) {
                return true;
            }
        }
        $list = self::get('TRUSTED_HOSTS', '');
        if ($list === null || $list === '') {
            return false;
        }
        foreach (explode(',', $list) as $allowed) {
            $allowed = strtolower(trim($allowed));
            if ($allowed === '') {
                continue;
            }
            if (str_contains($allowed, '://')) {
                $parsed = parse_url($allowed, PHP_URL_HOST);
                $allowed = is_string($parsed) ? strtolower($parsed) : $allowed;
            }
            $allowed = (string) preg_replace('/:\\d+$/', '', $allowed);
            if ($allowed === $bare) {
                return true;
            }
            // Wildcard subdomain match: *.example.com
            if (str_starts_with($allowed, '*.')
                && str_ends_with($bare, substr($allowed, 1))
                && strlen($bare) > strlen($allowed) - 1) {
                return true;
            }
        }
        return false;
    }

    /** True when the request arrived over TLS (directly or via a proxy). */
    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }
        if ((($_SERVER['SERVER_PORT'] ?? '') === '443')) {
            return true;
        }
        $scheme = $_SERVER['REQUEST_SCHEME'] ?? '';
        if (is_string($scheme) && strtolower($scheme) === 'https') {
            return true;
        }
        $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
        if (is_string($proto) && strtolower(trim(explode(',', $proto)[0])) === 'https') {
            return true;
        }
        $fwdSsl = $_SERVER['HTTP_X_FORWARDED_SSL'] ?? '';
        if (is_string($fwdSsl) && strtolower($fwdSsl) === 'on') {
            return true;
        }
        $cf = $_SERVER['HTTP_CF_VISITOR'] ?? '';
        if (is_string($cf) && str_contains($cf, 'https')) {
            return true;
        }
        return false;
    }

    public static function dbDsn(): string
    {
        return sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            self::get('DB_HOST', '127.0.0.1'),
            self::get('DB_PORT', '3306'),
            self::get('DB_NAME', 'physioelectric')
        );
    }

    // ---- security tunables -------------------------------------------

    public static function sessionIdleSeconds(): int
    {
        return max(60, self::getInt('SESSION_IDLE_MINUTES', 30) * 60);
    }

    public static function sessionAbsoluteSeconds(): int
    {
        return max(300, self::getInt('SESSION_ABSOLUTE_MINUTES', 480) * 60);
    }

    public static function maxUploadBytes(): int
    {
        return max(65_536, self::getInt('MAX_UPLOAD_BYTES', 2 * 1024 * 1024));
    }

    public static function maxImageEdge(): int
    {
        return max(64, self::getInt('MAX_IMAGE_EDGE', 6000));
    }

    public static function maxUploads(): int
    {
        return max(1, self::getInt('MAX_UPLOADS', 2000));
    }
}
