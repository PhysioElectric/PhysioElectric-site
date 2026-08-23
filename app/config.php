<?php
declare(strict_types=1);

/**
 * Central configuration. All values come from environment variables
 * (see docker-compose.yml / .env.example) with safe defaults.
 */
final class Config
{
    /** @var array<string,string>|null */
    private static ?array $env = null;

    public static function env(): array
    {
        if (self::$env === null) {
            $env = [];
            foreach (['APP_ENV', 'SITE_BASE_URL', 'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS',
                       'ADMIN_NAME', 'ADMIN_EMAIL', 'ADMIN_PASSWORD'] as $key) {
                $val = getenv($key);
                if ($val !== false && $val !== '') {
                    $env[$key] = $val;
                }
            }
            self::$env = $env;
        }
        return self::$env;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return self::env()[$key] ?? $default;
    }

    public static function isProduction(): bool
    {
        return (self::get('APP_ENV', 'production') !== 'development');
    }

    /** Public base URL without trailing slash, e.g. https://example.com */
    public static function baseUrl(): string
    {
        $base = self::get('SITE_BASE_URL');
        if ($base === null || $base === '') {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $base   = $scheme . '://' . $host;
        }
        return rtrim($base, '/');
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
}
