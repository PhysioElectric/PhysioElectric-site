<?php
declare(strict_types=1);

/**
 * Creates / resyncs the admin user on boot (idempotent).
 *
 * Credentials come from environment variables / `.env`:
 *   ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD
 *   ADMIN_PASSWORD_RESET=1  → rewrite the hash even after first login
 *
 * The password is hashed with Argon2id (fallback: bcrypt).
 *
 * Hardening:
 *  - the shipped default password is refused when APP_ENV is production
 *  - the password must pass the SHARED password policy
 *  - a generated (secrets-volume) password still forces rotation at first
 *    login; an operator-supplied ADMIN_PASSWORD does not, so dropping a
 *    `.env` on the host actually lets you into the panel
 */

require dirname(__DIR__) . '/config.php';
Config::boot();
require dirname(__DIR__) . '/core/Database.php';
require dirname(__DIR__) . '/core/Security.php';
require dirname(__DIR__) . '/core/Auth.php';
require dirname(__DIR__) . '/core/PasswordPolicy.php';
require dirname(__DIR__) . '/models/UserModel.php';

$result = UserModel::bootstrapFromEnv();
$email  = $result['email'] !== '' ? $result['email'] : 'admin@physioelectric.com';
$isProd = Config::isProduction();

if (!$result['ok']) {
    $err = (string) ($result['error'] ?? 'unknown error');
    if (str_contains($err, 'empty')) {
        fwrite(STDERR, "[create_admin] ADMIN_PASSWORD is empty; skipping (set it in .env).\n");
        exit($isProd ? 1 : 0);
    }
    if (str_contains($err, 'shipped default')) {
        fwrite(STDERR, "[create_admin] FATAL: ADMIN_PASSWORD is still the shipped default. "
            . "Set a unique password in .env before starting in production.\n");
        exit(1);
    }
    if (str_contains($err, 'not a valid address')) {
        fwrite(STDERR, "[create_admin] ADMIN_EMAIL is not a valid address; refusing.\n");
        exit(1);
    }
    if (str_contains($err, 'rejected')) {
        fwrite(STDERR, "[create_admin] " . $err . "\n");
        fwrite(STDERR, "[create_admin] Generate one with:  openssl rand -base64 24\n");
        exit(1);
    }
    fwrite(STDERR, "[create_admin] " . $err . "\n");
    exit(1);
}

switch ($result['action']) {
    case 'created':
        echo "[create_admin] Admin user created (super_admin): {$email}\n";
        if (Config::getBool('ADMIN_PASSWORD_GENERATED', false)) {
            echo "[create_admin] NOTE: force_password_change is set — the first login will ask for a new password.\n";
        } else {
            echo "[create_admin] Operator-supplied password: forced rotation skipped, panel is reachable.\n";
        }
        break;
    case 'updated':
        echo "[create_admin] Admin password resynced from ADMIN_PASSWORD for {$email}\n";
        break;
    case 'unlocked':
        echo "[create_admin] Admin {$email} unlocked (forced rotation cleared).\n";
        break;
    default:
        echo "[create_admin] Admin user already exists: {$email}\n";
        break;
}
exit(0);
