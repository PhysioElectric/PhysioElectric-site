<?php
declare(strict_types=1);

/**
 * Idempotent schema migrations for databases created from an older init.sql.
 *
 * Safe to run on every boot: every step inspects information_schema first and
 * only issues DDL when the change is actually missing. Works on MySQL 8 and
 * MariaDB (no `ADD COLUMN IF NOT EXISTS`, which MySQL does not support).
 */

require dirname(__DIR__) . '/config.php';
Config::boot();
require dirname(__DIR__) . '/core/Database.php';

$dbName = (string) Config::get('DB_NAME', 'physioelectric');

try {
    $pdo = Database::pdo();
} catch (Throwable $e) {
    fwrite(STDERR, "[migrate] DB not reachable: " . $e->getMessage() . "\n");
    exit(1);
}

/** True when information_schema says the column exists. */
$hasColumn = static function (string $table, string $column) use ($pdo, $dbName): bool {
    $st = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :t AND COLUMN_NAME = :c'
    );
    $st->execute([':db' => $dbName, ':t' => $table, ':c' => $column]);
    return (int) $st->fetchColumn() > 0;
};

/** True when information_schema says the index exists. */
$hasIndex = static function (string $table, string $index) use ($pdo, $dbName): bool {
    $st = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :t AND INDEX_NAME = :i'
    );
    $st->execute([':db' => $dbName, ':t' => $table, ':i' => $index]);
    return (int) $st->fetchColumn() > 0;
};

/** True when information_schema says the table exists. */
$hasTable = static function (string $table) use ($pdo, $dbName): bool {
    $st = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :t'
    );
    $st->execute([':db' => $dbName, ':t' => $table]);
    return (int) $st->fetchColumn() > 0;
};

$steps = [
    [
        'name' => 'login_attempts.identifier (per-account throttling)',
        'test' => static fn() => $hasColumn('login_attempts', 'identifier'),
        'sql'  => 'ALTER TABLE `login_attempts`
                   ADD COLUMN `identifier` VARCHAR(190) NULL DEFAULT NULL AFTER `ip`',
    ],
    [
        'name' => 'index idx_attempts_ident_time',
        'test' => static fn() => $hasIndex('login_attempts', 'idx_attempts_ident_time'),
        'sql'  => 'ALTER TABLE `login_attempts`
                   ADD KEY `idx_attempts_ident_time` (`identifier`, `attempted_at`)',
    ],
    [
        'name' => 'index idx_attempts_time (purge job)',
        'test' => static fn() => $hasIndex('login_attempts', 'idx_attempts_time'),
        'sql'  => 'ALTER TABLE `login_attempts` ADD KEY `idx_attempts_time` (`attempted_at`)',
    ],
    [
        'name' => 'table team_members (admin-managed About page team)',
        'test' => static fn() => $hasTable('team_members'),
        'sql'  => 'CREATE TABLE IF NOT EXISTS `team_members` (
                     `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                     `name_fa` VARCHAR(120) NOT NULL DEFAULT \'\',
                     `name_en` VARCHAR(120) NOT NULL DEFAULT \'\',
                     `role_fa` VARCHAR(160) NOT NULL DEFAULT \'\',
                     `role_en` VARCHAR(160) NOT NULL DEFAULT \'\',
                     `desc_fa` VARCHAR(600) NOT NULL DEFAULT \'\',
                     `desc_en` VARCHAR(600) NOT NULL DEFAULT \'\',
                     `image` VARCHAR(255) NOT NULL DEFAULT \'\',
                     `sort_order` INT NOT NULL DEFAULT 0,
                     `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                     PRIMARY KEY (`id`),
                     KEY `idx_team_sort` (`sort_order`, `id`)
                   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    ],
    [
        'name' => 'table messages (public inquiries inbox)',
        'test' => static fn() => $hasTable('messages'),
        'sql'  => 'CREATE TABLE IF NOT EXISTS `messages` (
                     `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                     `kind` VARCHAR(20) NOT NULL DEFAULT \'contact\',
                     `category` VARCHAR(190) NOT NULL DEFAULT \'\',
                     `name` VARCHAR(120) NOT NULL,
                     `company` VARCHAR(160) NOT NULL DEFAULT \'\',
                     `email` VARCHAR(190) NOT NULL,
                     `phone` VARCHAR(40) NOT NULL DEFAULT \'\',
                     `contact_method` VARCHAR(20) NOT NULL DEFAULT \'\',
                     `contact_id` VARCHAR(120) NOT NULL DEFAULT \'\',
                     `timeline` VARCHAR(60) NOT NULL DEFAULT \'\',
                     `body` TEXT NOT NULL,
                     `notes` VARCHAR(500) NOT NULL DEFAULT \'\',
                     `lang` VARCHAR(5) NOT NULL DEFAULT \'fa\',
                     `attachments` TEXT NULL,
                     `is_read` TINYINT(1) NOT NULL DEFAULT 0,
                     `ip` VARCHAR(45) NOT NULL DEFAULT \'\',
                     `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                     PRIMARY KEY (`id`),
                     KEY `idx_msg_read` (`is_read`, `created_at`),
                     KEY `idx_msg_ip_time` (`ip`, `created_at`)
                   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    ],
];

$applied = 0;
foreach ($steps as $step) {
    if (($step['test'])()) {
        continue;
    }
    try {
        $pdo->exec($step['sql']);
        $applied++;
        echo "[migrate] applied: {$step['name']}\n";
    } catch (Throwable $e) {
        fwrite(STDERR, "[migrate] FAILED: {$step['name']} -> " . $e->getMessage() . "\n");
        exit(1);
    }
}

// Seed the team with the members that used to be hardcoded in about.php, so
// an upgraded database keeps showing the same team until edited in the panel.
$teamCount = (int) $pdo->query('SELECT COUNT(*) FROM team_members')->fetchColumn();
if ($teamCount === 0) {
    $seed = [
        ['دکتر امیر حسینی', 'Dr. Amir Hosseini', 'مهندس ارشد / سیستم‌های AI', 'Lead Engineer / AI Systems',
         'طراحی معماری سیستم‌های هوشمند و نظارت بر مدل‌های پیچیده محاسباتی.', 'Architecting intelligent systems and overseeing complex computational models.',
         'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=800&auto=format&fit=crop', 1],
        ['سارا رادمنش', 'Sara Radmanesh', 'آرشیتکت نرم‌افزار', 'Software Architect',
         'طراحی زیرساخت‌های مقیاس‌پذیر وب و پر کردن شکاف بین ریاضیات و کد.', 'Designing scalable web infrastructure and bridging the gap between math and code.',
         'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=800&auto=format&fit=crop', 2],
        ['محمدرضا افراز', 'Mohammad Reza Afraz', 'شبیه‌سازی و تحلیل', 'Simulation & Analysis',
         'تبدیل پدیده‌های فیزیکی دنیای واقعی به مدل‌های کامسول با دقت بالا.', 'Translating real-world physical phenomena into highly accurate COMSOL models.',
         'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=800&auto=format&fit=crop', 3],
        ['ندا وحدتی', 'Neda Vahdati', 'سیستم‌های نهفته', 'Embedded Systems',
         'توسعه سخت‌افزارهای IoT و بهینه‌سازی میکروکنترلرها برای محاسبات لبه.', 'Developing IoT hardware and optimizing microcontrollers for edge computing.',
         'https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=800&auto=format&fit=crop', 4],
    ];
    $st = $pdo->prepare(
        'INSERT INTO team_members
            (name_fa, name_en, role_fa, role_en, desc_fa, desc_en, image, sort_order)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($seed as $row) {
        $st->execute($row);
    }
    echo "[migrate] seeded team_members (4)\n";
}

echo $applied === 0 ? "[migrate] schema up to date.\n" : "[migrate] {$applied} change(s) applied.\n";
exit(0);
