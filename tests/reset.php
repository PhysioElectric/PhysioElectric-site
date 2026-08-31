<?php
declare(strict_types=1);

/**
 * Test-data reset. Removes the rows the e2e suite creates and clears the
 * brute-force counters so the suite can be re-run back to back.
 *
 * Never touches the seeded content (ids 1..6 projects / 1..4 posts).
 *
 * Run: php tests/reset.php
 */
require __DIR__ . '/bootstrap.php';

// Guard: this deletes rows, so refuse to run unless the operator asked for it.
if (!in_array('--yes', $argv, true)) {
    fwrite(STDERR, "usage: php tests/reset.php --yes\n");
    exit(2);
}

$pdo = Database::pdo();

$pdo->exec("DELETE FROM login_attempts");
$pdo->exec("DELETE FROM posts    WHERE slug_en LIKE 'e2e-test-%' OR slug_en LIKE 'long-%'");
$pdo->exec("DELETE FROM projects WHERE slug_en LIKE 'e2e-test-%'");

// Settings are upserted back to their seeded values. The seed is parsed out
// of db/init.sql so there is a single source of truth and the two can never
// drift apart. Without this the suite is not idempotent: a run that writes a
// setting would leave it written for the next run.
$initSql = (string) @file_get_contents(dirname(BASE_PATH) . '/db/init.sql');
$settingsRestored = 0;
if ($initSql !== '' && preg_match(
    '/INSERT\s+INTO\s+`settings`\s*\([^)]*\)\s*VALUES(.*?);/is',
    $initSql,
    $block
)) {
    $upsert = $pdo->prepare(
        'INSERT INTO settings (skey, svalue) VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)'
    );
    $unescape = static fn(string $s): string => str_replace(["\\'", "''", '\\"'], ["'", "'", '"'], $s);
    if (preg_match_all("/\(\s*'((?:[^']|'')*)'\s*,\s*'((?:[^']|'')*)'\s*\)/s", $block[1], $pairs, PREG_SET_ORDER)) {
        foreach ($pairs as $pair) {
            $upsert->execute([':k' => $unescape($pair[1]), ':v' => $unescape($pair[2])]);
            $settingsRestored++;
        }
    }
}

// Uploads created by the suite. Opt-in (--uploads) and restricted to files
// whose embedded timestamp is inside the last 60 minutes, so pre-existing
// content in the repo can never be touched.
$dir = BASE_PATH . '/uploads';
$used = [];
foreach ($pdo->query('SELECT image FROM posts WHERE image IS NOT NULL
                      UNION ALL SELECT image FROM projects WHERE image IS NOT NULL') as $row) {
    $used[(string) $row['image']] = true;
}
$removed = 0;
$pruneUploads = in_array('--uploads', $argv, true);
if ($pruneUploads && is_dir($dir)) {
    $cutoff = time() - 3600;
    foreach (scandir($dir) ?: [] as $f) {
        if (!is_string($f)
            || !preg_match('/^(\d{8})-(\d{6})-[0-9a-f]{16}\.(jpg|png|webp)$/', $f, $m)) {
            continue;
        }
        $stamp = strtotime($m[1] . substr($m[2], 0, 2) . substr($m[2], 2, 2));
        if ($stamp === false || $stamp < $cutoff) {
            continue; // not something this suite just created
        }
        if (isset($used['/uploads/' . $f])) {
            continue;
        }
        if (@unlink($dir . '/' . $f)) {
            $removed++;
        }
    }
}

printf(
    "[reset] posts=%d projects=%d settings=%d (restored=%d) uploads_removed=%d\n",
    (int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn(),
    (int) $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn(),
    (int) $pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn(),
    $settingsRestored,
    $removed
);
exit(0);
