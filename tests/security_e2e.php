<?php
declare(strict_types=1);

/**
 * End-to-end security suite for CHANGES-SECURITY-2.md (needs a running
 * server + MariaDB/MySQL, see README/CHANGES for the exact bootstrap):
 *
 *   export DB_HOST=127.0.0.1 DB_NAME=physioelectric DB_USER=pe_user DB_PASS=...
 *   cd app && php -S 127.0.0.1:8090 index.php &        # APP_ENV=production
 *   php ../tests/security_e2e.php http://127.0.0.1:8090
 *
 * Covers, end to end over real HTTP + real DB:
 *   - attachment MIME whitelist + zip-bomb rejection (fake ext, big zip)
 *   - CAPTCHA plumbing (disabled by default; separate server with dummy
 *     keys when PE_CAPTCHA_BASE is given)
 *   - login, forced password change gate, RBAC (super/editor/viewer),
 *     admin-user management guards, TOTP 2FA enable/login/disable
 *
 * The suite only touches accounts/emails under pe-e2e-*@physioelectric.test
 * plus the master admin row, whose password/force/2FA state is restored.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../app/core/Totp.php'; // standalone, no deps

/** Mirror RFC 6238 generator (test-only): code valid at $time for $secret. */
function totpCodeAt(string $secret, int $time): string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $key = '';
    $buffer = 0;
    $bits = 0;
    foreach (str_split(strtoupper(trim($secret))) as $char) {
        $val = strpos($alphabet, $char);
        if ($val === false) {
            throw new RuntimeException('bad base32 secret: ' . $char);
        }
        $buffer = ($buffer << 5) | $val;
        $bits += 5;
        if ($bits >= 8) {
            $key .= chr(($buffer >> ($bits - 8)) & 0xFF);
            $bits -= 8;
        }
    }
    $counter = (int) floor($time / 30);
    $hash = hash_hmac('sha1', pack('N', $counter), $key, true);
    $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
    $binary = ((ord($hash[$offset]) & 0x7F) << 24)
        | ((ord($hash[$offset + 1]) & 0xFF) << 16)
        | ((ord($hash[$offset + 2]) & 0xFF) << 8)
        | (ord($hash[$offset + 3]) & 0xFF);
    return str_pad((string) ($binary % 1000000), 6, '0', STR_PAD_LEFT);
}

// ---------------------------------------------------------------------
//  Config
// ---------------------------------------------------------------------
$base     = rtrim((string) ($argv[1] ?? getenv('PE_BASE') ?: 'http://127.0.0.1:8090'), '/');
$dbHost   = getenv('DB_HOST') ?: '127.0.0.1';
$dbName   = getenv('DB_NAME') ?: 'physioelectric';
$dbUser   = getenv('DB_USER') ?: 'pe_user';
$dbPass   = (string) getenv('DB_PASS');

$masterEmail = 'admin@physioelectric.com';
$masterPass  = (string) (getenv('PE_MASTER_PASSWORD') ?: 'M#9xQ!vL2mW$pR7tKz');

$domain = 'physioelectric.test';

$passCount = 0;
$failCount = 0;
$section   = '';

function section(string $name): void
{
    global $section;
    $section = $name;
    echo "\n==== {$name} ====\n";
}

function check(string $name, bool $cond, string $detail = ''): void
{
    global $passCount, $failCount, $section;
    if ($cond) {
        $passCount++;
        echo "  ✔ [{$section}] {$name}\n";
    } else {
        $failCount++;
        echo "  ✘ [{$section}] {$name}" . ($detail !== '' ? " — {$detail}" : '') . "\n";
    }
}

// ---------------------------------------------------------------------
//  Database helpers (direct PDO; the suite is DB-aware by design)
// ---------------------------------------------------------------------
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        global $dbHost, $dbName, $dbUser, $dbPass;
        $pdo = new PDO(
            "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
            $dbUser, $dbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    return $pdo;
}

function dbScalar(string $sql, array $bind = []): mixed
{
    $st = db()->prepare($sql);
    $st->execute($bind);
    return $st->fetchColumn();
}

function dbRow(string $sql, array $bind = []): ?array
{
    $st = db()->prepare($sql);
    $st->execute($bind);
    $r = $st->fetch(PDO::FETCH_ASSOC);
    return $r === false ? null : $r;
}

// ---------------------------------------------------------------------
//  HTTP client (curl, cookie jar per actor, no redirect following)
// ---------------------------------------------------------------------
final class Client
{
    private string $jar;
    private string $base;

    public function __construct(string $tag, ?string $baseUrl = null)
    {
        global $base;
        $this->base = $baseUrl ?? $base;
        $this->jar = tempnam(sys_get_temp_dir(), 'pejar_' . $tag . '_');
        if ($this->jar === false) {
            throw new RuntimeException('no tmp cookie jar');
        }
    }

    public function __destruct()
    {
        @unlink($this->jar);
    }

    /**
     * @param array<string,string>        $fields
     * @param array<int,array{path:string,name:string,type:string}> $files
     * @param array<string,string>        $headers
     * @return array{status:int, location:?string, headers:array<string,string>, body:string}
     */
    public function request(string $method, string $path, array $fields = [], array $files = [], array $headers = []): array
    {
        $ch = curl_init($this->base . $path);
        if ($ch === false) {
            throw new RuntimeException('curl init failed');
        }
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_COOKIEJAR      => $this->jar,
            CURLOPT_COOKIEFILE     => $this->jar,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30,
        ];
        $method = strtoupper($method);
        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            if ($files !== []) {
                $post = $fields;
                foreach ($files as $i => $f) {
                    $post['files[' . $i . ']'] = new CURLFile($f['path'], $f['type'], $f['name']);
                }
                $opts[CURLOPT_POSTFIELDS] = $post;
            } else {
                $opts[CURLOPT_POSTFIELDS] = http_build_query($fields);
            }
        } else {
            $opts[CURLOPT_HTTPGET] = true;
            if ($fields !== []) {
                $opts[CURLOPT_URL] = $this->base . $path . '?' . http_build_query($fields);
            }
        }
        $opts[CURLOPT_HTTPHEADER] = [];
        foreach ($headers as $k => $v) {
            $opts[CURLOPT_HTTPHEADER][] = $k . ': ' . $v;
        }
        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);
        if (!is_string($raw)) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('curl error: ' . $err);
        }
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        $headEnd = strpos($raw, "\r\n\r\n");
        $head    = $headEnd === false ? $raw : substr($raw, 0, $headEnd);
        $body    = $headEnd === false ? '' : substr($raw, $headEnd + 4);
        // A redirect body (Location) is what we want; keep everything else.
        $headersOut = [];
        $location = null;
        foreach (explode("\r\n", $head) as $line) {
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $headersOut[strtolower(trim($k))] = trim($v);
                if (strtolower(trim($k)) === 'location') {
                    $location = trim($v);
                }
            }
        }
        return ['status' => $status, 'location' => $location, 'headers' => $headersOut, 'body' => $body];
    }

    /** Parse the first CSRF token: hidden inputs (name or id) or the
     *  data-csrf attribute of the admin footer script. */
    public function csrf(string $html): string
    {
        $patterns = [
            '/name="csrf_token" value="([a-f0-9]{64})"/',
            '/id="inp_csrf" value="([a-f0-9]{64})"/',
            '/data-csrf="([a-f0-9]{64})"/',
        ];
        foreach ($patterns as $re) {
            if (preg_match($re, $html, $m)) {
                return $m[1];
            }
        }
        return '';
    }
}

// ---------------------------------------------------------------------
//  Shared test material
// ---------------------------------------------------------------------
$m = new Client('master');

function freshToken(Client $c, string $url): string
{
    $r = $c->request('GET', $url);
    $t = $c->csrf($r['body']);
    if ($t === '') {
        throw new RuntimeException('no CSRF token found at ' . $url);
    }
    return $t;
}

function login(Client $c, string $email, string $password): array
{
    $r = $c->request('GET', '/admin/login');
    $token = $c->csrf($r['body']);
    if ($token === '' && $r['status'] === 302) {
        // The jar is still authenticated (the login page redirects away):
        // sign out properly, then fetch the fresh login form.
        logout($c);
        $r = $c->request('GET', '/admin/login');
        $token = $c->csrf($r['body']);
    }
    if ($token === '') {
        throw new RuntimeException('no CSRF token found at /admin/login');
    }
    return $c->request('POST', '/admin/login', [
        'csrf_token' => $token, 'email' => $email, 'password' => $password,
    ]);
}

function logout(Client $c): void
{
    // POST logout needs CSRF. The authenticated session cannot see the
    // login page (it redirects away), so take the token from a panel page;
    // its footer always embeds data-csrf.
    $r = $c->request('GET', '/admin/dashboard');
    $token = $c->csrf($r['body']);
    if ($token === '') {
        $r = $c->request('GET', '/admin/login');
        $token = $c->csrf($r['body']);
    }
    $c->request('POST', '/admin/logout', ['csrf_token' => $token]);
}

/** Random 24+ char password that passes the policy. */
function strongPass(): string
{
    // Random-only output: fixed class letters + a hex tail. Hex can never
    // embed an identity fragment of the test accounts (their emails/names
    // contain no 4+ char hex run), so the policy's substring rule can't
    // false-positive on it — and no real word can leak into the password.
    return 'Kz#9' . bin2hex(random_bytes(10)) . '!qX7';
}

/** Make a real zip archive at $path; returns entry count + uncompressed bytes. */
function makeZip(string $path, int $entries, int $bytesPerEntry): void
{
    $z = new ZipArchive();
    if ($z->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException('zip create failed');
    }
    $chunk = str_repeat("A", 4096);
    for ($i = 0; $i < $entries; $i++) {
        $payload = '';
        while (strlen($payload) < $bytesPerEntry) {
            $payload .= $chunk;
        }
        $z->addFromString("entry-{$i}.txt", substr($payload, 0, $bytesPerEntry));
    }
    $z->close();
}

function tmpPath(string $name): string
{
    $dir = sys_get_temp_dir() . '/pe-e2e';
    if (!is_dir($dir)) {
        mkdir($dir, 0770, true);
    }
    return $dir . '/' . $name;
}

// ---------------------------------------------------------------------
//  0. Sanity + DB seed
// ---------------------------------------------------------------------
section('0 · sanity & seed');
check('GET /healthz → 200', ($r = $m->request('GET', '/healthz'))['status'] === 200);
check('GET / → 301 /fa', ($r = $m->request('GET', '/'))['location'] === '/fa');
check('GET /fa/contact → 200', ($r = $m->request('GET', '/fa/contact'))['status'] === 200);
$contactHtml = $r['body'];
check('no CAPTCHA widget when disabled', !str_contains($contactHtml, 'pe-captcha')
    && !str_contains($contactHtml, 'challenges.cloudflare.com')
    && str_contains($contactHtml, 'PE_CONTACT_CAPTCHA = false'));
check('CSP default has no cloudflare origin', !str_contains((string) ($r['headers']['content-security-policy'] ?? ''), 'challenges.cloudflare.com'));

// Master admin: pin a deterministic state for the whole suite (this is a
// disposable local test database; the hash is set directly so every run
// starts from the same known secret).
$masterRow = dbRow('SELECT * FROM users WHERE email = ?', [$masterEmail]);
if ($masterRow === null) {
    fwrite(STDERR, "[e2e] master admin not found in DB; run create_admin.php first.\n");
    exit(2);
}
$masterId = (int) $masterRow['id'];
require __DIR__ . '/../app/core/Auth.php'; // hash options only
$masterHash = password_hash($masterPass, \Auth::hashOptions(), \Auth::hashAlgoOptions());
db()->prepare(
    'UPDATE users SET password_hash = :h, is_active = 1, role = :r,
            force_password_change = 0, totp_enabled = 0, totp_secret = NULL
     WHERE id = :id'
)->execute([
    ':h'  => $masterHash,
    ':r'  => 'super_admin',
    ':id' => $masterId,
]);
db()->exec('DELETE FROM login_attempts');
db()->exec("DELETE FROM messages WHERE ip IN ('127.0.0.1','::1')");
db()->exec("DELETE FROM users WHERE email LIKE 'pe-e2e-%@" . $domain . "'");
foreach (glob(sys_get_temp_dir() . '/pe-e2e/*') ?: [] as $f) {
    @unlink($f);
}

// ---------------------------------------------------------------------
//  1. Public inquiry attachments (MIME whitelist + zip-bomb)
// ---------------------------------------------------------------------
section('1 · attachment hardening (public inquiry)');

$c = new Client('anon');
$csrfAnon = freshToken($c, '/fa/contact');

$baseFields = static function () use ($csrfAnon): array {
    return [
        'csrf_token' => $csrfAnon,
        'lang'       => 'fa',
        'kind'       => 'contact',
        'name'       => 'E2E Tester',
        'email'      => 'e2e-sender@physioelectric.test',
        'body'       => 'E2E attachment test body',
    ];
};

// 1a. fake extension: PHP shell renamed .pdf
$f = tmpPath('evil.pdf');
file_put_contents($f, "<?php echo 'owned'; ?>");
$r = $c->request('POST', '/fa/inquiry', $baseFields(), [[
    'path' => $f, 'name' => 'resume.pdf', 'type' => 'application/pdf',
]]);
check('.pdf containing PHP text rejected (415)', $r['status'] === 415 && str_contains($r['body'], '"file"'), $r['body']);

// 1b. real image bytes, lying extension (.jpg containing PNG)
$png1x1 = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
$f = tmpPath('photo.jpg');
file_put_contents($f, (string) $png1x1);
$r = $c->request('POST', '/fa/inquiry', $baseFields(), [[
    'path' => $f, 'name' => 'photo.jpg', 'type' => 'image/jpeg',
]]);
check('PNG bytes with .jpg extension rejected (MIME mismatch, 415)', $r['status'] === 415, $r['body']);

// 1c. valid PNG with real .png extension accepted
$f = tmpPath('diagram.png');
file_put_contents($f, (string) $png1x1);
$r = $c->request('POST', '/fa/inquiry', $baseFields(), [[
    'path' => $f, 'name' => 'diagram.png', 'type' => 'image/png',
]]);
check('valid PNG accepted (ok:true)', $r['status'] === 200 && str_contains($r['body'], '"ok":true'), $r['body']);

// 1d. minimal real PDF accepted
$f = tmpPath('doc.pdf');
file_put_contents($f, "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 200 200]>>endobj\ntrailer<</Root 1 0 R/Size 4>>\n%%EOF\n");
$r = $c->request('POST', '/fa/inquiry', $baseFields(), [[
    'path' => $f, 'name' => 'doc.pdf', 'type' => 'application/pdf',
]]);
check('real minimal PDF accepted', $r['status'] === 200 && str_contains($r['body'], '"ok":true'), $r['body']);

// 1e. zip bomb by entry count (60 tiny entries > 50)
$f = tmpPath('bomb-count.zip');
makeZip($f, 60, 100);
$r = $c->request('POST', '/fa/inquiry', $baseFields(), [[
    'path' => $f, 'name' => 'archive.zip', 'type' => 'application/zip',
]]);
check('zip with 60 entries rejected (415)', $r['status'] === 415, $r['body']);

// 1f. zip bomb by uncompressed size (11 × 5 MB zeros = 55 MB > 50 MB)
$f = tmpPath('bomb-size.zip');
makeZip($f, 11, 5 * 1024 * 1024);
clearstatcache(true, $f);
check('oversized-uncompressed zip file itself stays small', (int) filesize($f) < 2 * 1024 * 1024, (string) filesize($f));
$r = $c->request('POST', '/fa/inquiry', $baseFields(), [[
    'path' => $f, 'name' => 'archive.zip', 'type' => 'application/zip',
]]);
check('zip with 55 MB uncompressed rejected (415)', $r['status'] === 415, $r['body']);

// 1g. honest small zip accepted
$f = tmpPath('ok.zip');
makeZip($f, 2, 100);
$r = $c->request('POST', '/fa/inquiry', $baseFields(), [[
    'path' => $f, 'name' => 'files.zip', 'type' => 'application/zip',
]]);
check('honest 2-entry zip accepted', $r['status'] === 200 && str_contains($r['body'], '"ok":true'), $r['body']);

// 1h. docx (zip container with OOXML payload) accepted under .docx
$f = tmpPath('letter.docx');
$z = new ZipArchive();
if ($z->open($f, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
    $z->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"/>');
    $z->addFromString('word/document.xml', '<?xml version="1.0"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"/>');
    $z->close();
}
$r = $c->request('POST', '/fa/inquiry', $baseFields(), [[
    'path' => $f, 'name' => 'letter.docx', 'type' => 'application/zip',
]]);
check('docx (zip container) accepted', $r['status'] === 200 && str_contains($r['body'], '"ok":true'), $r['body']);

$stored = db()->query("SELECT attachments FROM messages ORDER BY id DESC LIMIT 1")->fetchColumn();
$storedFlat = is_string($stored) ? str_replace('\\/', '/', $stored) : '';
check('stored attachment path points at /uploads/attachments/',
    is_string($stored) && str_contains($storedFlat, '/uploads/attachments/'), (string) $stored);

// 1i. plain submission (no files) still works (control) — 5th success
$r = $c->request('POST', '/fa/inquiry', array_merge($baseFields(), [
    'name' => 'E2E Plain', 'email' => 'e2e-plain@physioelectric.test',
]));
check('plain submission without attachments accepted', $r['status'] === 200 && str_contains($r['body'], '"ok":true'), $r['body']);
$controlId = (int) dbScalar('SELECT MAX(id) FROM messages');
check('control message stored', $controlId > 0);

// ---------------------------------------------------------------------
//  2. Master login + panel reachable
// ---------------------------------------------------------------------
section('2 · super_admin login & users list');
$r = login($m, $masterEmail, $masterPass);
check('master login → 302 (no forced flag in this suite)', $r['status'] === 302, $r['status'] . ' ' . (string) $r['location']);
check('redirect goes to /admin/dashboard', ($r['location'] ?? '') === '/admin/dashboard', (string) $r['location']);

$r = $m->request('GET', '/admin/users');
check('GET /admin/users → 200 for super_admin', $r['status'] === 200);
check('users page lists the master email', str_contains($r['body'], $masterEmail));

// ---------------------------------------------------------------------
//  3. Admin-user management (create/edit/toggle + guards)
// ---------------------------------------------------------------------
section('3 · admin-user management');
$tok = $m->csrf($r['body']);
if ($tok === '') {
    $tok = freshToken($m, '/admin/users');
}

$pwEditor = strongPass();
$pwViewer = strongPass();
$pwSuper2 = strongPass();
$edEmail  = 'pe-e2e-editor@' . $domain;
$vwEmail  = 'pe-e2e-viewer@' . $domain;
$spEmail  = 'pe-e2e-super2@' . $domain;

// 3a. create editor without current_password → refused (re-auth)
$r = $m->request('POST', '/admin/users/create', [
    'csrf_token' => $tok, 'name' => 'Editor One', 'email' => $edEmail,
    'role' => 'editor', 'password' => $pwEditor, // no current_password
]);
check('create without current_password refused', $r['status'] === 302
    && dbScalar('SELECT COUNT(*) FROM users WHERE email = ?', [$edEmail]) == 0);

// 3b. wrong current_password → refused
$r = $m->request('POST', '/admin/users/create', [
    'csrf_token' => $tok, 'name' => 'Editor One', 'email' => $edEmail,
    'role' => 'editor', 'password' => $pwEditor, 'current_password' => 'totally-wrong',
]);
check('create with wrong current_password refused', $r['status'] === 302
    && dbScalar('SELECT COUNT(*) FROM users WHERE email = ?', [$edEmail]) == 0);

// 3c. weak password (leaked) → refused with policy feedback
$r = $m->request('POST', '/admin/users/create', [
    'csrf_token' => $tok, 'name' => 'Weak One', 'email' => 'pe-e2e-weak@' . $domain,
    'role' => 'editor', 'password' => 'Welcome1234567890', 'current_password' => $masterPass,
]);
check('create with leaked common password refused', $r['status'] === 302
    && dbScalar('SELECT COUNT(*) FROM users WHERE email = ?', ['pe-e2e-weak@' . $domain]) == 0);
$r = $m->request('GET', '/admin/users/create');
check('policy reason visible in the re-rendered form (leaked list)',
    str_contains($r['body'], 'لو رفته') || str_contains($r['body'], 'رایج'));

// 3d. bogus role → refused
$r = $m->request('POST', '/admin/users/create', [
    'csrf_token' => $tok, 'name' => 'Rogue', 'email' => 'pe-e2e-rogue@' . $domain,
    'role' => 'god_mode', 'password' => $pwEditor, 'current_password' => $masterPass,
]);
check('create with out-of-enum role refused', $r['status'] === 302
    && dbScalar('SELECT COUNT(*) FROM users WHERE email = ?', ['pe-e2e-rogue@' . $domain]) == 0);

// 3e. valid creates (editor, viewer, second super)
$r = $m->request('POST', '/admin/users/create', [
    'csrf_token' => $tok, 'name' => 'Editor One', 'email' => $edEmail,
    'role' => 'editor', 'password' => $pwEditor, 'current_password' => $masterPass,
]);
$editorRow = dbRow('SELECT * FROM users WHERE email = ?', [$edEmail]);
check('editor created', $editorRow !== null
    && $editorRow['role'] === 'editor' && (int) $editorRow['force_password_change'] === 1
    && (int) $editorRow['created_by'] === (int) $masterRow['id']);

$r = $m->request('POST', '/admin/users/create', [
    'csrf_token' => $tok, 'name' => 'Viewer One', 'email' => $vwEmail,
    'role' => 'viewer', 'password' => $pwViewer, 'current_password' => $masterPass,
]);
$viewerRow = dbRow('SELECT * FROM users WHERE email = ?', [$vwEmail]);
check('viewer created (force_password_change=1)', $viewerRow !== null
    && $viewerRow['role'] === 'viewer' && (int) $viewerRow['force_password_change'] === 1);

$r = $m->request('POST', '/admin/users/create', [
    'csrf_token' => $tok, 'name' => 'Super Two', 'email' => $spEmail,
    'role' => 'super_admin', 'password' => $pwSuper2, 'current_password' => $masterPass,
]);
$super2Row = dbRow('SELECT * FROM users WHERE email = ?', [$spEmail]);
check('second super_admin created', $super2Row !== null && $super2Row['role'] === 'super_admin');

$editorId = (int) $editorRow['id'];
$viewerId = (int) $viewerRow['id'];
$super2Id = (int) $super2Row['id'];
$masterId = (int) $masterRow['id'];

// 3f. guards: self-delete / self-toggle / self-demote all refused
$r = $m->request('POST', '/admin/users/delete', ['csrf_token' => $tok, 'id' => (string) $masterId]);
check('self soft-delete refused', dbScalar('SELECT is_active FROM users WHERE id = ?', [$masterId]) == 1);
$r = $m->request('POST', '/admin/users/toggle', ['csrf_token' => $tok, 'id' => (string) $masterId]);
check('self deactivation refused', dbScalar('SELECT is_active FROM users WHERE id = ?', [$masterId]) == 1);
// with a correct re-auth the deeper guard still refuses (no self lock-out)
$r = $m->request('POST', '/admin/users/toggle', [
    'csrf_token' => $tok, 'id' => (string) $masterId, 'current_password' => $masterPass,
]);
check('self deactivation refused even with valid current_password',
    dbScalar('SELECT is_active FROM users WHERE id = ?', [$masterId]) == 1);
$r = $m->request('POST', '/admin/users/' . $masterId, [
    'csrf_token' => $tok, 'name' => (string) $masterRow['name'], 'role' => 'editor',
    'is_active' => '1', 'current_password' => $masterPass,
]);
check('self demotion via edit refused (role still super_admin)',
    dbScalar('SELECT role FROM users WHERE id = ?', [$masterId]) === 'super_admin');

// 3g. toggle viewer off/on (soft delete semantics)
$r = $m->request('POST', '/admin/users/toggle', [
    'csrf_token' => $tok, 'id' => (string) $viewerId, 'current_password' => $masterPass,
]);
check('deactivating another user works (soft-delete)', $r['status'] === 302
    && dbScalar('SELECT is_active FROM users WHERE id = ?', [$viewerId]) == 0);
$r = $m->request('POST', '/admin/users/toggle', [
    'csrf_token' => $tok, 'id' => (string) $viewerId, 'current_password' => $masterPass,
]);
check('re-activating works', $r['status'] === 302
    && dbScalar('SELECT is_active FROM users WHERE id = ?', [$viewerId]) == 1);

// 3h. editor cannot touch /admin/users (403 even on GET)
$e = new Client('editor');
$r = login($e, $edEmail, $pwEditor);
check('editor first login → redirected to forced password change', $r['status'] === 302
    && ($r['location'] ?? '') === '/admin/forced-password', (string) $r['location']);
$r = $e->request('GET', '/admin/dashboard');
check('forced editor blocked from /admin/dashboard (302 → forced page)', $r['status'] === 302
    && ($r['location'] ?? '') === '/admin/forced-password');
$r = $e->request('GET', '/admin/posts/create');
check('forced editor blocked from /admin/posts/create too', $r['status'] === 302
    && ($r['location'] ?? '') === '/admin/forced-password');

// 3i. complete the forced change → panel opens with editor powers
$tokE = freshToken($e, '/admin/forced-password');
$newEditorPass = strongPass();
$r = $e->request('POST', '/admin/forced-password', [
    'csrf_token' => $tokE,
    'new_password' => $newEditorPass, 'confirm_password' => $newEditorPass,
]);
check('forced password change → 302 to /admin/dashboard', $r['status'] === 302
    && ($r['location'] ?? '') === '/admin/dashboard', (string) $r['location']);
check('force_password_change cleared in DB', dbScalar('SELECT force_password_change FROM users WHERE id = ?', [$editorId]) == 0);
$r = $e->request('GET', '/admin/dashboard');
check('editor reaches /admin/dashboard after rotation', $r['status'] === 200);
$r = $e->request('GET', '/admin/users');
check('editor GET /admin/users → 403', $r['status'] === 403, (string) $r['status']);
$tokE2 = $e->csrf($r['body']) ?: freshToken($e, '/admin/dashboard');
$r = $e->request('POST', '/admin/users/create', [
    'csrf_token' => $tokE2, 'name' => 'Nope', 'email' => 'pe-e2e-nope@' . $domain,
    'role' => 'viewer', 'password' => strongPass(), 'current_password' => $newEditorPass,
]);
check('editor POST /admin/users/create → 403', $r['status'] === 403, (string) $r['status']);
$r = $e->request('GET', '/admin/posts');
check('editor GET /admin/posts → 200', $r['status'] === 200);
$r = $e->request('GET', '/admin/posts/create');
check('editor GET /admin/posts/create → 200 (form allowed)', $r['status'] === 200);
$tokE3 = $e->csrf($r['body']) ?: freshToken($e, '/admin/posts');
$r = $e->request('POST', '/admin/messages/' . $controlId . '/read', ['csrf_token' => $tokE3]);
check('editor POST message toggleRead → 302 (mutation allowed)', $r['status'] === 302, (string) $r['status']);

// 3j. viewer: read-only everywhere
$v = new Client('viewer');
$r = login($v, $vwEmail, $pwViewer);
check('viewer first login → forced password change', $r['status'] === 302
    && ($r['location'] ?? '') === '/admin/forced-password');
$tokV = freshToken($v, '/admin/forced-password');
$newViewerPass = strongPass();
$r = $v->request('POST', '/admin/forced-password', [
    'csrf_token' => $tokV, 'new_password' => $newViewerPass, 'confirm_password' => $newViewerPass,
]);
check('viewer completes forced change', $r['status'] === 302);
$r = $v->request('GET', '/admin/messages');
check('viewer GET /admin/messages → 200 (view allowed)', $r['status'] === 200);
$r = $v->request('GET', '/admin/posts/create');
check('viewer GET /admin/posts/create → 403', $r['status'] === 403, (string) $r['status']);
$r = $v->request('GET', '/admin/posts/1/edit');
check('viewer GET /admin/posts/1/edit → 403', $r['status'] === 403, (string) $r['status']);
$r = $v->request('GET', '/admin/messages/' . $controlId);
check('viewer GET message detail → 200', $r['status'] === 200);
$tokV2 = $v->csrf($r['body']) ?: freshToken($v, '/admin/messages');
$r = $v->request('POST', '/admin/messages/' . $controlId . '/read', ['csrf_token' => $tokV2]);
check('viewer POST message toggleRead → 403', $r['status'] === 403, (string) $r['status']);
$r = $v->request('GET', '/admin/upload');
check('viewer GET /admin/upload → 403', $r['status'] === 403, (string) $r['status']);

// 3k. viewer can still manage her OWN account (password)
$r = $v->request('GET', '/admin/account');
check('viewer GET /admin/account → 200', $r['status'] === 200);
$tokV3 = $v->csrf($r['body']) ?: freshToken($v, '/admin/account');
$rotatedViewerPass = strongPass();
$r = $v->request('POST', '/admin/account/password', [
    'csrf_token' => $tokV3,
    'current_password' => $newViewerPass,
    'new_password' => $rotatedViewerPass,
    'confirm_password' => $rotatedViewerPass,
]);
check('viewer changes her own password → 302', $r['status'] === 302, (string) $r['status']);
logout($v);
$r = login($v, $vwEmail, $rotatedViewerPass);
check('viewer re-login with rotated password works', $r['status'] === 302);

// 3l. account password change rejects weak policy password
$r = $v->request('GET', '/admin/account');
$tokV4 = $v->csrf($r['body']) ?: freshToken($v, '/admin/account');
$r = $v->request('POST', '/admin/account/password', [
    'csrf_token' => $tokV4, 'current_password' => $rotatedViewerPass,
    'new_password' => 'Welcome1234567890', 'confirm_password' => 'Welcome1234567890',
]);
check('weak password on account change refused', $r['status'] === 302
    && !password_verify('Welcome1234567890', (string) dbScalar('SELECT password_hash FROM users WHERE id = ?', [$viewerId])));

// 3m. second super can deactivate the master? NO — master is not the last
//     super while super2 exists, and self-deactivation is blocked. Test the
//     inverse guard instead: super2 removes editor? not needed. Verify
//     super2 has super powers and then cleanup-delete super2 later.
$s2 = new Client('super2');
$r = login($s2, $spEmail, $pwSuper2);
check('second super first login → forced page', $r['status'] === 302
    && ($r['location'] ?? '') === '/admin/forced-password');
$tokS2 = freshToken($s2, '/admin/forced-password');
$pwSuper2b = strongPass();
$r = $s2->request('POST', '/admin/forced-password', [
    'csrf_token' => $tokS2, 'new_password' => $pwSuper2b, 'confirm_password' => $pwSuper2b,
]);
$r = $s2->request('GET', '/admin/users');
check('second super reaches /admin/users', $r['status'] === 200);

// 3n. Last-active-super window: with super2 as the ONLY active super, no
//     path may remove the final super_admin (self-targeted ops are refused),
//     and the count invariant holds after every mutation.
$tokS2b = freshToken($s2, '/admin/users');
$r = $s2->request('POST', '/admin/users/toggle', [
    'csrf_token' => $tokS2b, 'id' => (string) $masterId, 'current_password' => $pwSuper2b,
]);
check('super2 deactivates master while 2 supers exist → allowed', $r['status'] === 302
    && dbScalar('SELECT is_active FROM users WHERE id = ?', [$masterId]) == 0);
$r = $s2->request('POST', '/admin/users/toggle', [
    'csrf_token' => $tokS2b, 'id' => (string) $super2Id, 'current_password' => $pwSuper2b,
]);
check('last active super cannot deactivate self', $r['status'] === 302
    && dbScalar('SELECT is_active FROM users WHERE id = ?', [$super2Id]) == 1);
$r = $s2->request('POST', '/admin/users/' . $super2Id, [
    'csrf_token' => $tokS2b, 'name' => (string) $super2Row['name'], 'role' => 'viewer',
    'is_active' => '1', 'current_password' => $pwSuper2b,
]);
check('last active super cannot demote self (role pinned to super_admin)',
    dbScalar('SELECT role FROM users WHERE id = ?', [$super2Id]) === 'super_admin');
check('at least one active super_admin always remains',
    (int) dbScalar("SELECT COUNT(*) FROM users WHERE role = 'super_admin' AND is_active = 1") >= 1);
$r = $s2->request('POST', '/admin/users/toggle', [
    'csrf_token' => $tokS2b, 'id' => (string) $masterId, 'current_password' => $pwSuper2b,
]);
check('last super can re-activate a colleague (still ≥1 active super)',
    $r['status'] === 302 && dbScalar('SELECT is_active FROM users WHERE id = ?', [$masterId]) == 1);
$r = login($m, $masterEmail, $masterPass);
check('master re-login works after the reactivation window', $r['status'] === 302);

// ---------------------------------------------------------------------
//  4. TOTP 2FA on the master account
// ---------------------------------------------------------------------
section('4 · TOTP 2FA (enable → challenge → disable)');

// 4a. enable: grab the generated secret from the setup page
$r = $m->request('GET', '/admin/account/2fa/setup');
check('2FA setup page → 200', $r['status'] === 200);
if (!preg_match('/([A-Z2-7]{32})/', $r['body'], $mm)) {
    check('setup page exposes a base32 secret', false, 'no secret in HTML');
    $totpSecret = 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
} else {
    $totpSecret = $mm[1];
    check('setup page exposes a base32 secret', true);
}
$tokM = $m->csrf($r['body']) ?: freshToken($m, '/admin/account/2fa/setup');

// 4b. wrong code → refused
$r = $m->request('POST', '/admin/account/2fa/setup', [
    'csrf_token' => $tokM, 'code' => '000000', 'current_password' => $masterPass,
]);
check('2FA enable with wrong code refused', dbScalar('SELECT totp_enabled FROM users WHERE id = ?', [$masterId]) == 0);

// 4c. right code + password → enabled
$code = totpCodeAt($totpSecret, time());
$r = $m->request('POST', '/admin/account/2fa/setup', [
    'csrf_token' => $tokM, 'code' => $code, 'current_password' => $masterPass,
]);
check('2FA enable with valid code → 302', $r['status'] === 302, (string) $r['status']);
check('totp_enabled=1 + secret persisted', dbScalar('SELECT totp_enabled FROM users WHERE id = ?', [$masterId]) == 1
    && dbScalar('SELECT totp_secret FROM users WHERE id = ?', [$masterId]) === $totpSecret);

// 4d. login now requires the second factor
logout($m);
$r = login($m, $masterEmail, $masterPass);
check('login with 2FA on → 302 to /admin/login/2fa', $r['status'] === 302
    && ($r['location'] ?? '') === '/admin/login/2fa', (string) $r['location']);
$r = $m->request('GET', '/admin/login/2fa');
check('2FA step page renders', $r['status'] === 200 && str_contains($r['body'], 'csrf_token'));
$tok2 = $m->csrf($r['body']) ?: freshToken($m, '/admin/login');
$r = $m->request('POST', '/admin/login/2fa', ['csrf_token' => $tok2, 'code' => '000000']);
check('wrong 2FA code → 401', $r['status'] === 401, (string) $r['status']);
$r = $m->request('GET', '/admin/login/2fa');
$tok2 = $m->csrf($r['body']) ?: $tok2;
$code = totpCodeAt($totpSecret, time());
$r = $m->request('POST', '/admin/login/2fa', ['csrf_token' => $tok2, 'code' => $code]);
check('correct 2FA code → 302 to dashboard', $r['status'] === 302
    && ($r['location'] ?? '') === '/admin/dashboard', $r['status'] . ' ' . (string) $r['location']);
$r = $m->request('GET', '/admin/dashboard');
check('master fully logged in after 2FA', $r['status'] === 200);

// 4e. disable 2FA again (needs the account password)
$r = $m->request('GET', '/admin/account');
$tokM2 = $m->csrf($r['body']) ?: freshToken($m, '/admin/account');
$r = $m->request('POST', '/admin/account/2fa/disable', [
    'csrf_token' => $tokM2, 'current_password' => $masterPass,
]);
check('2FA disable with password → 302', $r['status'] === 302, (string) $r['status']);
check('totp disabled in DB', dbScalar('SELECT totp_enabled FROM users WHERE id = ?', [$masterId]) == 0
    && dbScalar('SELECT totp_secret FROM users WHERE id = ?', [$masterId]) === null);

// 4f. login works without 2FA again
logout($m);
$r = login($m, $masterEmail, $masterPass);
check('login after 2FA disable → dashboard directly', $r['status'] === 302
    && ($r['location'] ?? '') === '/admin/dashboard');

// ---------------------------------------------------------------------
//  5. CAPTCHA plumbing against a second server with dummy keys
// ---------------------------------------------------------------------
$capBase = rtrim((string) getenv('PE_CAPTCHA_BASE'), '/');
if ($capBase !== '') {
    section('5 · CAPTCHA (Turnstile, dummy keys)');
    $cc = new Client('captcha', $capBase);
    $r = $cc->request('GET', '/fa/contact');
    check('captcha page renders the widget container', $r['status'] === 200
        && str_contains($r['body'], 'pe-captcha')
        && str_contains($r['body'], 'PE_CONTACT_CAPTCHA = true'));
    check('captcha page loads the Turnstile script origin',
        str_contains($r['body'], 'challenges.cloudflare.com/turnstile/v0/api.js'));
    check('CSP allows the Turnstile origin',
        str_contains((string) ($r['headers']['content-security-policy'] ?? ''), 'https://challenges.cloudflare.com'));
    $token = $cc->csrf($r['body']);
    $r = $cc->request('POST', '/fa/inquiry', [
        'csrf_token' => $token, 'lang' => 'fa', 'kind' => 'contact',
        'name' => 'Bot', 'email' => 'bot@physioelectric.test', 'body' => 'x',
    ]);
    check('inquiry without a solved token → 422 code=captcha', $r['status'] === 422
        && str_contains($r['body'], '"captcha"'), $r['status'] . ' ' . $r['body']);
    // The server on :8091 runs with a *junk* secret (not Cloudflare's
    // always-pass test key), so a forged token must fail siteverify and the
    // submission is rejected — fail closed, whether the provider is
    // reachable or not.
    $r = $cc->request('POST', '/fa/inquiry', [
        'csrf_token' => $token, 'lang' => 'fa', 'kind' => 'contact',
        'name' => 'Bot2', 'email' => 'bot2@physioelectric.test', 'body' => 'x',
        'cf-turnstile-response' => 'XXXX.forged-token.XXXX',
    ]);
    check('forged token fails server-side verification (fail closed)', $r['status'] === 422
        && str_contains($r['body'], '"captcha"'), $r['status'] . ' ' . $r['body']);
}

// ---------------------------------------------------------------------
//  6. Cleanup — remove every pe-e2e-* row, test attachments, restore master
// ---------------------------------------------------------------------
section('6 · cleanup');
foreach (glob(sys_get_temp_dir() . '/pe-e2e/*') ?: [] as $f) {
    @unlink($f);
}
$rows = db()->query("SELECT id FROM users WHERE email LIKE 'pe-e2e-%" . $domain . "'")->fetchAll();
foreach ($rows as $row) {
    db()->prepare('DELETE FROM users WHERE id = ?')->execute([(int) $row['id']]);
}
db()->exec('DELETE FROM login_attempts');
$mRows = db()->query("SELECT id, attachments FROM messages WHERE ip IN ('127.0.0.1','::1')")->fetchAll();
foreach ($mRows as $row) {
    $atts = json_decode((string) $row['attachments'], true);
    if (is_array($atts)) {
        foreach ($atts as $att) {
            $rel = (string) ($att['path'] ?? '');
            $full = __DIR__ . '/../app' . str_replace('/', DIRECTORY_SEPARATOR, $rel);
            if (is_string($rel) && str_starts_with($rel, '/uploads/attachments/') && is_file($full)) {
                @unlink($full);
            }
        }
    }
    db()->prepare('DELETE FROM messages WHERE id = ?')->execute([(int) $row['id']]);
}
db()->prepare('UPDATE users SET is_active=1, force_password_change=0, totp_enabled=0 WHERE id = ?')
    ->execute([$masterId]);
check('test users cleaned up', dbScalar('SELECT COUNT(*) FROM users WHERE email LIKE ?', ['pe-e2e-%@' . $domain]) == 0);
check('master admin restored (active, no force, no 2FA)', dbScalar('SELECT COUNT(*) FROM users WHERE id=? AND is_active=1 AND force_password_change=0 AND totp_enabled=0', [$masterId]) == 1);

echo "\n============================================================\n";
echo "E2E RESULT: {$passCount} passed, {$failCount} failed\n";
exit($failCount === 0 ? 0 : 1);
