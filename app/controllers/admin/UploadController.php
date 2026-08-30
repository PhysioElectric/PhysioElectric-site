<?php
declare(strict_types=1);

namespace Admin;

/**
 * Secure image uploads for the admin panel.
 *
 *  - `image` must be a single file (an `image[]` body is rejected cleanly)
 *  - is_uploaded_file() is verified before the bytes are ever touched
 *  - the size is measured on disk, not taken from the client's claim
 *  - extension whitelist: jpg / jpeg / png / webp
 *  - real MIME validation with finfo (not just the extension)
 *  - getimagesize() must decode it, and the pixel dimensions are capped
 *  - when GD is available the image is RE-ENCODED: that strips EXIF and any
 *    bytes appended after the image data (polyglot payloads)
 *  - 2 MB size cap, random file name, 0644, /uploads has PHP disabled
 *  - a hard cap on the number of stored files stops disk-exhaustion abuse
 */
final class UploadController
{
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
            \Security::audit('upload.csrf_rejected');
            self::fail('CSRF token missing', 403, 'csrf');
        }

        if (empty($_FILES['image']) || !is_array($_FILES['image'])) {
            self::fail('No file received.', 400, 'no_file');
        }
        $file = $_FILES['image'];

        // Reject `image[]`-style bodies before any array->string cast.
        if (isset($file['name']) && is_array($file['name'])) {
            self::fail('Only a single file can be uploaded at a time.', 400, 'multi_file');
        }
        foreach (['name', 'tmp_name'] as $k) {
            if (!isset($file[$k]) || !is_string($file[$k])) {
                self::fail('Malformed upload.', 400, 'malformed');
            }
        }

        $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err !== UPLOAD_ERR_OK) {
            $msg = match ($err) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Image is larger than the server limit.',
                UPLOAD_ERR_PARTIAL   => 'Upload was interrupted. Please try again.',
                UPLOAD_ERR_NO_FILE   => 'No file received.',
                UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'Server could not store the upload.',
                UPLOAD_ERR_EXTENSION => 'Upload blocked by a PHP extension.',
                default              => 'Upload failed.',
            };
            self::fail($msg, 400, 'upload_err_' . $err);
        }

        $tmp = (string) $file['tmp_name'];

        // 0) it really came through the HTTP upload mechanism
        if (!is_uploaded_file($tmp)) {
            \Security::audit('upload.not_uploaded_file');
            self::fail('Invalid upload source.', 400, 'not_uploaded');
        }

        // 1) real size, measured on disk
        clearstatcache(true, $tmp);
        $size = @filesize($tmp);
        $maxBytes = \Config::maxUploadBytes();
        if ($size === false || $size <= 0) {
            self::fail('Could not read the uploaded file.', 400, 'empty_file');
        }
        if ($size > $maxBytes) {
            self::fail('Image must be ' . self::humanBytes($maxBytes) . ' or smaller.', 413, 'too_large');
        }

        // 2) extension whitelist
        $ext = strtolower((string) pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXT, true)) {
            self::fail('Only jpg, png or webp images are allowed.', 415, 'bad_extension');
        }

        // 3) real MIME check (guards against renamed php shells etc.)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = (string) $finfo->file($tmp);
        if (!isset(self::ALLOWED_MIME[$mime])) {
            \Security::audit('upload.bad_mime', ['mime' => $mime, 'name' => self::safeName((string) $file['name'])]);
            self::fail('File content is not a valid image.', 415, 'bad_mime');
        }

        // 4) it must actually decode, and stay within sane dimensions
        $info = @getimagesize($tmp);
        if ($info === false || !isset($info[0], $info[1])) {
            self::fail('Corrupted image file.', 415, 'not_an_image');
        }
        [$w, $h] = [(int) $info[0], (int) $info[1]];
        $maxEdge = \Config::maxImageEdge();
        if ($w < 1 || $h < 1 || $w > $maxEdge || $h > $maxEdge) {
            self::fail('Image dimensions are out of range (max ' . $maxEdge . 'px per side).', 415, 'bad_dimensions');
        }
        // Decompression-bomb guard: pixels, not bytes.
        if (($w * $h) > 40_000_000) {
            \Security::audit('upload.pixel_bomb', ['w' => $w, 'h' => $h]);
            self::fail('Image has too many pixels.', 415, 'pixel_bomb');
        }

        // 5) destination + quota
        $dir = BASE_PATH . '/uploads';
        if (!is_dir($dir) && !@mkdir($dir, 0770, true) && !is_dir($dir)) {
            self::fail('Could not store the file.', 500, 'no_dir');
        }
        if (self::countUploads($dir) >= \Config::maxUploads()) {
            \Security::audit('upload.quota_reached');
            self::fail('Media library is full. Delete unused images first.', 507, 'quota');
        }

        $name = date('Ymd-His') . '-' . bin2hex(random_bytes(8)) . '.' . self::ALLOWED_MIME[$mime];
        $dest = $dir . '/' . $name;

        // 6) store. Re-encode when GD can handle the type so that EXIF and
        //    any trailing polyglot bytes are dropped.
        if (!self::storeImage($tmp, $dest, self::ALLOWED_MIME[$mime])) {
            \Security::audit('upload.store_failed');
            self::fail('Could not store the file.', 500, 'store_failed');
        }
        @chmod($dest, 0644);

        \Security::audit('upload.stored', ['name' => $name, 'bytes' => (int) @filesize($dest)]);

        json_response([
            'ok'   => true,
            'url'  => '/uploads/' . $name,
            'name' => $name,
            'width'  => $w,
            'height' => $h,
        ]);
    }

    /** JSON list of already uploaded files (media picker). */
    public static function media(): void
    {
        $dir   = BASE_PATH . '/uploads';
        $items = [];
        if (is_dir($dir)) {
            foreach (scandir($dir) ?: [] as $f) {
                if (!is_string($f) || $f === '.' || $f === '..' || str_starts_with($f, '.')) {
                    continue;
                }
                // Only real image files belong in the picker (index.html used
                // to show up here and could be chosen as a post image).
                if (!preg_match('/\.(jpe?g|png|webp)$/i', $f)) {
                    continue;
                }
                $full = $dir . '/' . $f;
                if (!is_file($full)) {
                    continue;
                }
                $mtime = @filemtime($full);
                $items[] = [
                    'url'     => '/uploads/' . $f,
                    'name'    => $f,
                    'size'    => (int) (@filesize($full) ?: 0),
                    'updated' => date('Y-m-d H:i:s', $mtime === false ? 0 : $mtime),
                    '_ts'     => $mtime === false ? 0 : $mtime,
                ];
            }
            usort($items, static fn(array $a, array $b): int => $b['_ts'] <=> $a['_ts']);
            $items = array_slice($items, 0, 60);
            foreach ($items as &$it) {
                unset($it['_ts']);
            }
            unset($it);
        }
        json_response(['ok' => true, 'items' => $items]);
    }

    // ------------------------------------------------------------------

    /**
     * Write the image to $dest. Re-encodes through GD when possible.
     */
    private static function storeImage(string $src, string $dest, string $type): bool
    {
        $img = self::loadImage($src, $type);
        if ($img instanceof \GdImage) {
            $ok = false;
            if ($type === 'jpg') {
                $ok = imagejpeg($img, $dest, 88);
            } elseif ($type === 'png') {
                imagealphablending($img, false);
                imagesavealpha($img, true);
                $ok = imagepng($img, $dest, 6);
            } elseif ($type === 'webp' && function_exists('imagewebp')) {
                $ok = imagewebp($img, $dest, 88);
            }
            imagedestroy($img);
            if ($ok) {
                return true;
            }
            // Fall through to a plain move if the encoder refused.
        }

        return move_uploaded_file($src, $dest);
    }

    private static function loadImage(string $src, string $type): ?\GdImage
    {
        if (!function_exists('imagecreatefromstring')) {
            return null;
        }
        $img = match ($type) {
            'jpg'  => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($src) : false,
            'png'  => function_exists('imagecreatefrompng')  ? @imagecreatefrompng($src)  : false,
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($src) : false,
            default=> false,
        };
        return $img instanceof \GdImage ? $img : null;
    }

    private static function countUploads(string $dir): int
    {
        $n = 0;
        foreach (scandir($dir) ?: [] as $f) {
            if (!is_string($f) || $f === '.' || $f === '..' || str_starts_with($f, '.')) {
                continue;
            }
            if (is_file($dir . '/' . $f)) {
                $n++;
            }
        }
        return $n;
    }

    private static function humanBytes(int $b): string
    {
        return $b >= 1048576
            ? rtrim(rtrim(number_format($b / 1048576, 1), '0'), '.') . ' MB'
            : (int) round($b / 1024) . ' KB';
    }

    /** Log-safe file name (no control chars, capped). */
    private static function safeName(string $name): string
    {
        return substr((string) preg_replace('/[^\x20-\x7E]/', '', $name), 0, 120);
    }

    /**
     * Delete a file that lives under /uploads, given its stored URL path
     * (e.g. "/uploads/foo.jpg" or "/uploads/attachments/foo.pdf").
     * Containment is enforced with realpath so a crafted value can never
     * escape the uploads directory. No-op for external URLs or missing files.
     */
    public static function removeUploadFile(string $urlPath): void
    {
        if ($urlPath === '' || !str_starts_with($urlPath, '/uploads/')) {
            return; // external URL (e.g. a CDN image) or empty - nothing on disk
        }
        $base = realpath(BASE_PATH . '/uploads');
        $full = realpath(BASE_PATH . str_replace('/', DIRECTORY_SEPARATOR, $urlPath));
        if ($base === false || $full === false) {
            return;
        }
        if (str_starts_with($full, $base . DIRECTORY_SEPARATOR) && is_file($full)) {
            @unlink($full);
        }
    }

    /** @return never */
    private static function fail(string $message, int $status, string $code): never
    {
        json_response(['ok' => false, 'message' => $message, 'code' => $code], $status);
    }
}
