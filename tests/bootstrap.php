<?php
declare(strict_types=1);

/**
 * Minimal bootstrap for running the backend unit tests outside a web request
 * (CLI). No session, no headers, no output.
 */

define('BASE_PATH', dirname(__DIR__) . '/app');

require BASE_PATH . '/config.php';

putenv('APP_ENV=' . (getenv('APP_ENV') ?: 'development'));
putenv('DB_HOST=' . (getenv('DB_HOST') ?: '127.0.0.1'));
putenv('DB_PORT=' . (getenv('DB_PORT') ?: '3306'));
putenv('DB_NAME=' . (getenv('DB_NAME') ?: 'physioelectric'));
putenv('DB_USER=' . (getenv('DB_USER') ?: 'pe_user'));
putenv('DB_PASS=' . (getenv('DB_PASS') ?: 'pe_secret_2026'));

Config::boot();

require BASE_PATH . '/core/Security.php';
require BASE_PATH . '/core/Database.php';
require BASE_PATH . '/core/Csrf.php';
require BASE_PATH . '/core/RateLimiter.php';
require BASE_PATH . '/core/Auth.php';
require BASE_PATH . '/core/HtmlSanitizer.php';
require BASE_PATH . '/core/functions.php';
require BASE_PATH . '/models/CategoryModel.php';
require BASE_PATH . '/models/ProjectModel.php';
require BASE_PATH . '/models/PostModel.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

/** Tiny assertion helper shared by the test files. */
final class T
{
    public static int $pass = 0;
    /** @var string[] */
    public static array $fail = [];
    private static string $group = '';

    public static function group(string $name): void
    {
        self::$group = $name;
        echo "\n== {$name} ==\n";
    }

    public static function ok(bool $cond, string $what, string $detail = ''): void
    {
        if ($cond) {
            self::$pass++;
            echo "  \033[32mPASS\033[0m {$what}\n";
            return;
        }
        self::$fail[] = self::$group . ' :: ' . $what . ($detail !== '' ? ' -> ' . $detail : '');
        echo "  \033[31mFAIL\033[0m {$what}" . ($detail !== '' ? "  ({$detail})" : '') . "\n";
    }

    public static function same(mixed $expected, mixed $actual, string $what): void
    {
        self::ok(
            $expected === $actual,
            $what,
            $expected === $actual ? '' : 'expected ' . var_export($expected, true)
                . ', got ' . var_export($actual, true)
        );
    }

    public static function summary(): int
    {
        $failed = count(self::$fail);
        $total  = self::$pass + $failed;
        echo "\n---------------------------------------------\n";
        echo 'passed ' . ($total - $failed) . '/' . $total . "\n";
        if (self::$fail !== []) {
            echo "\nFAILURES:\n";
            foreach (self::$fail as $f) {
                echo " - {$f}\n";
            }
            return 1;
        }
        echo "ALL GREEN\n";
        return 0;
    }
}
