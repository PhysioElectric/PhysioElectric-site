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
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ];
            try {
                self::$pdo = new PDO(
                    Config::dbDsn(),
                    Config::get('DB_USER', 'pe_user'),
                    Config::get('DB_PASS', ''),
                    $options
                );
            } catch (PDOException $e) {
                // Never leak DB details to the user.
                error_log('[DB] Connection failed: ' . $e->getMessage());
                http_response_code(500);
                exit('500 - Database connection error. Please try again later.');
            }
        }
        return self::$pdo;
    }

    private function __construct()
    {
    }
}
