<?php
declare(strict_types=1);

/**
 * Admin user accounts. Introduced with the multi-admin / RBAC work
 * (CHANGES-SECURITY-2.md). All writes that carry a risk of locking the
 * panel out (deactivation, demotion, soft-delete) are guarded at the
 * controller level inside transactions; this layer only persists.
 */
final class UserModel
{
    /** Persian labels for the role ENUM (admin UI). */
    public const ROLE_LABELS = [
        'super_admin' => 'مدیر ارشد',
        'editor'      => 'ویرایشگر',
        'viewer'      => 'بیننده',
    ];

    /** @return array<int,array<string,mixed>> newest first */
    public static function all(): array
    {
        $st = Database::pdo()->query(
            'SELECT u.id, u.name, u.email, u.role, u.is_active,
                    u.force_password_change, u.created_by, u.last_login_at,
                    u.created_at, c.name AS created_by_name
             FROM users u
             LEFT JOIN users c ON c.id = u.created_by
             ORDER BY u.id ASC'
        );
        return $st->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public static function byId(int $id): ?array
    {
        $st = Database::pdo()->prepare(
            'SELECT id, name, email, role, is_active, force_password_change,
                    created_by, totp_enabled, last_login_at, created_at
             FROM users WHERE id = :id LIMIT 1'
        );
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    /** Full row including the password hash (auth-sensitive callers only). */
    public static function byIdWithHash(int $id): ?array
    {
        $st = Database::pdo()->prepare(
            'SELECT id, name, email, role, is_active, force_password_change,
                    password_hash, totp_enabled, totp_secret, created_by
             FROM users WHERE id = :id LIMIT 1'
        );
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    /** @return array<string,mixed>|null */
    public static function byEmail(string $email): ?array
    {
        $st = Database::pdo()->prepare(
            'SELECT id, name, email, role, is_active FROM users WHERE email = :email LIMIT 1'
        );
        $st->execute([':email' => mb_strtolower(trim($email))]);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    /**
     * @param array<string,mixed> $u name/email/password_hash/role/is_active/created_by
     */
    public static function create(array $u): int
    {
        $st = Database::pdo()->prepare(
            'INSERT INTO users (name, email, password_hash, role, is_active,
                                force_password_change, created_by)
             VALUES (:name, :email, :hash, :role, :active, 1, :createdBy)'
        );
        $st->execute([
            ':name'       => $u['name'],
            ':email'      => $u['email'],
            ':hash'       => $u['password_hash'],
            ':role'       => $u['role'],
            ':active'     => (int) ($u['is_active'] ?? 1),
            ':createdBy'  => $u['created_by'] ?? null,
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    /**
     * Name / role / active state edit (email is immutable once created).
     * @param array{name?:string, role?:string, is_active?:int} $u
     */
    public static function update(int $id, array $u): void
    {
        $sets = [];
        $bind = [':id' => $id];
        foreach (['name' => 'name', 'role' => 'role', 'is_active' => 'is_active'] as $field => $col) {
            if (array_key_exists($field, $u)) {
                $sets[] = "`$col` = :$col";
                $bind[":$col"] = $u[$field];
            }
        }
        if ($sets === []) {
            return;
        }
        $st = Database::pdo()->prepare('UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id');
        $st->execute($bind);
    }

    /** Rotate the hash; optionally clear the forced-change flag. */
    public static function setPasswordHash(int $id, string $hash, bool $clearForced = false): void
    {
        $st = Database::pdo()->prepare(
            'UPDATE users
             SET password_hash = :hash,
                 force_password_change = CASE WHEN :clear = 1 THEN 0 ELSE force_password_change END
             WHERE id = :id'
        );
        $st->execute([
            ':hash'  => $hash,
            ':clear' => $clearForced ? 1 : 0,
            ':id'    => $id,
        ]);
    }

    public static function setActive(int $id, bool $active): void
    {
        $st = Database::pdo()->prepare('UPDATE users SET is_active = :a WHERE id = :id');
        $st->execute([':a' => $active ? 1 : 0, ':id' => $id]);
    }

    public static function enableTotp(int $id, string $secret): void
    {
        $st = Database::pdo()->prepare(
            'UPDATE users SET totp_secret = :s, totp_enabled = 1 WHERE id = :id'
        );
        $st->execute([':s' => $secret, ':id' => $id]);
    }

    public static function disableTotp(int $id): void
    {
        $st = Database::pdo()->prepare(
            'UPDATE users SET totp_secret = NULL, totp_enabled = 0 WHERE id = :id'
        );
        $st->execute([':id' => $id]);
    }

    /** Number of active super admins (used by the last-super guard). */
    public static function countActiveSuperAdmins(): int
    {
        return (int) Database::pdo()
            ->query("SELECT COUNT(*) FROM users WHERE role = 'super_admin' AND is_active = 1")
            ->fetchColumn();
    }
}
