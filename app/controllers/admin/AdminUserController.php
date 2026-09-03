<?php
declare(strict_types=1);

namespace Admin;

use Throwable;

/**
 * Admin-user management ("مدیریت ادمین‌ها") — super_admin only. The role
 * gate itself lives in the central router block (index.php), not here.
 *
 * Anti-sabotage rules enforced server side on every write:
 *  - an admin can never deactivate / soft-delete / demote / edit-role on
 *    their own account (no self lock-out, no self escalation)
 *  - the LAST active super_admin can never be deactivated, deleted or
 *    demoted — COUNT(*) inside the same transaction, checked before AND
 *    after the write (concurrent-edit safe)
 *  - passwords of OTHER users are never changeable from this controller:
 *    every password is rotated only by its owner in AccountController
 *  - every sensitive mutation requires the actor's own password again
 *    (current_password, password_verify) — a stolen session alone is not
 *    enough
 *  - every operation is audited with target id + actor id.
 */
final class AdminUserController
{
    private const OLD_KEY = 'admin_user_form_old';

    private const ROLES = ['super_admin', 'editor', 'viewer'];

    /** Preserved fields when a create/edit fails (password never kept). */
    private const OLD_FIELDS = ['name', 'email', 'role', 'is_active'];

    // ------------------------------------------------------------------
    //  Read
    // ------------------------------------------------------------------

    public static function index(): void
    {
        admin_view('users/list', [
            'users'       => \UserModel::all(),
            'roleLabels'  => \UserModel::ROLE_LABELS,
            'adminActive' => 'users',
        ]);
    }

    public static function createForm(): void
    {
        $old = self::consumeOld();
        admin_view('users/form', [
            'user'        => $old ?? self::blank(),
            'isEdit'      => false,
            'roleLabels'  => \UserModel::ROLE_LABELS,
            'isSelf'      => false,
            'adminActive' => 'users',
        ]);
    }

    public static function editForm(int $id): void
    {
        $target = \UserModel::byId($id);
        if ($target === null) {
            flash('error', 'کاربر موردنظر یافت نشد.');
            redirect('/admin/users');
        }
        $old = self::consumeOld();
        if (is_array($old)) {
            $target = array_merge($target, $old);
        }
        admin_view('users/form', [
            'user'        => $target,
            'isEdit'      => true,
            'roleLabels'  => \UserModel::ROLE_LABELS,
            'isSelf'      => (int) $target['id'] === \Auth::userId(),
            'adminActive' => 'users',
        ]);
    }

    // ------------------------------------------------------------------
    //  create() / store()
    // ------------------------------------------------------------------

    public static function create(): void
    {
        self::store();
    }

    public static function store(): void
    {
        $actor = self::actor();
        if ($actor === null) {
            return;
        }

        // Re-authentication of the person running the operation.
        if (!self::reauth($actor)) {
            self::keepOld($_POST);
            redirect('/admin/users/create');
        }

        $name  = str_cap(trim((string) ($_POST['name'] ?? '')), 120);
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $role  = (string) ($_POST['role'] ?? '');
        $pass  = (string) ($_POST['password'] ?? '');

        $error = self::validateCreate($name, $email, $role, $pass);
        if ($error !== null) {
            flash('error', $error);
            self::keepOld($_POST);
            redirect('/admin/users/create');
        }

        $hash = password_hash($pass, \Auth::hashOptions(), \Auth::hashAlgoOptions());
        $newId = \UserModel::create([
            'name'        => $name,
            'email'       => $email,
            'password_hash' => $hash,
            'role'        => $role,
            'is_active'   => 1,
            'created_by'  => (int) $actor['id'],
        ]);

        \Security::audit('admin.user_created', [
            'created_id' => $newId,
            'role'       => $role,
            'by'         => (int) $actor['id'],
            'email'      => $email,
        ]);
        flash('success', 'کاربر ساخته شد. او باید در اولین ورود، رمز عبور را عوض کند.');
        redirect('/admin/users');
    }

    private static function validateCreate(string $name, string $email, string $role, string $pass): ?string
    {
        if ($name === '') {
            return 'نام نمی‌تواند خالی باشد.';
        }
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return 'ایمیل معتبر نیست.';
        }
        if (strlen($email) > 190) {
            return 'ایمیل بیش از حد طولانی است.';
        }
        if (!in_array($role, self::ROLES, true)) {
            return 'نقش انتخابی مجاز نیست.';
        }
        if (\UserModel::byEmail($email) !== null) {
            return 'کاربری با این ایمیل قبلاً ساخته شده است.';
        }
        $policy = \PasswordPolicy::validate($pass, $email, $name);
        if (!$policy['ok']) {
            return (string) $policy['reason'];
        }
        return null;
    }

    // ------------------------------------------------------------------
    //  edit() / update()  (name / role / active state only)
    // ------------------------------------------------------------------

    public static function edit(int $id): void
    {
        self::editForm($id);
    }

    public static function update(int $id): void
    {
        $actor = self::actor();
        if ($actor === null) {
            return;
        }
        $target = \UserModel::byId($id);
        if ($target === null) {
            flash('error', 'کاربر موردنظر یافت نشد.');
            redirect('/admin/users');
        }

        // Re-authentication of the person running the operation.
        if (!self::reauth($actor)) {
            self::keepOld($_POST);
            redirect('/admin/users/' . $id . '/edit');
        }

        $name      = str_cap(trim((string) ($_POST['name'] ?? '')), 120);
        $role      = (string) ($_POST['role'] ?? '');
        $isActive  = isset($_POST['is_active']) ? 1 : 0; // checkbox semantics
        $isSelf    = (int) $target['id'] === (int) $actor['id'];

        // Self-role-edit and self-deactivation are disabled entirely (even
        // super_admin): whatever the form posted, the actor's own role and
        // active state are pinned to their current database values.
        if ($isSelf) {
            $role     = (string) ($target['role'] ?? '');
            $isActive = (int) ($target['is_active'] ?? 1);
        }

        if ($name === '') {
            flash('error', 'نام نمی‌تواند خالی باشد.');
            redirect('/admin/users/' . $id . '/edit');
        }
        if (!in_array($role, self::ROLES, true)) {
            flash('error', 'نقش انتخابی مجاز نیست.');
            redirect('/admin/users/' . $id . '/edit');
        }
        if ($isSelf && $role !== (string) ($target['role'] ?? '')) {
            \Security::audit('admin.self_role_edit_denied', [
                'target_id' => $id, 'by' => (int) $actor['id'],
            ]);
            flash('error', 'نمی‌توانید نقش خودتان را تغییر دهید.');
            redirect('/admin/users/' . $id . '/edit');
        }
        if ($isSelf && $isActive !== (int) $target['is_active']) {
            \Security::audit('admin.self_deactivate_denied', [
                'target_id' => $id, 'by' => (int) $actor['id'],
            ]);
            flash('error', 'نمی‌توانید حساب خودتان را غیرفعال کنید.');
            redirect('/admin/users/' . $id . '/edit');
        }

        $guardError = self::guardLastActiveSuper($target, $role, $isActive === 1);
        if ($guardError !== null) {
            \Security::audit('admin.last_super_guard', [
                'target_id' => $id, 'by' => (int) $actor['id'],
                'reason'    => $guardError,
            ]);
            flash('error', $guardError);
            redirect('/admin/users/' . $id . '/edit');
        }

        // Persist inside a transaction: the last-super invariant is checked
        // under a row lock before the write and re-verified after it.
        $txError = self::applyInTransaction($id, [
            'name'      => $name,
            'role'      => $role,
            'is_active' => $isActive,
        ]);
        if ($txError !== null) {
            flash('error', $txError);
            redirect('/admin/users/' . $id . '/edit');
        }

        \Security::audit('admin.user_updated', [
            'target_id' => $id,
            'by'        => (int) $actor['id'],
            'role'      => $role,
            'is_active' => $isActive,
        ]);
        flash('success', 'تغییرات کاربر ذخیره شد.');
        redirect('/admin/users');
    }

    // ------------------------------------------------------------------
    //  toggleActive / delete (soft-delete via is_active = 0)
    // ------------------------------------------------------------------

    public static function toggleActive(): void
    {
        $id = input_int('id');
        $target = \UserModel::byId($id);
        if ($target === null) {
            flash('error', 'کاربر موردنظر یافت نشد.');
            redirect('/admin/users');
        }
        self::setActiveState($id, (int) $target['is_active'] !== 1);
    }

    /** Soft-delete: is_active = 0 (row stays for the audit trail). */
    public static function delete(): void
    {
        $id = input_int('id');
        $target = \UserModel::byId($id);
        if ($target === null) {
            flash('error', 'کاربر موردنظر یافت نشد.');
            redirect('/admin/users');
        }
        self::setActiveState($id, false);
    }

    /**
     * Shared deactivation/activation path with the full guard set:
     * re-auth, no self lock-out, no last-super lock-out.
     */
    private static function setActiveState(int $id, bool $active): void
    {
        $actor = self::actor();
        if ($actor === null) {
            return;
        }
        $target = \UserModel::byId($id);
        if ($target === null) {
            redirect('/admin/users');
        }
        if (!self::reauth($actor)) {
            redirect('/admin/users');
        }

        $isSelf = (int) $target['id'] === (int) $actor['id'];
        if ($isSelf) {
            \Security::audit('admin.self_deactivate_denied', [
                'target_id' => $id, 'by' => (int) $actor['id'],
            ]);
            flash('error', 'نمی‌توانید حساب خودتان را غیرفعال یا حذف کنید.');
            redirect('/admin/users');
        }
        // Deactivating an active super admin must leave ≥1 active super.
        $guardError = null;
        if (!$active && (int) $target['is_active'] === 1
            && (string) $target['role'] === 'super_admin') {
            $guardError = self::guardLastActiveSuper($target, 'super_admin', false);
        }
        if ($guardError !== null) {
            \Security::audit('admin.last_super_guard', [
                'target_id' => $id, 'by' => (int) $actor['id'],
                'reason'    => $guardError,
            ]);
            flash('error', $guardError);
            redirect('/admin/users');
        }

        $txError = self::applyInTransaction($id, ['is_active' => $active ? 1 : 0]);
        if ($txError !== null) {
            flash('error', $txError);
            redirect('/admin/users');
        }
        \Security::audit($active ? 'admin.user_activated' : 'admin.user_deactivated', [
            'target_id' => $id,
            'by'        => (int) $actor['id'],
            'soft'      => $active ? 0 : 1,
        ]);
        flash('success', $active ? 'حساب کاربر فعال شد.' : 'حساب کاربر غیرفعال شد (soft-delete).');
        redirect('/admin/users');
    }

    // ------------------------------------------------------------------

    /**
     * The last-active-super guard (COUNT of active super_admins). Runs
     * before the mutation is attempted; the transaction helper below
     * re-checks the same invariant after the write, under locks.
     */
    private static function guardLastActiveSuper(array $target, string $newRole, bool $newActive): ?string
    {
        $wasActiveSuper = (int) $target['is_active'] === 1
            && (string) $target['role'] === 'super_admin';
        $staysSuper     = $newRole === 'super_admin' && $newActive;
        if (!$wasActiveSuper || $staysSuper) {
            return null; // the change cannot remove an active super admin
        }
        if (\UserModel::countActiveSuperAdmins() > 1) {
            return null;
        }
        return 'غیرممکن است: باید همیشه حداقل یک super_admin فعال در سیستم بماند.';
    }

    /**
     * Persist an edit under a transaction:
     *   1. SELECT ... FOR UPDATE on the target row (serialises editors)
     *   2. UPDATE
     *   3. locking re-count of active super_admins — when the change left
     *      the system with zero active supers the transaction is rolled
     *      back (protects against concurrent last-super removal).
     *
     * @param array{name?:string, role?:string, is_active?:int} $changes
     * @return string|null error message (null = committed)
     */
    private static function applyInTransaction(int $targetId, array $changes): ?string
    {
        $pdo = \Database::pdo();
        try {
            $pdo->beginTransaction();

            $st = $pdo->prepare(
                'SELECT id, role, is_active FROM users WHERE id = :id FOR UPDATE'
            );
            $st->execute([':id' => $targetId]);
            $row = $st->fetch();
            if ($row === false) {
                $pdo->rollBack();
                return 'کاربر موردنظر یافت نشد.';
            }

            $finalRole   = (string) ($changes['role'] ?? $row['role']);
            $finalActive = (int) ($changes['is_active'] ?? $row['is_active']);
            $removesSuper = (int) $row['is_active'] === 1
                && (string) $row['role'] === 'super_admin'
                && ($finalRole !== 'super_admin' || $finalActive !== 1);
            if ($removesSuper) {
                $cnt = (int) $pdo->query(
                    "SELECT COUNT(*) FROM users
                     WHERE role = 'super_admin' AND is_active = 1 AND id <> " . (int) $targetId
                )->fetchColumn();
                if ($cnt < 1) {
                    $pdo->rollBack();
                    return 'غیرممکن است: باید همیشه حداقل یک super_admin فعال در سیستم بماند.';
                }
            }

            \UserModel::update($targetId, $changes);

            // Post-write re-verification (locking read = current state).
            $remaining = (int) $pdo->query(
                "SELECT COUNT(*) FROM users
                 WHERE role = 'super_admin' AND is_active = 1 FOR UPDATE"
            )->fetchColumn();
            if ($remaining < 1) {
                $pdo->rollBack();
                return 'عملیات به‌دلیل نقض قانون «حداقل یک super_admin فعال» لغو شد.';
            }

            $pdo->commit();
            return null;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[AdminUserController] tx failed: ' . $e->getMessage());
            return 'خطای پایگاه داده هنگام اعمال تغییر؛ دوباره تلاش کنید.';
        }
    }

    /** The acting super admin, or redirect when the session user is gone. */
    private static function actor(): ?array
    {
        $actor = \UserModel::byIdWithHash(\Auth::userId());
        if ($actor === null) {
            \Auth::logout();
            redirect('/admin/login');
        }
        return $actor;
    }

    /** current_password must verify against the ACTOR's own hash. */
    private static function reauth(array $actor): bool
    {
        $current = input_str('current_password');
        if ($current !== '' && password_verify($current, (string) $actor['password_hash'])) {
            return true;
        }
        \Security::audit('admin.reauth_failed', [
            'actor_id' => (int) $actor['id'],
            'role'     => (string) ($actor['role'] ?? ''),
        ]);
        flash('error', 'تأیید مجدد با رمز عبور خودتان انجام نشد. عملیات رد شد.');
        return false;
    }

    private static function keepOld(array $in): void
    {
        $old = [];
        foreach (self::OLD_FIELDS as $field) {
            if (isset($in[$field]) && is_scalar($in[$field])) {
                $old[$field] = (string) $in[$field];
            }
        }
        $_SESSION[self::OLD_KEY] = $old;
    }

    /** @return array<string,mixed>|null */
    private static function consumeOld(): ?array
    {
        $o = $_SESSION[self::OLD_KEY] ?? null;
        unset($_SESSION[self::OLD_KEY]);
        return is_array($o) ? $o : null;
    }

    /** @return array<string,mixed> */
    private static function blank(): array
    {
        return [
            'id' => 0, 'name' => '', 'email' => '', 'role' => 'editor',
            'is_active' => 1, 'created_by' => null, 'last_login_at' => null,
        ];
    }
}
