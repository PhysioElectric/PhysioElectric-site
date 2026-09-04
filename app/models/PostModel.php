<?php
declare(strict_types=1);

final class PostModel
{
    /**
     * Published posts that are actually due. A post saved with a future
     * published_at used to appear on the site immediately.
     */
    public static function allPublished(?int $limit = null): array
    {
        $sql = 'SELECT * FROM posts
                WHERE status = "published"
                  AND (published_at IS NULL OR published_at <= NOW())
                ORDER BY published_at DESC, id DESC';
        if ($limit !== null) {
            $sql .= ' LIMIT ' . max(1, (int) $limit);
        }
        return Database::pdo()->query($sql)->fetchAll();
    }

    public static function bySlug(string $slug): ?array
    {
        $st = Database::pdo()->prepare(
            'SELECT * FROM posts WHERE (slug_fa = :slugFa OR slug_en = :slugEn) LIMIT 1'
        );
        $st->execute([':slugFa' => $slug, ':slugEn' => $slug]);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    public static function byId(int $id): ?array
    {
        $st = Database::pdo()->prepare('SELECT * FROM posts WHERE id = :id LIMIT 1');
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    public static function related(int $excludeId, int $limit = 3): array
    {
        $st = Database::pdo()->prepare(
            'SELECT * FROM posts
             WHERE status = "published" AND id != :id
               AND (published_at IS NULL OR published_at <= NOW())
             ORDER BY published_at DESC, id DESC LIMIT ' . max(1, (int) $limit)
        );
        $st->execute([':id' => $excludeId]);
        return $st->fetchAll();
    }

    // ---------------- admin: all rows (incl. drafts) ----------------
    public static function all(): array
    {
        return Database::pdo()->query('SELECT * FROM posts ORDER BY created_at DESC, id DESC')->fetchAll();
    }

    public static function countPublished(): int
    {
        return (int) Database::pdo()->query('SELECT COUNT(*) FROM posts WHERE status = "published"')->fetchColumn();
    }

    public static function slugTaken(string $slug, int $exceptId = 0): bool
    {
        $st = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM posts WHERE (slug_fa = :s1 OR slug_en = :s2) AND id != :id'
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
            'INSERT INTO posts
                (title_fa, title_en, slug_fa, slug_en,
                 excerpt_fa, excerpt_en, content_fa, content_en,
                 image, meta_title_fa, meta_title_en, meta_desc_fa, meta_desc_en,
                 status, published_at)
             VALUES
                (:tfa, :ten, :sfa, :sen,
                 :exfa, :exen, :cfa, :cen,
                 :img, :mtfa, :mten, :mdfa, :mden,
                 :status, :pub)'
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
            'UPDATE posts SET
                title_fa = :tfa, title_en = :ten, slug_fa = :sfa, slug_en = :sen,
                excerpt_fa = :exfa, excerpt_en = :exen,
                content_fa = :cfa, content_en = :cen,
                image = :img,
                meta_title_fa = :mtfa, meta_title_en = :mten,
                meta_desc_fa = :mdfa, meta_desc_en = :mden,
                status = :status, published_at = :pub
             WHERE id = :id'
        );
        $params = self::bindParams($d);
        $params[':id'] = (int) $d['id'];
        return $st->execute($params);
    }

    public static function delete(int $id): bool
    {
        $st = Database::pdo()->prepare('DELETE FROM posts WHERE id = :id');
        return $st->execute([':id' => $id]);
    }

    /**
     * @param array<string,mixed> $d
     * @return array<string,mixed>
     */
    private static function bindParams(array $d): array
    {
        $publishedAt = ($d['published_at'] ?? '') !== '' && ($d['published_at'] ?? '') !== null
            ? (string) $d['published_at']
            : null;
        return [
            ':tfa'    => (string) $d['title_fa'],
            ':ten'    => (string) $d['title_en'],
            ':sfa'    => (string) $d['slug_fa'],
            ':sen'    => (string) $d['slug_en'],
            ':exfa'   => (string) ($d['excerpt_fa'] ?? ''),
            ':exen'   => (string) ($d['excerpt_en'] ?? ''),
            ':cfa'    => (string) ($d['content_fa'] ?? ''),
            ':cen'    => (string) ($d['content_en'] ?? ''),
            ':img'    => $d['image'] !== null && $d['image'] !== '' ? (string) $d['image'] : null,
            ':mtfa'   => ($d['meta_title_fa'] ?? '') !== '' ? (string) $d['meta_title_fa'] : null,
            ':mten'   => ($d['meta_title_en'] ?? '') !== '' ? (string) $d['meta_title_en'] : null,
            ':mdfa'   => ($d['meta_desc_fa'] ?? '') !== '' ? (string) $d['meta_desc_fa'] : null,
            ':mden'   => ($d['meta_desc_en'] ?? '') !== '' ? (string) $d['meta_desc_en'] : null,
            ':status' => ($d['status'] ?? 'draft') === 'published' ? 'published' : 'draft',
            ':pub'    => $publishedAt,
        ];
    }
}
