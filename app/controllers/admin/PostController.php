<?php
declare(strict_types=1);

namespace Admin;

final class PostController
{
    public static function index(): void
    {
        $posts = \PostModel::all();
        admin_view('posts/list', ['posts' => $posts]);
    }

    public static function createForm(): void
    {
        admin_view('posts/form', [
            'post' => self::blank(),
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
            $_SESSION['form_old'] = self::old($_POST);
            redirect('/admin/posts/create');
        }
        $d  = self::payload($_POST);
        $id = \PostModel::create($d);
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
            $_SESSION['form_old'] = self::old($_POST);
            redirect('/admin/posts/' . $id . '/edit');
        }
        \PostModel::update(array_merge(self::payload($_POST), ['id' => $id]));
        flash('success', t('admin.saved'));
        redirect('/admin/posts');
    }

    public static function delete(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $post = \PostModel::byId($id);
        if ($post !== null) {
            self::removeImageFile((string) ($post['image'] ?? ''));
            \PostModel::delete($id);
        }
        flash('success', t('admin.deleted'));
        redirect('/admin/posts');
    }

    // ------------------------------------------------------------------

    private static function blank(): array
    {
        if (!empty($_SESSION['form_old']) && is_array($_SESSION['form_old'])) {
            $old = $_SESSION['form_old'];
            unset($_SESSION['form_old']);
            return $old;
        }
        return [
            'id' => 0, 'title_fa' => '', 'title_en' => '', 'slug_fa' => '', 'slug_en' => '',
            'excerpt_fa' => '', 'excerpt_en' => '', 'content_fa' => '', 'content_en' => '',
            'image' => '', 'meta_title_fa' => '', 'meta_title_en' => '',
            'meta_desc_fa' => '', 'meta_desc_en' => '', 'status' => 'draft', 'published_at' => '',
        ];
    }

    private static function old(array $post): array
    {
        $base = self::blankRaw();
        foreach ($base as $k => $v) {
            if (array_key_exists($k, $post)) {
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
        if ($slugEn === '' || $slugFa === '') {
            return t('admin.fillAll');
        }
        if (\PostModel::slugTaken($slugEn, $exceptId) || \PostModel::slugTaken($slugFa, $exceptId)) {
            return t('admin.slugTaken');
        }
        return null;
    }

    private static function normalizeSlug(string $s): string
    {
        $s = strtolower(trim($s));
        $s = (string) preg_replace('/[^a-z0-9]+/', '-', $s);
        $s = (string) trim($s, '-');
        return mb_substr($s, 0, 150);
    }

    /**
     * @return array<string,mixed>
     */
    private static function payload(array $p): array
    {
        $slugEn = self::normalizeSlug((string) ($p['slug_en'] ?? ''));
        $slugFa = self::normalizeSlug((string) ($p['slug_fa'] ?? '')) ?: $slugEn;
        $pub = (string) ($p['published_at'] ?? '');
        $status = ($p['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        $pubTs = $status === 'published' && $pub === '' ? date('Y-m-d H:i:s') : ($pub !== '' ? date('Y-m-d H:i:s', strtotime($pub) ?: time()) : '');

        return [
            'title_fa'    => trim((string) ($p['title_fa'] ?? '')),
            'title_en'    => trim((string) ($p['title_en'] ?? '')),
            'slug_fa'     => $slugFa,
            'slug_en'     => $slugEn,
            'excerpt_fa'  => trim((string) ($p['excerpt_fa'] ?? '')),
            'excerpt_en'  => trim((string) ($p['excerpt_en'] ?? '')),
            'content_fa'  => \HtmlSanitizer::clean((string) ($p['content_fa'] ?? '')),
            'content_en'  => \HtmlSanitizer::clean((string) ($p['content_en'] ?? '')),
            'image'       => self::safeImagePath((string) ($p['image'] ?? '')),
            'meta_title_fa' => trim((string) ($p['meta_title_fa'] ?? '')),
            'meta_title_en' => trim((string) ($p['meta_title_en'] ?? '')),
            'meta_desc_fa'  => trim((string) ($p['meta_desc_fa'] ?? '')),
            'meta_desc_en'  => trim((string) ($p['meta_desc_en'] ?? '')),
            'status'      => $status,
            'published_at'=> $pubTs !== '' ? $pubTs : null,
        ];
    }

    /** Only accept /uploads/... paths we own. */
    public static function safeImagePath(string $path): ?string
    {
        $path = trim($path);
        if ($path === '') {
            return null;
        }
        if (!str_starts_with($path, '/uploads/') || str_contains($path, '..')) {
            return null;
        }
        return $path;
    }

    public static function removeImageFile(string $path): void
    {
        if ($path === '' || !str_starts_with($path, '/uploads/')) {
            return;
        }
        $file = BASE_PATH . '/' . $path;
        $real = realpath($file);
        $root = realpath(BASE_PATH . '/uploads');
        if ($real !== false && $root !== false
            && str_starts_with($real, $root . DIRECTORY_SEPARATOR)
            && is_file($real)) {
            @unlink($real);
        }
    }
}
