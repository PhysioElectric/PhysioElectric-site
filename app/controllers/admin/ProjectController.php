<?php
declare(strict_types=1);

namespace Admin;

final class ProjectController
{
    public static function index(): void
    {
        $projects = \ProjectModel::allWithCategory();
        admin_view('projects/list', ['projects' => $projects]);
    }

    public static function createForm(): void
    {
        $post = self::blank();
        $categories = \CategoryModel::active();
        admin_view('projects/form', [
            'project'    => $post,
            'categories' => $categories,
        ]);
    }

    public static function editForm(int $id): void
    {
        $project = \ProjectModel::byId($id);
        if ($project === null) {
            flash('error', t('admin.noRows'));
            redirect('/admin/projects');
        }
        $categories = \CategoryModel::active();
        admin_view('projects/form', [
            'project'    => $project,
            'categories' => $categories,
        ]);
    }

    public static function create(): void
    {
        $errors = self::validate($_POST, 0);
        if ($errors !== null) {
            flash('error', $errors);
            $_SESSION['form_old'] = self::old($_POST);
            redirect('/admin/projects/create');
        }
        $d  = self::payload($_POST);
        $id = \ProjectModel::create($d);
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
            $_SESSION['form_old'] = self::old($_POST);
            redirect('/admin/projects/' . $id . '/edit');
        }
        \ProjectModel::update(array_merge(self::payload($_POST), ['id' => $id]));
        flash('success', t('admin.saved'));
        redirect('/admin/projects');
    }

    public static function delete(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $project = \ProjectModel::byId($id);
        if ($project !== null) {
            PostController::removeImageFile((string) ($project['image'] ?? ''));
            \ProjectModel::delete($id);
        }
        flash('success', t('admin.deleted'));
        redirect('/admin/projects');
    }

    // ------------------------------------------------------------------

    private static function blank(): array
    {
        if (!empty($_SESSION['form_old']) && is_array($_SESSION['form_old'])) {
            $old = $_SESSION['form_old'];
            unset($_SESSION['form_old']);
            return $old;
        }
        return self::blankRaw();
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
        if ($slugEn === '' || $slugFa === '') {
            return t('admin.fillAll');
        }
        if (\ProjectModel::slugTaken($slugEn, $exceptId) || \ProjectModel::slugTaken($slugFa, $exceptId)) {
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
        return [
            'category_id'   => (int) ($p['category_id'] ?? 0),
            'title_fa'      => trim((string) ($p['title_fa'] ?? '')),
            'title_en'      => trim((string) ($p['title_en'] ?? '')),
            'slug_fa'       => $slugFa,
            'slug_en'       => $slugEn,
            'short_desc_fa' => trim((string) ($p['short_desc_fa'] ?? '')),
            'short_desc_en' => trim((string) ($p['short_desc_en'] ?? '')),
            'content_fa'    => \HtmlSanitizer::clean((string) ($p['content_fa'] ?? '')),
            'content_en'    => \HtmlSanitizer::clean((string) ($p['content_en'] ?? '')),
            'image'         => PostController::safeImagePath((string) ($p['image'] ?? '')),
            'tech_tags'     => trim((string) ($p['tech_tags'] ?? '')),
            'meta_title_fa' => trim((string) ($p['meta_title_fa'] ?? '')),
            'meta_title_en' => trim((string) ($p['meta_title_en'] ?? '')),
            'meta_desc_fa'  => trim((string) ($p['meta_desc_fa'] ?? '')),
            'meta_desc_en'  => trim((string) ($p['meta_desc_en'] ?? '')),
            'status'        => ($p['status'] ?? 'draft') === 'published' ? 'published' : 'draft',
            'sort_order'    => (int) ($p['sort_order'] ?? 0),
        ];
    }
}
