<?php
declare(strict_types=1);

/**
 * One-command setup + smoke test for the received-messages feature.
 *
 * Run on the SERVER (CLI) to both fix and verify in a single step:
 *
 *     cd app && php setup/selftest.php
 *
 * It:
 *   1. connects to the configured database,
 *   2. self-heals the schema (creates/seed team_members + messages if missing),
 *      printing the exact MySQL error if the DB user lacks privileges,
 *   3. stores a sample inquiry through the SAME code path the form uses,
 *   4. confirms it is readable for the admin inbox, then removes it.
 *
 * Exit code 0 = all good; 1 = a problem was detected (see the printed reason).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('not found');
}

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/config.php';
Config::boot();
require BASE_PATH . '/core/Database.php';
require BASE_PATH . '/core/Schema.php';
require BASE_PATH . '/models/MessageModel.php';

function out(string $s): void
{
    fwrite(STDOUT, $s . "\n");
}

out('== PhysioElectric received-messages self test ==');

// 1) connection
try {
    $pdo = Database::pdo();
    out('[OK] database connected');
} catch (Throwable $e) {
    out('[FAIL] cannot connect to database: ' . $e->getMessage());
    out('       Check DB_HOST/DB_NAME/DB_USER/DB_PASS (or .env / docker secrets).');
    exit(1);
}

// 2) schema self-heal
try {
    Schema::ensureFeatureTables();
    if (Database::tableExists('messages') && Database::tableExists('team_members')) {
        out('[OK] feature tables present (created/seeded automatically if they were missing)');
    } else {
        out('[FAIL] feature tables are still missing after self-heal.');
        out('       The DB user likely lacks CREATE privilege. Run setup/migrate.php with a');
        out('       privileged user, or grant: GRANT CREATE, INSERT, SELECT, UPDATE, DELETE ON <db>.* TO <user>;');
        exit(1);
    }
} catch (Throwable $e) {
    out('[FAIL] schema self-heal error: ' . $e->getMessage());
    exit(1);
}

// 3) store a sample inquiry via the real model path
try {
    $id = MessageModel::create([
        'kind'           => 'contact',
        'category'       => 'self-test',
        'name'           => 'پیام تست',
        'company'        => '',
        'email'          => 'selftest@physioelectric.local',
        'phone'          => '',
        'contact_method' => '',
        'contact_id'     => '',
        'timeline'       => '',
        'body'           => 'این یک پیام تست خودکار برای بررسی صندوق دریافتی است.',
        'notes'          => '',
        'lang'           => 'fa',
        'attachments'    => '',
        'ip'             => '127.0.0.1',
    ]);
    out("[OK] sample inquiry stored (id {$id})");
} catch (Throwable $e) {
    out('[FAIL] could not store sample inquiry: ' . $e->getMessage());
    exit(1);
}

// 4) confirm it is readable for the inbox, then clean up
try {
    $row = MessageModel::byId($id);
    if ($row === null || (string) $row['email'] !== 'selftest@physioelectric.local') {
        out('[FAIL] stored message is not readable back.');
        exit(1);
    }
    out('[OK] message is readable for the admin inbox');
    MessageModel::delete($id);
    out("[OK] sample message cleaned up (id {$id} removed)");
} catch (Throwable $e) {
    out('[FAIL] read/cleanup error: ' . $e->getMessage());
    exit(1);
}

out('== ALL GOOD: the wizard can store requests and the admin inbox can read them. ==');
out('   If the browser still shows an error, hard-refresh (Ctrl+F5) so the latest');
out('   contact.js is loaded, then submit the form again.');
exit(0);
