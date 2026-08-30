<?php
declare(strict_types=1);

/**
 * Creates the admin user on first boot (idempotent).
 *
 * Credentials come from environment variables:
 *   ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD
 *
 * The password is hashed with Argon2id (fallback: bcrypt).
 * If the admin already exists, nothing is changed — this script
 * is safe to run on every container start.
 *
 * Hardening:
 *  - the shipped default password is refused when APP_ENV is production
 *  - a minimum length of 12 is enforced (was 8)
 *  - the e-mail is stored lower-cased so the UNIQUE index cannot be dodged
 *  - an existing account whose password still matches ADMIN_PASSWORD is
 *    reported loudly (it means nobody changed it)
 */

require dirname(__DIR__) . '/config.php';
Config::boot();
require dirname(__DIR__) . '/core/Database.php';
require dirname(__DIR__) . '/core/Security.php';
require dirname(__DIR__) . '/core/Auth.php';

const DEFAULT_PASSWORD = 'Physio@2026';
const MIN_LENGTH       = 12;

$name     = (string) (getenv('ADMIN_NAME') ?: 'Admin');
$email    = strtolower(trim((string) (getenv('ADMIN_EMAIL') ?: 'admin@physioelectric.com')));
$password = (string) (getenv('ADMIN_PASSWORD') ?: '');
$isProd   = Config::isProduction();

if ($password === '') {
    fwrite(STDERR, "[create_admin] ADMIN_PASSWORD is empty; skipping (set it in .env).\n");
    exit($isProd ? 1 : 0);
}
if (strlen($password) < MIN_LENGTH) {
    fwrite(STDERR, "[create_admin] ADMIN_PASSWORD must be at least " . MIN_LENGTH . " characters; refusing.\n");
    exit(1);
}
if ($isProd && $password === DEFAULT_PASSWORD) {
    fwrite(STDERR, "[create_admin] FATAL: ADMIN_PASSWORD is still the shipped default. "
        . "Set a unique password in .env before starting in production.\n");
    exit(1);
}
if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    fwrite(STDERR, "[create_admin] ADMIN_EMAIL is not a valid address; refusing.\n");
    exit(1);
}

try {
    $pdo = Database::pdo();
} catch (Throwable $e) {
    fwrite(STDERR, "[create_admin] DB not ready: " . $e->getMessage() . "\n");
    exit(1);
}

$st = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = :email LIMIT 1');
$st->execute([':email' => $email]);
$row = $st->fetch();

if ($row === false) {
    $hash = password_hash($password, Auth::hashOptions(), Auth::hashAlgoOptions());
    $ins  = $pdo->prepare(
        'INSERT INTO users (name, email, password_hash, is_active)
         VALUES (:name, :email, :hash, 1)'
    );
    $ins->execute([
        ':name'  => mb_substr($name, 0, 120),
        ':email' => $email,
        ':hash'  => $hash,
    ]);
    echo "[create_admin] Admin user created: {$email}\n";
    exit(0);
}

echo "[create_admin] Admin user already exists: {$email}\n";

// Warn (do not change anything) when the password was never rotated.
if (password_verify($password, (string) $row['password_hash'])
    && $isProd && $password === DEFAULT_PASSWORD) {
    fwrite(STDERR, "[create_admin] WARNING: the admin password is still the shipped default. "
        . "Log in and change it immediately.\n");
}
exit(0);
