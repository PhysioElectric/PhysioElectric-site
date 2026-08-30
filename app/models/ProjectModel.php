<?php
declare(strict_types=1);

final class ProjectModel
{
    private const SELECT =
        'SELECT p.*, c.slug AS category_slug, c.name_fa AS category_name_fa, c.name_en AS category_name_en,
                c.icon AS category_icon
         FROM projects p
         INNER JOIN categories c ON c.id = p.category_id';

    /** Published projects, newest first. */
    public static function allPublished(?int $limit = null): array
    {
        $sql = self::SELECT . '
                WHERE p.status = "published"
                ORDER BY p.sort_order ASC, p.created_at DESC, p.id DESC';
        if ($limit !== null) {
            $sql .= ' LIMIT ' . max(1, (int) $limit);
        }
        return Database::pdo()->query($sql)->fetchAll();
    }

    /** Published projects of one category slug. */
    public static function byCategory(string $categorySlug): array
    {
        $st = Database::pdo()->prepare(self::SELECT . '
                WHERE p.status = "published" AND c.slug = :slug
                ORDER BY p.sort_order ASC, p.created_at DESC, p.id DESC');
        $st->execute([':slug' => $categorySlug]);
        return $st->fetchAll();
    }

    public static function bySlug(string $slug): ?array
    {
        // Slugs are unique per language column; ASCII slugs are shared,
        // so check both columns.
        $st = Database::pdo()->prepare(self::SELECT . '
                WHERE (p.slug_fa = :slugFa OR p.slug_en = :slugEn)
                LIMIT 1');
        $st->execute([':slugFa' => $slug, ':slugEn' => $slug]);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    public static function byId(int $id): ?array
    {
        $st = Database::pdo()->prepare(self::SELECT . ' WHERE p.id = :id LIMIT 1');
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    /** Related: same category, published, excluding the current one. */
    public static function related(int $excludeId, int $categoryId, int $limit = 3): array
    {
        $st = Database::pdo()->prepare(self::SELECT . '
                WHERE p.status = "published" AND p.id != :id AND p.category_id = :cid
                ORDER BY p.sort_order ASC, p.created_at DESC
                LIMIT ' . (int) $limit);
        $st->execute([':id' => $excludeId, ':cid' => $categoryId]);
        return $st->fetchAll();
    }

    // ---------------- admin: all rows (incl. drafts) ----------------
    public static function allWithCategory(): array
    {
        $st = Database::pdo()->query(self::SELECT . ' ORDER BY p.created_at DESC, p.id DESC');
        return $st->fetchAll();
    }

    public static function countPublished(): int
    {
        return (int) Database::pdo()->query('SELECT COUNT(*) FROM projects WHERE status = "published"')->fetchColumn();
    }

    public static function slugTaken(string $slug, int $exceptId = 0): bool
    {
        $st = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM projects
             WHERE (slug_fa = :s1 OR slug_en = :s2) AND id != :id'
        );
        $st->execute([':s1' => $slug, ':s2' => $slug, ':id' => $exceptId]);
        return (int) $st->fetchColumn() > 0;
    }

    /**
     * @param array<string,mixed> $d
     */
    public static function create(array $d): int
    {
        $st = Database::pdo()->prepare(
            'INSERT INTO projects
                (category_id, title_fa, title_en, slug_fa, slug_en,
                 short_desc_fa, short_desc_en, content_fa, content_en,
                 image, tech_tags, meta_title_fa, meta_title_en,
                 meta_desc_fa, meta_desc_en, status, sort_order)
             VALUES
                (:cid, :tfa, :ten, :sfa, :sen,
                 :sdfa, :sden, :cfa, :cen,
                 :img, :tags, :mtfa, :mten,
                 :mdfa, :mden, :status, :sort)'
        );
        $st->execute(self::bindParams($d));
        return (int) Database::pdo()->lastInsertId();
    }

    /**
     * @param array<string,mixed> $d must include id
     */
    public static function update(array $d): bool
    {
        $st = Database::pdo()->prepare(
            'UPDATE projects SET
                category_id = :cid, title_fa = :tfa, title_en = :ten,
                slug_fa = :sfa, slug_en = :sen,
                short_desc_fa = :sdfa, short_desc_en = :sden,
                content_fa = :cfa, content_en = :cen,
                image = :img, tech_tags = :tags,
                meta_title_fa = :mtfa, meta_title_en = :mten,
                meta_desc_fa = :mdfa, meta_desc_en = :mden,
                status = :status, sort_order = :sort
             WHERE id = :id'
        );
        $params = self::bindParams($d);
        $params[':id'] = (int) $d['id'];
        return $st->execute($params);
    }

    public static function delete(int $id): bool
    {
        $st = Database::pdo()->prepare('DELETE FROM projects WHERE id = :id');
        return $st->execute([':id' => $id]);
    }

    /**
     * @param array<string,mixed> $d
     * @return array<string,mixed>
     */
    private static function bindParams(array $d): array
    {
        return [
            ':cid'    => (int) $d['category_id'],
            ':tfa'    => (string) $d['title_fa'],
            ':ten'    => (string) $d['title_en'],
            ':sfa'    => (string) $d['slug_fa'],
            ':sen'    => (string) $d['slug_en'],
            ':sdfa'   => (string) ($d['short_desc_fa'] ?? ''),
            ':sden'   => (string) ($d['short_desc_en'] ?? ''),
            ':cfa'    => (string) ($d['content_fa'] ?? ''),
            ':cen'    => (string) ($d['content_en'] ?? ''),
            ':img'    => $d['image'] !== null && $d['image'] !== '' ? (string) $d['image'] : null,
            ':tags'   => (string) ($d['tech_tags'] ?? ''),
            ':mtfa'   => ($d['meta_title_fa'] ?? '') !== '' ? (string) $d['meta_title_fa'] : null,
            ':mten'   => ($d['meta_title_en'] ?? '') !== '' ? (string) $d['meta_title_en'] : null,
            ':mdfa'   => ($d['meta_desc_fa'] ?? '') !== '' ? (string) $d['meta_desc_fa'] : null,
            ':mden'   => ($d['meta_desc_en'] ?? '') !== '' ? (string) $d['meta_desc_en'] : null,
            ':status' => ($d['status'] ?? 'draft') === 'published' ? 'published' : 'draft',
            ':sort'   => (int) ($d['sort_order'] ?? 0),
        ];
    }
}
