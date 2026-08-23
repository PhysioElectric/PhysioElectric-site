<?php
declare(strict_types=1);

namespace Admin;

/**
 * Secure image uploads for the admin panel.
 *
 *  - extension whitelist: jpg / jpeg / png / webp
 *  - real MIME validation with finfo (not just the extension)
 *  - 2 MB size cap
 *  - random file name (no user supplied names on disk)
 *  - stored in /uploads which has PHP execution disabled (.htaccess)
 */
final class UploadController
{
    private const MAX_BYTES   = 2 * 1024 * 1024; // 2 MB
    private const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'webp'];
    private const ALLOWED_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    public static function upload(): void
    {
        // CSRF token arrives in the X-CSRF-TOKEN header (see admin.js).
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
        if (!\Csrf::verify(is_string($token) ? $token : null)) {
            json_response(['ok' => false, 'message' => 'CSRF token missing'], 419);
        }

        if (empty($_FILES['image']) || !is_array($_FILES['image'])) {
            json_response(['ok' => false, 'message' => 'No file received.'], 400);
        }
        $file = $_FILES['image'];

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            json_response(['ok' => false, 'message' => 'Upload failed (code ' . (int) $file['error'] . ').'], 400);
        }

        $size = (int) $file['size'];
        if ($size <= 0 || $size > self::MAX_BYTES) {
            json_response(['ok' => false, 'message' => 'Image must be 2 MB or smaller.'], 400);
        }

        // 1) extension whitelist
        $ext = strtolower((string) pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXT, true)) {
            json_response(['ok' => false, 'message' => 'Only jpg, png or webp images are allowed.'], 400);
        }

        // 2) real MIME check (guards against renamed php shells etc.)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = (string) $finfo->file((string) $file['tmp_name']);
        if (!isset(self::ALLOWED_MIME[$mime])) {
            json_response(['ok' => false, 'message' => 'File content is not a valid image.'], 400);
        }

        // 3) webp sanity: confirm the file really decodes as an image
        $info = @getimagesize((string) $file['tmp_name']);
        if ($info === false) {
            json_response(['ok' => false, 'message' => 'Corrupted image file.'], 400);
        }

        // 4) store with a random name
        $dir  = BASE_PATH . '/uploads';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $name = date('Ymd-His') . '-' . bin2hex(random_bytes(8)) . '.' . self::ALLOWED_MIME[$mime];
        $dest = $dir . '/' . $name;

        if (!is_uploaded_file((string) $file['tmp_name'])) {
            json_response(['ok' => false, 'message' => 'Invalid upload source.'], 400);
        }
        if (!move_uploaded_file((string) $file['tmp_name'], $dest)) {
            json_response(['ok' => false, 'message' => 'Could not store the file.'], 500);
        }
        @chmod($dest, 0644);

        json_response([
            'ok'  => true,
            'url' => '/uploads/' . $name,
            'name'=> $name,
        ]);
    }

    /** JSON list of already uploaded files (media picker). */
    public static function media(): void
    {
        $dir = BASE_PATH . '/uploads';
        $items = [];
        if (is_dir($dir)) {
            foreach (scandir($dir) ?: [] as $f) {
                if ($f === '.' || $f === '..' || str_starts_with($f, '.')) {
                    continue;
                }
                $full = $dir . '/' . $f;
                if (!is_file($full)) {
                    continue;
                }
                $items[] = [
                    'url'     => '/uploads/' . $f,
                    'name'    => $f,
                    'size'    => filesize($full) ?: 0,
                    'updated' => date('Y-m-d H:i:s', (int) filemtime($full)),
                ];
            }
            usort($items, fn($a, $b) => strcmp($b['updated'], $a['updated']));
            $items = array_slice($items, 0, 60);
        }
        json_response(['ok' => true, 'items' => $items]);
    }
}
