<?php
declare(strict_types=1);

namespace Admin;

final class ProjectController
{
    /** Per-form old-input key (was shared with posts -> wrong data leak). */
    private const OLD_KEY = 'form_old_projects';

    private const LIMITS = [
        'title_fa'      => 255,
        'title_en'      => 255,
        'slug_fa'       => 255,
        'slug_en'       => 255,
        'short_desc_fa' => 600,
        'short_desc_en' => 600,
        'image'         => 255,
        'tech_tags'     => 500,
        'meta_title_fa' => 255,
        'meta_title_en' => 255,
        'meta_desc_fa'  => 500,
        'meta_desc_en'  => 500,
    ];

    public static function index(): void
    {
        $projects = \ProjectModel::allWithCategory();
        admin_view('projects/list', ['projects' => $projects]);
    }

    public static function createForm(): void
    {
        admin_view('projects/form', [
            'project'    => self::blank(),
            'categories' => \CategoryModel::active(),
        ]);
    }

    public static function editForm(int $id): void
    {
        $project = \ProjectModel::byId($id);
        if ($project === null) {
            flash('error', t('admin.noRows'));
            redirect('/admin/projects');
        }
        admin_view('projects/form', [
            'project'    => $project,
            'categories' => \CategoryModel::active(),
        ]);
    }

    public static function create(): void
    {
        $errors = self::validate($_POST, 0);
        if ($errors !== null) {
            flash('error', $errors);
            $_SESSION[self::OLD_KEY] = self::old($_POST);
            redirect('/admin/projects/create');
        }
        try {
            $id = \ProjectModel::create(self::payload($_POST));
        } catch (\PDOException $e) {
            self::handleWriteError($e, '/admin/projects/create');
        }
        \Security::audit('project.created', ['id' => $id]);
        flash('success', t('admin.created'));
        redirect('/admin/projects/' . $id . '/edit');
    }

    public static function update(int $id): void
    {
        $project = \ProjectModel::byId($id);
        if ($project === null) {
            flash('error', t('admin.noRows'));
            redirect('/admin/projects');
        }
        $errors = self::validate($_POST, $id);
        if ($errors !== null) {
            flash('error', $errors);
            $_SESSION[self::OLD_KEY] = self::old($_POST);
            redirect('/admin/projects/' . $id . '/edit');
        }
        try {
            \ProjectModel::update(array_merge(self::payload($_POST), ['id' => $id]));
        } catch (\PDOException $e) {
            self::handleWriteError($e, '/admin/projects/' . $id . '/edit');
        }
        \Security::audit('project.updated', ['id' => $id]);
        flash('success', t('admin.saved'));
        redirect('/admin/projects');
    }

    public static function delete(): void
    {
        $id      = (int) ($_POST['id'] ?? 0);
        $project = $id > 0 ? \ProjectModel::byId($id) : null;
        if ($project === null) {
            flash('error', t('admin.noRows'));
            redirect('/admin/projects');
        }
        PostController::removeImageFile((string) ($project['image'] ?? ''));
        \ProjectModel::delete($id);
        \Security::audit('project.deleted', ['id' => $id]);
        flash('success', t('admin.deleted'));
        redirect('/admin/projects');
    }

    /** @return never */
    private static function handleWriteError(\PDOException $e, string $backTo): never
    {
        $msg = $e->getMessage();
        if (str_contains($msg, 'Duplicate entry') || ($e->getCode() === '23000')) {
            \Security::audit('project.slug_conflict');
            flash('error', t('admin.slugTaken'));
        } elseif (str_contains($msg, 'Data too long')
            || ($e->getCode() === '22001')
            || str_contains($msg, 'foreign key')) {
            \Security::audit('project.write_rejected', ['code' => (string) $e->getCode()]);
            flash('error', t('admin.fillAll'));
        } else {
            error_log('[PE] project write failed: ' . $msg);
            \Security::audit('project.write_failed', ['code' => (string) $e->getCode()]);
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
            'id' => 0, 'category_id' => '1',
            'title_fa' => '', 'title_en' => '', 'slug_fa' => '', 'slug_en' => '',
            'short_desc_fa' => '', 'short_desc_en' => '',
            'content_fa' => '', 'content_en' => '',
            'image' => '', 'tech_tags' => '',
            'meta_title_fa' => '', 'meta_title_en' => '',
            'meta_desc_fa' => '', 'meta_desc_en' => '',
            'status' => 'draft', 'sort_order' => '0',
        ];
    }

    /**
     * @return string|null
     */
    private static function validate(array $p, int $exceptId): ?string
    {
        if (trim((string) ($p['title_fa'] ?? '')) === '') {
            return t('admin.fillAll');
        }
        $catId = (int) ($p['category_id'] ?? 0);
        if ($catId <= 0 || \CategoryModel::byId($catId) === null) {
            return t('admin.fillAll');
        }
        $slugEn = self::normalizeSlug((string) ($p['slug_en'] ?? ''));
        $slugFa = self::normalizeSlug((string) ($p['slug_fa'] ?? '')) ?: $slugEn;
        if ($slugEn === '' || $slugFa === '' || !is_valid_slug($slugEn) || !is_valid_slug($slugFa)) {
            return t('admin.fillAll');
        }
        if (\ProjectModel::slugTaken($slugEn, $exceptId) || \ProjectModel::slugTaken($slugFa, $exceptId)) {
            return t('admin.slugTaken');
        }
        return null;
    }

    public static function normalizeSlug(string $s): string
    {
        return PostController::normalizeSlug($s);
    }

    /**
     * @return array<string,mixed>
     */
    private static function payload(array $p): array
    {
        $slugEn = self::normalizeSlug((string) ($p['slug_en'] ?? ''));
        $slugFa = self::normalizeSlug((string) ($p['slug_fa'] ?? '')) ?: $slugEn;

        $out = [
            'category_id'   => (int) ($p['category_id'] ?? 0),
            'title_fa'      => str_cap(trim((string) ($p['title_fa'] ?? '')), self::LIMITS['title_fa']),
            'title_en'      => str_cap(trim((string) ($p['title_en'] ?? '')), self::LIMITS['title_en']),
            'slug_fa'       => $slugFa,
            'slug_en'       => $slugEn,
            'short_desc_fa' => str_cap(trim((string) ($p['short_desc_fa'] ?? '')), self::LIMITS['short_desc_fa']),
            'short_desc_en' => str_cap(trim((string) ($p['short_desc_en'] ?? '')), self::LIMITS['short_desc_en']),
            'content_fa'    => \HtmlSanitizer::clean((string) ($p['content_fa'] ?? '')),
            'content_en'    => \HtmlSanitizer::clean((string) ($p['content_en'] ?? '')),
            'image'         => PostController::safeImagePath((string) ($p['image'] ?? '')),
            'tech_tags'     => str_cap(trim((string) ($p['tech_tags'] ?? '')), self::LIMITS['tech_tags']),
            'meta_title_fa' => str_cap(trim((string) ($p['meta_title_fa'] ?? '')), self::LIMITS['meta_title_fa']),
            'meta_title_en' => str_cap(trim((string) ($p['meta_title_en'] ?? '')), self::LIMITS['meta_title_en']),
            'meta_desc_fa'  => str_cap(trim((string) ($p['meta_desc_fa'] ?? '')), self::LIMITS['meta_desc_fa']),
            'meta_desc_en'  => str_cap(trim((string) ($p['meta_desc_en'] ?? '')), self::LIMITS['meta_desc_en']),
            'status'        => (($p['status'] ?? 'draft') === 'published') ? 'published' : 'draft',
            'sort_order'    => (int) ($p['sort_order'] ?? 0),
        ];
        return $out;
    }
}
