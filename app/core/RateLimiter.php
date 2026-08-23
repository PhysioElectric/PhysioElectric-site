<?php
declare(strict_types=1);

/**
 * Brute-force protection for the admin login.
 * State is persisted in MySQL (login_attempts) so it survives
 * container restarts and works behind load balancers.
 *
 * Policy: MAX_ATTEMPTS failed logins per IP within WINDOW minutes
 * lock the IP out completely.
 */
final class RateLimiter
{
    public const MAX_ATTEMPTS   = 5;
    public const WINDOW_MINUTES = 15;
    public const PURGE_HOURS    = 24;

    public static function clientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return is_string($ip) ? trim($ip) : '0.0.0.0';
    }

    /** Number of failed attempts for this IP inside the window. */
    private static function failures(?string $ip = null): int
    {
        $ip ??= self::clientIp();
        $sql = 'SELECT COUNT(*) FROM login_attempts
                WHERE ip = INET6_ATON(:ip)
                  AND success = 0
                  AND attempted_at >= (NOW() - INTERVAL ' . (int) self::WINDOW_MINUTES . ' MINUTE)';
        $st  = Database::pdo()->prepare($sql);
        $st->execute([':ip' => $ip]);
        return (int) $st->fetchColumn();
    }

    public static function isLocked(?string $ip = null): bool
    {
        try {
            return self::failures($ip) >= self::MAX_ATTEMPTS;
        } catch (Throwable) {
            return false; // fail open if the table is missing, never lock the site
        }
    }

    public static function recordFailure(?string $ip = null): void
    {
        $ip ??= self::clientIp();
        $st = Database::pdo()->prepare(
            'INSERT INTO login_attempts (ip, success) VALUES (INET6_ATON(:ip), 0)'
        );
        $st->execute([':ip' => $ip]);
        self::cleanup();
    }

    public static function recordSuccess(?string $ip = null): void
    {
        $ip ??= self::clientIp();
        try {
            $st = Database::pdo()->prepare(
                'INSERT INTO login_attempts (ip, success) VALUES (INET6_ATON(:ip), 1)'
            );
            $st->execute([':ip' => $ip]);
            // A successful login clears the failing history for this IP.
            $st2 = Database::pdo()->prepare(
                'DELETE FROM login_attempts WHERE ip = INET6_ATON(:ip) AND success = 0'
            );
            $st2->execute([':ip' => $ip]);
        } catch (Throwable) {
            // Non-fatal.
        }
    }

    /** Seconds until the lock expires (0 when not locked). */
    public static function lockSecondsLeft(?string $ip = null): int
    {
        if (!self::isLocked($ip)) {
            return 0;
        }
        $sql = 'SELECT MIN(attempted_at) FROM login_attempts
                WHERE ip = INET6_ATON(:ip) AND success = 0
                  AND attempted_at >= (NOW() - INTERVAL ' . (int) self::WINDOW_MINUTES . ' MINUTE)';
        $st = Database::pdo()->prepare($sql);
        $st->execute([':ip' => $ip]);
        $oldest = $st->fetchColumn();
        if ($oldest === false || $oldest === null) {
            return self::WINDOW_MINUTES * 60;
        }
        $unlockAt = strtotime($oldest . ' +0000 + ' . (int) self::WINDOW_MINUTES . ' minutes');
        $left     = (int) $unlockAt - time();
        return $left > 0 ? $left : 1;
    }

    private static function cleanup(): void
    {
        $st = Database::pdo()->prepare(
            'DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL ' . (int) self::PURGE_HOURS . ' HOUR)'
        );
        $st->execute();
    }
}
