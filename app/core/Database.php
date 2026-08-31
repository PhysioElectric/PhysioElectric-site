<?php
declare(strict_types=1);

/**
 * PDO singleton. All queries in the project MUST use prepared
 * statements (SQL-injection safe).
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
                PDO::ATTR_PERSISTENT         => false,
            ];
            try {
                self::$pdo = new PDO(
                    Config::dbDsn(),
                    (string) Config::get('DB_USER', 'pe_user'),
                    (string) Config::get('DB_PASS', ''),
                    $options
                );
                // Pin the session clock to the application timezone so that
                // NOW() and PHP time() agree. The mismatch (MySQL at +03:30,
                // PHP at UTC) used to corrupt Jalali dates and the brute-force
                // lock countdown.
                self::$pdo->exec("SET time_zone = " . self::$pdo->quote(date('P')));
            } catch (PDOException $e) {
                // Never leak DB details (DSN, host, driver message) to the user.
                error_log('[DB] Connection failed: ' . $e->getMessage());
                if (class_exists('Security')) {
                    Security::audit('db.connect_failed');
                }
                self::fail();
            }
        }
        return self::$pdo;
    }

    /**
     * Uniform, information-free database failure response.
     * @return never
     */
    public static function fail(): void
    {
        if (!headers_sent()) {
            http_response_code(503);
            header('Content-Type: text/html; charset=utf-8');
            header('Cache-Control: no-store');
            if (class_exists('Security')) {
                header('X-Request-Id: ' . Security::requestId());
            }
        }
        exit('<!doctype html><html lang="en"><head><meta charset="utf-8">'
            . '<title>503 Service Unavailable</title></head>'
            . '<body style="font-family:sans-serif;padding:3rem;background:#f8fafc;color:#0f172a">'
            . '<h1>503</h1><p>The site is temporarily unavailable. Please try again shortly.</p>'
            . '</body></html>');
    }

    /**
     * True when the application tables exist. Used by the login limiter so a
     * missing/broken schema fails *closed* instead of disabling brute-force
     * protection.
     */
    public static function schemaReady(): bool
    {
        try {
            self::pdo()->query('SELECT 1 FROM login_attempts LIMIT 1');
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /** @var array<string,bool> per-request cache */
    private static array $tableCache = [];

    /**
     * Per-request-cached table-existence check. Lets pages degrade gracefully
     * (empty lists, zero counters) instead of throwing a hard 500 when the
     * schema has not been migrated yet (e.g. an old database / Docker volume).
     */
    public static function tableExists(string $table): bool
    {
        if (preg_match('/^[A-Za-z0-9_]+$/', $table) !== 1) {
            return false;
        }
        if (array_key_exists($table, self::$tableCache)) {
            return self::$tableCache[$table];
        }
        try {
            $st = self::pdo()->prepare(
                'SELECT 1 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t LIMIT 1'
            );
            $st->execute([':t' => $table]);
            $exists = $st->fetchColumn() !== false;
        } catch (Throwable) {
            $exists = false;
        }
        return self::$tableCache[$table] = $exists;
    }

    private function __construct()
    {
    }
}
