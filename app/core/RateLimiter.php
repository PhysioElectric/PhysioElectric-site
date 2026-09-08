<?php
declare(strict_types=1);

/**
 * Brute-force protection for the admin login.
 *
 * State lives in MySQL (login_attempts) so it survives container restarts and
 * is shared across app instances behind a load balancer.
 *
 * Policy: MAX_ATTEMPTS failed logins for the same IP **or** the same account
 * inside WINDOW_MINUTES locks both out for the remainder of the window.
 *
 * Fixes over the previous implementation:
 *  - fails *closed*: if the limiter's table is unusable the login is refused
 *    instead of silently allowing unlimited attempts
 *  - the remaining-lock countdown is computed by MySQL (TIMESTAMPDIFF against
 *    NOW()), so a PHP/MySQL timezone difference can no longer inflate it
 *  - the client IP is validated before it reaches INET6_ATON()
 *  - throttling is per-account as well as per-IP, so an attacker rotating
 *    addresses cannot keep hammering one mailbox
 */
final class RateLimiter
{
    public const MAX_ATTEMPTS   = 5;
    public const WINDOW_MINUTES = 15;
    public const PURGE_HOURS    = 24;

    private static ?bool $usable = null;

    public static function maxAttempts(): int
    {
        return max(1, Config::getInt('LOGIN_MAX_ATTEMPTS', self::MAX_ATTEMPTS));
    }

    public static function windowMinutes(): int
    {
        return max(1, Config::getInt('LOGIN_WINDOW_MINUTES', self::WINDOW_MINUTES));
    }

    public static function clientIp(): string
    {
        return Security::clientIp();
    }

    /**
     * Rate-limit bucket IP. An unusable REMOTE_ADDR used to fail closed for
     * *every* login (the whole panel locked) on hosts that hide the client
     * address. Unknown sources share the 0.0.0.0 bucket instead.
     */
    private static function effectiveIp(?string $ip): string
    {
        $ip ??= self::clientIp();
        return Security::isValidIp($ip) ? $ip : '0.0.0.0';
    }

    /** True when the backing table can be read/written. Cached per request. */
    public static function usable(): bool
    {
        if (self::$usable === null) {
            try {
                Database::pdo()->query('SELECT 1 FROM login_attempts LIMIT 1');
                self::$usable = true;
            } catch (Throwable $e) {
                self::$usable = false;
                error_log('[RateLimiter] login_attempts unavailable: ' . $e->getMessage());
                Security::audit('ratelimit.unavailable');
            }
        }
        return self::$usable;
    }

    /**
     * Fail closed: no working limiter means no login.
     */
    public static function isLocked(?string $ip = null, ?string $identifier = null): bool
    {
        if (!self::usable()) {
            return true;
        }
        return self::failures($ip, $identifier) >= self::maxAttempts();
    }

    /**
     * Failed attempts for this IP *or* this account inside the window.
     */
    private static function failures(?string $ip = null, ?string $identifier = null): int
    {
        $ip = self::effectiveIp($ip);

        // INTERVAL count is interpolated (int-cast) so native prepares on
        // MariaDB/MySQL never reject `:window` as an identifier. A failed
        // query here used to fail closed and lock the whole admin panel.
        $window = self::windowMinutes();
        $sql = 'SELECT COUNT(*) FROM login_attempts
                WHERE success = 0
                  AND attempted_at >= (NOW() - INTERVAL ' . $window . ' MINUTE)
                  AND (ip = INET6_ATON(:ip) OR identifier = :ident)';
        try {
            $st = Database::pdo()->prepare($sql);
            $st->bindValue(':ip', $ip, PDO::PARAM_STR);
            $st->bindValue(':ident', self::normalizeIdentifier($identifier), PDO::PARAM_STR);
            $st->execute();
            return (int) $st->fetchColumn();
        } catch (Throwable $e) {
            // Fail closed: an unusable limiter must not mean unlimited tries.
            error_log('[RateLimiter] failures(): ' . $e->getMessage());
            Security::audit('ratelimit.query_failed');
            return self::maxAttempts();
        }
    }

    /** Seconds until the lock expires (0 when not locked). */
    public static function lockSecondsLeft(?string $ip = null, ?string $identifier = null): int
    {
        if (!self::usable() || !self::isLocked($ip, $identifier)) {
            return self::usable() ? 0 : self::windowMinutes() * 60;
        }
        $ip = self::effectiveIp($ip);

        // Oldest attempt still inside the window, measured by the database
        // clock — immune to PHP/MySQL timezone drift.
        $window = self::windowMinutes();
        $sql = 'SELECT TIMESTAMPDIFF(SECOND, MIN(attempted_at), NOW()) AS age
                FROM login_attempts
                WHERE success = 0
                  AND attempted_at >= (NOW() - INTERVAL ' . $window . ' MINUTE)
                  AND (ip = INET6_ATON(:ip) OR identifier = :ident)';
        try {
            $st = Database::pdo()->prepare($sql);
            $st->bindValue(':ip', $ip, PDO::PARAM_STR);
            $st->bindValue(':ident', self::normalizeIdentifier($identifier), PDO::PARAM_STR);
            $st->execute();
            $row = $st->fetch();
        } catch (Throwable $e) {
            error_log('[RateLimiter] lockSecondsLeft(): ' . $e->getMessage());
            return self::windowMinutes() * 60;
        }
        if (!is_array($row) || $row['age'] === null) {
            return self::windowMinutes() * 60;
        }
        $left = (self::windowMinutes() * 60) - (int) $row['age'];
        return $left > 0 ? $left : 1;
    }

    public static function recordFailure(?string $ip = null, ?string $identifier = null): void
    {
        if (!self::usable()) {
            return;
        }
        $ip = self::effectiveIp($ip);
        try {
            $st = Database::pdo()->prepare(
                'INSERT INTO login_attempts (ip, identifier, success)
                 VALUES (INET6_ATON(:ip), :ident, 0)'
            );
            $st->execute([
                ':ip'    => $ip,
                ':ident' => self::normalizeIdentifier($identifier),
            ]);
        } catch (Throwable $e) {
            error_log('[RateLimiter] recordFailure: ' . $e->getMessage());
        }
        self::cleanup();
    }

    public static function recordSuccess(?string $ip = null, ?string $identifier = null): void
    {
        if (!self::usable()) {
            return;
        }
        $ip = self::effectiveIp($ip);
        try {
            $pdo = Database::pdo();
            $st = $pdo->prepare(
                'INSERT INTO login_attempts (ip, identifier, success)
                 VALUES (INET6_ATON(:ip), :ident, 1)'
            );
            $st->execute([
                ':ip'    => $ip,
                ':ident' => self::normalizeIdentifier($identifier),
            ]);
            // A successful login clears the failing history for this IP.
            $st2 = $pdo->prepare(
                'DELETE FROM login_attempts
                 WHERE ip = INET6_ATON(:ip) AND success = 0'
            );
            $st2->execute([':ip' => $ip]);
        } catch (Throwable $e) {
            error_log('[RateLimiter] recordSuccess: ' . $e->getMessage());
        }
    }

    /** Keep the table small. Cheap: the (attempted_at) index covers it. */
    private static function cleanup(): void
    {
        try {
            $st = Database::pdo()->prepare(
                'DELETE FROM login_attempts
                 WHERE attempted_at < (NOW() - INTERVAL ' . (int) self::PURGE_HOURS . ' HOUR)'
            );
            $st->execute();
        } catch (Throwable) {
            // Non-fatal.
        }
    }

    /** Lower-cased, length-capped account key (e-mail). */
    private static function normalizeIdentifier(?string $identifier): ?string
    {
        if ($identifier === null) {
            return null;
        }
        $identifier = strtolower(trim($identifier));
        if ($identifier === '') {
            return null;
        }
        return substr($identifier, 0, 190);
    }
}
