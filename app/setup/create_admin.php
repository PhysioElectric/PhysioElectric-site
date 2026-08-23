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
 */

require dirname(__DIR__) . '/config.php';
require dirname(__DIR__) . '/core/Database.php';

$name    = getenv('ADMIN_NAME') ?: 'Admin';
$email   = getenv('ADMIN_EMAIL') ?: 'admin@physioelectric.com';
$password = getenv('ADMIN_PASSWORD') ?: '';

if ($password === '') {
    fwrite(STDERR, "[create_admin] ADMIN_PASSWORD is empty; skipping (set it in .env).\n");
    exit(0);
}
if (strlen($password) < 8) {
    fwrite(STDERR, "[create_admin] ADMIN_PASSWORD must be at least 8 characters; skipping.\n");
    exit(0);
}

try {
    $pdo = Database::pdo();
} catch (Throwable $e) {
    fwrite(STDERR, "[create_admin] DB not ready: " . $e->getMessage() . "\n");
    exit(1);
}

$st = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$st->execute([':email' => $email]);

if ($st->fetch() === false) {
    $hash = password_hash($password, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT);
    $ins  = $pdo->prepare(
        'INSERT INTO users (name, email, password_hash, is_active)
         VALUES (:name, :email, :hash, 1)'
    );
    $ins->execute([':name' => $name, ':email' => $email, ':hash' => $hash]);
    echo "[create_admin] Admin user created: {$email}\n";
} else {
    echo "[create_admin] Admin user already exists: {$email}\n";
}
