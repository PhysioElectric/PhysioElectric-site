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

    /**
     * Create / resync the bootstrap admin from ADMIN_* env (and `.env`).
     *
     * Called from create_admin.php on every container start AND from the
     * login page so a `.env` dropped onto a host without a Docker restart
     * still takes effect.
     *
     *  - missing account → created
     *  - ADMIN_PASSWORD_RESET=1 → hash rewritten to ADMIN_PASSWORD
     *  - force_password_change still 1 (bootstrap never completed) → hash
     *    rewritten to the current ADMIN_PASSWORD so a newly supplied .env
     *    actually logs in
     *  - operator-supplied password (not the generated secret) → the forced
     *    first-login rotation is skipped so the panel is reachable
     *
     * @return array{ok:bool, action:string, email:string, error:?string}
     */
    public static function bootstrapFromEnv(): array
    {
        static $done = false;
        if ($done) {
            return ['ok' => true, 'action' => 'skipped', 'email' => '', 'error' => null];
        }
        $done = true;

        $name     = (string) (Config::get('ADMIN_NAME', 'Admin') ?? 'Admin');
        $email    = strtolower(trim((string) (Config::get('ADMIN_EMAIL', 'admin@physioelectric.com') ?? 'admin@physioelectric.com')));
        $password = (string) (Config::get('ADMIN_PASSWORD', '') ?? '');
        $reset    = Config::getBool('ADMIN_PASSWORD_RESET', false);
        $isProd   = Config::isProduction();

        $empty = ['ok' => false, 'action' => 'none', 'email' => $email, 'error' => null];

        if ($password === '') {
            $empty['error'] = 'ADMIN_PASSWORD is empty';
            return $empty;
        }
        if ($isProd && $password === 'Physio@2026') {
            $empty['error'] = 'ADMIN_PASSWORD is still the shipped default';
            return $empty;
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $empty['error'] = 'ADMIN_EMAIL is not a valid address';
            return $empty;
        }

        $generated = Config::getBool('ADMIN_PASSWORD_GENERATED', false);
        $secretFile = '/run/secrets/admin_pass';
        if (is_readable($secretFile)) {
            $fromSecret = trim((string) file_get_contents($secretFile));
            if ($fromSecret !== '' && hash_equals($fromSecret, $password)) {
                $generated = true;
            }
        }

        $policy = PasswordPolicy::validate($password, $email, $name);
        if (!$policy['ok']) {
            $empty['error'] = 'ADMIN_PASSWORD rejected: ' . (string) $policy['reason'];
            return $empty;
        }

        try {
            $pdo = Database::pdo();
        } catch (Throwable $e) {
            $empty['error'] = 'DB not ready: ' . $e->getMessage();
            return $empty;
        }

        try {
            $st = $pdo->prepare(
                'SELECT id, password_hash, force_password_change
                 FROM users WHERE email = :email LIMIT 1'
            );
            $st->execute([':email' => $email]);
            $row = $st->fetch();
        } catch (Throwable $e) {
            $empty['error'] = 'users table unavailable: ' . $e->getMessage();
            return $empty;
        }

        $forceOnCreate = $generated && !$reset ? 1 : 0;

        if ($row === false) {
            $hash = password_hash($password, Auth::hashOptions(), Auth::hashAlgoOptions());
            $ins  = $pdo->prepare(
                'INSERT INTO users (name, email, password_hash, is_active, role, force_password_change)
                 VALUES (:name, :email, :hash, 1, \'super_admin\', :force)'
            );
            $ins->execute([
                ':name'  => mb_substr($name, 0, 120),
                ':email' => $email,
                ':hash'  => $hash,
                ':force' => $forceOnCreate,
            ]);
            return ['ok' => true, 'action' => 'created', 'email' => $email, 'error' => null];
        }

        $id        = (int) $row['id'];
        $hashNow   = (string) $row['password_hash'];
        $forceFlag = (int) ($row['force_password_change'] ?? 0) === 1;

        if (!$reset && !$forceFlag) {
            return ['ok' => true, 'action' => 'exists', 'email' => $email, 'error' => null];
        }

        $matches = password_verify($password, $hashNow);

        // Resync when the operator asked for a reset, or when first-login
        // rotation never completed (the .env password was ignored before).
        if (!$matches) {
            $newHash = password_hash($password, Auth::hashOptions(), Auth::hashAlgoOptions());
            $clear   = ($reset || !$generated) ? 1 : 0;
            $upd = $pdo->prepare(
                'UPDATE users
                 SET password_hash = :hash,
                     is_active = 1,
                     force_password_change = CASE WHEN :clear = 1 THEN 0 ELSE force_password_change END
                 WHERE id = :id'
            );
            $upd->execute([':hash' => $newHash, ':clear' => $clear, ':id' => $id]);
            return ['ok' => true, 'action' => 'updated', 'email' => $email, 'error' => null];
        }

        if ($forceFlag && ($reset || !$generated)) {
            $pdo->prepare('UPDATE users SET force_password_change = 0, is_active = 1 WHERE id = :id')
                ->execute([':id' => $id]);
            return ['ok' => true, 'action' => 'unlocked', 'email' => $email, 'error' => null];
        }

        return ['ok' => true, 'action' => 'exists', 'email' => $email, 'error' => null];
    }
}
