<?php
declare(strict_types=1);

/**
 * Team members shown on the About page.
 * Managed from the admin panel (add / edit / remove, with photo upload).
 */
final class TeamModel
{
    /** Members in display order. Empty when the table is not migrated yet. */
    public static function all(): array
    {
        if (!Database::tableExists('team_members')) {
            return [];
        }
        return Database::pdo()->query(
            'SELECT * FROM team_members ORDER BY sort_order ASC, id ASC'
        )->fetchAll();
    }

    public static function byId(int $id): ?array
    {
        if (!Database::tableExists('team_members')) {
            return null;
        }
        $st = Database::pdo()->prepare('SELECT * FROM team_members WHERE id = :id LIMIT 1');
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    /**
     * @param array{name_fa:string,name_en:string,role_fa:string,role_en:string,
     *              desc_fa:string,desc_en:string,image:string,sort_order:int} $m
     */
    public static function create(array $m): int
    {
        $st = Database::pdo()->prepare(
            'INSERT INTO team_members
                (name_fa, name_en, role_fa, role_en, desc_fa, desc_en, image, sort_order)
             VALUES
                (:nameFa, :nameEn, :roleFa, :roleEn, :descFa, :descEn, :image, :sort)'
        );
        $st->execute(self::bind($m));
        return (int) Database::pdo()->lastInsertId();
    }

    /** @param array<string,mixed> $m */
    public static function update(int $id, array $m): bool
    {
        $st = Database::pdo()->prepare(
            'UPDATE team_members SET
                name_fa = :nameFa, name_en = :nameEn,
                role_fa = :roleFa, role_en = :roleEn,
                desc_fa = :descFa, desc_en = :descEn,
                image = :image, sort_order = :sort
             WHERE id = :id'
        );
        return $st->execute(self::bind($m) + [':id' => $id]);
    }

    public static function delete(int $id): bool
    {
        $st = Database::pdo()->prepare('DELETE FROM team_members WHERE id = :id');
        $st->execute([':id' => $id]);
        return $st->rowCount() > 0;
    }

    /** @param array<string,mixed> $m  @return array<string,mixed> */
    private static function bind(array $m): array
    {
        return [
            ':nameFa' => $m['name_fa'],
            ':nameEn' => $m['name_en'],
            ':roleFa' => $m['role_fa'],
            ':roleEn' => $m['role_en'],
            ':descFa' => $m['desc_fa'],
            ':descEn' => $m['desc_en'],
            ':image'  => $m['image'],
            ':sort'   => $m['sort_order'],
        ];
    }
}
