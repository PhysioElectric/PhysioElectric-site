<?php
declare(strict_types=1);

final class CategoryModel
{
    /** @return array<int,array<string,mixed>> active categories ordered for nav */
    public static function active(): array
    {
        $st = Database::pdo()->query(
            'SELECT c.*, (SELECT COUNT(*) FROM projects p
                          WHERE p.category_id = c.id AND p.status = "published") AS published_count
             FROM categories c
             WHERE c.is_active = 1
             ORDER BY c.sort_order ASC, c.id ASC'
        );
        return $st->fetchAll();
    }

    public static function bySlug(string $slug): ?array
    {
        $st = Database::pdo()->prepare(
            'SELECT c.*, (SELECT COUNT(*) FROM projects p
                          WHERE p.category_id = c.id AND p.status = "published") AS published_count
             FROM categories c
             WHERE c.slug = :slug AND c.is_active = 1
             LIMIT 1'
        );
        $st->execute([':slug' => $slug]);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    public static function byId(int $id): ?array
    {
        $st = Database::pdo()->prepare('SELECT * FROM categories WHERE id = :id AND is_active = 1 LIMIT 1');
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    public static function name(?array $cat, string $field): string
    {
        if ($cat === null) {
            return '';
        }
        return L($cat, $field === 'name' ? 'name' : $field, '—');
    }
}
