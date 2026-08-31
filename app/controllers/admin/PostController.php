<?php
declare(strict_types=1);

namespace Admin;

final class PostController
{
    /** Per-form old-input key (was shared with projects -> wrong data leak). */
    private const OLD_KEY = 'form_old_posts';

    // Column limits from db/init.sql, enforced before the query so an
    // oversized field cannot turn into a SQLSTATE[22001] 500.
    private const LIMITS = [
        'title_fa'      => 255,
        'title_en'      => 255,
        'slug_fa'       => 255,
        'slug_en'       => 255,
        'excerpt_fa'    => 600,
        'excerpt_en'    => 600,
        'image'         => 255,
        'meta_title_fa' => 255,
        'meta_title_en' => 255,
        'meta_desc_fa'  => 500,
        'meta_desc_en'  => 500,
    ];

    public static function index(): void
    {
        $posts = \PostModel::all();
        admin_view('posts/list', ['posts' => $posts]);
    }

    public static function createForm(): void
    {
        admin_view('posts/form', [
            'post'       => self::blank(),
            'formErrors' => null,
        ]);
    }

    public static function editForm(int $id): void
    {
        $post = \PostModel::byId($id);
        if ($post === null) {
            flash('error', t('admin.noRows'));
            redirect('/admin/posts');
        }
        admin_view('posts/form', ['post' => $post, 'formErrors' => null]);
    }

    public static function create(): void
    {
        $errors = self::validate($_POST, 0);
        if ($errors !== null) {
            flash('error', $errors);
            $_SESSION[self::OLD_KEY] = self::old($_POST);
            redirect('/admin/posts/create');
        }
        try {
            $id = \PostModel::create(self::payload($_POST));
        } catch (\PDOException $e) {
            self::handleWriteError($e, '/admin/posts/create');
        }
        \Security::audit('post.created', ['id' => $id]);
        flash('success', t('admin.created'));
        redirect('/admin/posts/' . $id . '/edit');
    }

    public static function update(int $id): void
    {
        $post = \PostModel::byId($id);
        if ($post === null) {
            flash('error', t('admin.noRows'));
            redirect('/admin/posts');
        }
        $errors = self::validate($_POST, $id);
        if ($errors !== null) {
            flash('error', $errors);
            $_SESSION[self::OLD_KEY] = self::old($_POST);
            redirect('/admin/posts/' . $id . '/edit');
        }
        try {
            \PostModel::update(array_merge(self::payload($_POST, $post), ['id' => $id]));
        } catch (\PDOException $e) {
            self::handleWriteError($e, '/admin/posts/' . $id . '/edit');
        }
        \Security::audit('post.updated', ['id' => $id]);
        flash('success', t('admin.saved'));
        redirect('/admin/posts');
    }

    public static function delete(): void
    {
        $id   = (int) ($_POST['id'] ?? 0);
        $post = $id > 0 ? \PostModel::byId($id) : null;
        if ($post === null) {
            flash('error', t('admin.noRows'));
            redirect('/admin/posts');
        }
        self::removeImageFile((string) ($post['image'] ?? ''));
        \PostModel::delete($id);
        \Security::audit('post.deleted', ['id' => $id]);
        flash('success', t('admin.deleted'));
        redirect('/admin/posts');
    }

    /**
     * Turn a write failure into the message an editor can act on instead of
     * a bare 500. The UNIQUE slug indexes are the common trigger.
     * @return never
     */
    private static function handleWriteError(\PDOException $e, string $backTo): never
    {
        $msg = $e->getMessage();
        if (str_contains($msg, 'Duplicate entry') || ($e->getCode() === '23000')) {
            \Security::audit('post.slug_conflict');
            flash('error', t('admin.slugTaken'));
        } elseif (str_contains($msg, 'Data too long') || ($e->getCode() === '22001')) {
            \Security::audit('post.field_too_long');
            flash('error', t('admin.fillAll'));
        } else {
            error_log('[PE] post write failed: ' . $msg);
            \Security::audit('post.write_failed', ['code' => (string) $e->getCode()]);
            flash('error', t('admin.noRows'));
        }
        redirect($backTo);
    }

    // ------------------------------------------------------------------

    private static function blank(): array
    {
        if (!empty($_SESSION[self::OLD_KEY]) && is_array($_SESSION[self::OLD_KEY])) {
            $old = $_SESSION[self::OLD_KEY];
            unset($_SESSION[self::OLD_KEY]);
            return array_merge(self::blankRaw(), $old);
        }
        return self::blankRaw();
    }

    private static function old(array $post): array
    {
        $base = self::blankRaw();
        foreach ($base as $k => $v) {
            if (array_key_exists($k, $post) && is_scalar($post[$k])) {
                $base[$k] = (string) $post[$k];
            }
        }
        return $base;
    }

    private static function blankRaw(): array
    {
        return [
            'id' => 0, 'title_fa' => '', 'title_en' => '', 'slug_fa' => '', 'slug_en' => '',
            'excerpt_fa' => '', 'excerpt_en' => '', 'content_fa' => '', 'content_en' => '',
            'image' => '', 'meta_title_fa' => '', 'meta_title_en' => '',
            'meta_desc_fa' => '', 'meta_desc_en' => '', 'status' => 'draft', 'published_at' => '',
        ];
    }

    /**
     * @return string|null error message or null when valid
     */
    private static function validate(array $p, int $exceptId): ?string
    {
        if (trim((string) ($p['title_fa'] ?? '')) === '') {
            return t('admin.fillAll');
        }
        $slugEn = self::normalizeSlug((string) ($p['slug_en'] ?? ''));
        $slugFa = self::normalizeSlug((string) ($p['slug_fa'] ?? '')) ?: $slugEn;
        if ($slugEn === '' || $slugFa === '' || !is_valid_slug($slugEn) || !is_valid_slug($slugFa)) {
            return t('admin.fillAll');
        }
        if (\PostModel::slugTaken($slugEn, $exceptId) || \PostModel::slugTaken($slugFa, $exceptId)) {
            return t('admin.slugTaken');
        }
        return null;
    }

    public static function normalizeSlug(string $s): string
    {
        $s = strtolower(trim($s));
        $s = (string) preg_replace('/[^a-z0-9]+/', '-', $s);
        $s = (string) trim($s, '-');
        return mb_substr($s, 0, 150);
    }

    /**
     * @param array<string,mixed> $p     submitted form
     * @param array<string,mixed>|null $existing current row (keeps published_at on unpublish)
     * @return array<string,mixed>
     */
    private static function payload(array $p, ?array $existing = null): array
    {
        $slugEn = self::normalizeSlug((string) ($p['slug_en'] ?? ''));
        $slugFa = self::normalizeSlug((string) ($p['slug_fa'] ?? '')) ?: $slugEn;
        $pub    = trim((string) ($p['published_at'] ?? ''));
        $status = (($p['status'] ?? 'draft') === 'published') ? 'published' : 'draft';

        if ($pub !== '') {
            $ts = strtotime($pub);
            $pubTs = $ts === false ? null : date('Y-m-d H:i:s', $ts);
        } elseif ($status === 'published') {
            // Newly published without an explicit date: stamp it now.
            $pubTs = date('Y-m-d H:i:s');
        } else {
            // Draft: remember the previous publish date instead of erasing it.
            $pubTs = ($existing['published_at'] ?? null) !== null && ($existing['published_at'] ?? '') !== ''
                ? (string) $existing['published_at']
                : null;
        }

        $out = [
            'title_fa'      => str_cap(trim((string) ($p['title_fa'] ?? '')), self::LIMITS['title_fa']),
            'title_en'      => str_cap(trim((string) ($p['title_en'] ?? '')), self::LIMITS['title_en']),
            'slug_fa'       => $slugFa,
            'slug_en'       => $slugEn,
            'excerpt_fa'    => str_cap(trim((string) ($p['excerpt_fa'] ?? '')), self::LIMITS['excerpt_fa']),
            'excerpt_en'    => str_cap(trim((string) ($p['excerpt_en'] ?? '')), self::LIMITS['excerpt_en']),
            'content_fa'    => \HtmlSanitizer::clean((string) ($p['content_fa'] ?? '')),
            'content_en'    => \HtmlSanitizer::clean((string) ($p['content_en'] ?? '')),
            'image'         => self::safeImagePath((string) ($p['image'] ?? '')),
            'meta_title_fa' => str_cap(trim((string) ($p['meta_title_fa'] ?? '')), self::LIMITS['meta_title_fa']),
            'meta_title_en' => str_cap(trim((string) ($p['meta_title_en'] ?? '')), self::LIMITS['meta_title_en']),
            'meta_desc_fa'  => str_cap(trim((string) ($p['meta_desc_fa'] ?? '')), self::LIMITS['meta_desc_fa']),
            'meta_desc_en'  => str_cap(trim((string) ($p['meta_desc_en'] ?? '')), self::LIMITS['meta_desc_en']),
            'status'        => $status,
            'published_at'  => $pubTs,
        ];

        foreach (self::LIMITS as $field => $max) {
            if (isset($out[$field]) && is_string($out[$field])) {
                $out[$field] = str_cap($out[$field], $max);
            }
        }
        return $out;
    }

    /**
     * Only accept a file we actually uploaded into /uploads.
     */
    public static function safeImagePath(string $path): ?string
    {
        $path = trim($path);
        if ($path === '') {
            return null;
        }
        if (!preg_match('#^/uploads/[A-Za-z0-9][A-Za-z0-9._-]{0,200}\.(jpg|jpeg|png|webp)$#i', $path)) {
            return null;
        }
        // Must exist on disk, so a stale/forged path never reaches the DB.
        $real = realpath(BASE_PATH . $path);
        $root = realpath(BASE_PATH . '/uploads');
        if ($real === false || $root === false
            || !str_starts_with($real, $root . DIRECTORY_SEPARATOR)
            || !is_file($real)) {
            return null;
        }
        return $path;
    }

    public static function removeImageFile(string $path): void
    {
        if ($path === '' || !str_starts_with($path, '/uploads/')) {
            return;
        }
        $file = BASE_PATH . '/' . ltrim($path, '/');
        $real = realpath($file);
        $root = realpath(BASE_PATH . '/uploads');
        if ($real !== false && $root !== false
            && str_starts_with($real, $root . DIRECTORY_SEPARATOR)
            && is_file($real)
            && preg_match('/\.(jpe?g|png|webp)$/i', $real)) {
            @unlink($real);
        }
    }
}
