<?php
declare(strict_types=1);

/**
 * Public endpoint that receives the contact / project-order wizard
 * submission and stores it as a received message for the admin inbox.
 *
 * Hardened: CSRF, honeypot, per-IP throttle, strict field caps, email
 * validation and a tightly-scoped attachment whitelist. Attachments are
 * stored where the web server will never serve them publicly; the admin
 * downloads them through an authenticated route only.
 *
 * Attachment defence (added in CHANGES-SECURITY-2.md):
 *  - the real file type (finfo / FILEINFO_MIME_TYPE) must match the claimed
 *    extension against a strict whitelist; mismatches are rejected and
 *    audited as 'inquiry.attachment_mime_mismatch'
 *  - zip files are inspected before storage (entry count + total
 *    uncompressed size) as a zip-bomb guard; without ext-zip the upload is
 *    refused (fail closed)
 *  - an optional CAPTCHA (Cloudflare Turnstile, opt-in via CAPTCHA_* env)
 *    is verified server side before the per-IP throttle runs.
 */
final class InquiryController
{
    private const ATTACH_EXT = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'zip'];
    private const ATTACH_MAX_BYTES = 2 * 1024 * 1024;   // 2 MB each
    private const ATTACH_MAX_COUNT = 3;
    private const HOURLY_LIMIT = 5;                      // submissions / IP / hour

    /**
     * Real-MIME whitelist per extension. The values are what finfo()
     * reports for real files; a name can no longer be trusted.
     *  - docx is a ZIP-based OOXML container: libmagic may report the
     *    OOXML type or plain application/zip depending on the host.
     *  - .doc files are OLE2/CDF containers reported under a few aliases.
     */
    private const ATTACH_MIME = [
        'pdf'  => ['application/pdf'],
        'zip'  => ['application/zip'],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
        ],
        'doc'  => [
            'application/msword',
            'application/x-ole-storage',
            'application/CDFV2',
            'application/vnd.ms-office',
        ],
        'png'  => ['image/png'],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
    ];

    // Zip-bomb guard: generous but bounded — anything beyond these numbers
    // is refused before the archive is written to disk.
    private const ZIP_MAX_ENTRIES = 50;
    private const ZIP_MAX_UNCOMPRESSED_BYTES = 50 * 1024 * 1024;

    public static function store(): void
    {
        // ---- CSRF -----------------------------------------------------
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
        if (!Csrf::verify(is_string($token) ? $token : null)) {
            Security::audit('inquiry.csrf_rejected');
            json_response(['ok' => false, 'code' => 'csrf'], 419);
        }

        // ---- Honeypot: bots fill it, humans never see it --------------
        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            Security::audit('inquiry.honeypot');
            json_response(['ok' => true]); // lie to the bot, store nothing
        }

        // ---- Optional CAPTCHA (server-side verification, before the
        //      throttle so bot traffic cannot eat the visitor's quota) ----
        $captcha = $_POST['cf-turnstile-response'] ?? null;
        if (!Captcha::verify(is_string($captcha) ? $captcha : null)) {
            json_response(['ok' => false, 'code' => 'captcha'], 422);
        }

        // ---- Schema guard (before any query on `messages`) -------------
        // An un-migrated database must yield a clean, audited error instead
        // of an uncaught exception (which the visitor's wizard would swallow).
        $ip = Security::clientIp();
        if (!Database::tableExists('messages')) {
            Security::audit('inquiry.schema_missing', ['ip' => $ip]);
            json_response(['ok' => false, 'code' => 'server'], 500);
        }

        // ---- Per-IP throttle ------------------------------------------
        $st = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM messages WHERE ip = :ip AND created_at > (NOW() - INTERVAL 1 HOUR)'
        );
        $st->execute([':ip' => $ip]);
        $recent = (int) $st->fetchColumn();
        if ($recent >= self::HOURLY_LIMIT) {
            Security::audit('inquiry.rate_limited', ['ip' => $ip]);
            json_response(['ok' => false, 'code' => 'rate'], 429);
        }

        // ---- Validate -------------------------------------------------
        $lang = in_array((string) ($_POST['lang'] ?? ''), ['fa', 'en'], true)
            ? (string) $_POST['lang'] : 'fa';

        $name  = str_cap(trim((string) ($_POST['name'] ?? '')), 120);
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $body  = str_cap(trim((string) ($_POST['body'] ?? '')), 3000);

        if ($name === '') {
            json_response(['ok' => false, 'code' => 'name'], 422);
        }
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            json_response(['ok' => false, 'code' => 'email'], 422);
        }
        if (strlen($email) > 190) {
            json_response(['ok' => false, 'code' => 'email'], 422);
        }
        if ($body === '') {
            json_response(['ok' => false, 'code' => 'body'], 422);
        }

        $method = (string) ($_POST['contact_method'] ?? '');
        if (!in_array($method, ['', 'Email', 'Phone', 'Telegram'], true)) {
            $method = '';
        }

        // ---- Attachments ----------------------------------------------
        $attachments = self::storeAttachments();
        if ($attachments === null) {
            json_response(['ok' => false, 'code' => 'file'], 415);
        }

        // ---- Persist --------------------------------------------------
        // A storage failure must surface as a clean error, never as a silent
        // "ok" that loses the visitor's request.
        try {
            MessageModel::create([
            'kind'           => (string) ($_POST['kind'] ?? 'contact') === 'project' ? 'project' : 'contact',
            'category'       => str_cap(trim((string) ($_POST['categories'] ?? '')), 190),
            'name'           => $name,
            'company'        => str_cap(trim((string) ($_POST['company'] ?? '')), 160),
            'email'          => $email,
            'phone'          => str_cap(preg_replace('/[^\d+\-()\s]/', '', (string) ($_POST['phone'] ?? '')), 40),
            'contact_method' => $method,
            'contact_id'     => str_cap(trim((string) ($_POST['contact_id'] ?? '')), 120),
            'timeline'       => str_cap(trim((string) ($_POST['timeline'] ?? '')), 60),
            'body'           => $body,
            'notes'          => str_cap(trim((string) ($_POST['notes'] ?? '')), 500),
            'lang'           => $lang,
            'attachments'    => $attachments === [] ? '' : (string) json_encode($attachments, JSON_UNESCAPED_UNICODE),
            'ip'             => $ip,
            ]);
        } catch (Throwable $e) {
            Security::audit('inquiry.store_failed', ['ip' => $ip]);
            json_response(['ok' => false, 'code' => 'server'], 500);
        }

        Security::audit('inquiry.stored', ['name' => $name, 'ip' => $ip]);
        json_response(['ok' => true]);
    }

    /**
     * @return array<int, array{name:string,path:string}>|null  null on rejection
     */
    private static function storeAttachments(): ?array
    {
        $files = $_FILES['files'] ?? null;
        if ($files === null) {
            return [];
        }
        // Normalise the multi-file array shape.
        $list = [];
        if (isset($files['name']) && is_array($files['name'])) {
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                $list[] = [
                    'name'     => $files['name'][$i] ?? '',
                    'tmp_name' => $files['tmp_name'][$i] ?? '',
                    'error'    => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                ];
            }
        } elseif (isset($files['name'])) {
            $list[] = $files;
        }

        // Drop "no file" slots.
        $list = array_values(array_filter(
            $list,
            static fn(array $f): bool => (int) ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK
        ));
        if ($list === []) {
            return [];
        }
        if (count($list) > self::ATTACH_MAX_COUNT) {
            return null;
        }

        $dir = BASE_PATH . '/uploads/attachments';
        if (!is_dir($dir) && !@mkdir($dir, 0770, true) && !is_dir($dir)) {
            return null;
        }

        $stored = [];
        foreach ($list as $f) {
            $tmp = (string) $f['tmp_name'];
            if (!is_uploaded_file($tmp)) {
                return null;
            }
            clearstatcache(true, $tmp);
            $size = @filesize($tmp);
            if ($size === false || $size <= 0 || $size > self::ATTACH_MAX_BYTES) {
                return null;
            }
            $ext = strtolower((string) pathinfo((string) $f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, self::ATTACH_EXT, true)) {
                return null;
            }

            // The extension is only a claim; verify the real file type.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = (string) $finfo->file($tmp);
            if (!isset(self::ATTACH_MIME[$ext]) || !in_array($mime, self::ATTACH_MIME[$ext], true)) {
                Security::audit('inquiry.attachment_mime_mismatch', [
                    'name' => self::safeName((string) $f['name']),
                    'ext'  => $ext,
                    'mime' => $mime === '' ? 'unknown' : $mime,
                ]);
                return null;
            }

            // Zip-bomb guard: inspect the archive before it reaches disk.
            if ($ext === 'zip' && !self::zipIsSafe($tmp)) {
                return null;
            }

            $name = date('Ymd-His') . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
            $dest = $dir . '/' . $name;
            if (!@move_uploaded_file($tmp, $dest)) {
                return null;
            }
            @chmod($dest, 0640);
            $stored[] = [
                'name' => substr((string) preg_replace('/[^\x20-\x7E\x{0600}-\x{06FF}]/u', '', (string) $f['name']), 0, 120),
                'path' => '/uploads/attachments/' . $name,
            ];
        }
        return $stored;
    }

    /**
     * Refuse archives that could decompress into an unreasonable amount of
     * data (zip-bomb defence): more than ZIP_MAX_ENTRIES members or more
     * than ZIP_MAX_UNCOMPRESSED_BYTES of total uncompressed content.
     * Without ext-zip the check is impossible → fail closed (reject).
     */
    private static function zipIsSafe(string $tmp): bool
    {
        if (!class_exists('ZipArchive')) {
            Security::audit('inquiry.zip_check_unavailable');
            return false;
        }
        $zip = new ZipArchive();
        if (@$zip->open($tmp) !== true) {
            Security::audit('inquiry.zip_unreadable');
            return false;
        }
        $entries = 0;
        $totalUncompressed = 0;
        $count = (int) $zip->numFiles;
        for ($i = 0; $i < $count; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) {
                continue;
            }
            $entries++;
            $totalUncompressed += (int) ($stat['size'] ?? 0);
            if ($entries > self::ZIP_MAX_ENTRIES
                || $totalUncompressed > self::ZIP_MAX_UNCOMPRESSED_BYTES) {
                $zip->close();
                Security::audit('inquiry.zip_bomb_rejected', [
                    'entries'         => $entries,
                    'uncompressed'    => $totalUncompressed,
                    'compressed'      => (int) @filesize($tmp),
                ]);
                return false;
            }
        }
        $zip->close();
        return true;
    }

    /** Log-safe file name (no control chars, capped). */
    private static function safeName(string $name): string
    {
        return substr((string) preg_replace('/[^\x20-\x7E]/', '', $name), 0, 120);
    }
}
