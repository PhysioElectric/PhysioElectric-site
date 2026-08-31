<?php
declare(strict_types=1);

namespace Admin;

/**
 * Team-member management for the About page.
 * Photos are uploaded through the existing secure /admin/upload endpoint;
 * this controller only persists the returned path (or an external URL).
 */
final class TeamController
{
    private const OLD_KEY = 'form_old_team';

    private const LIMITS = [
        'name_fa' => 120, 'name_en' => 120,
        'role_fa' => 160, 'role_en' => 160,
        'desc_fa' => 600, 'desc_en' => 600,
        'image'   => 255,
    ];

    public static function index(): void
    {
        admin_view('team/list', [
            'members'       => \TeamModel::all(),
            'schemaMissing' => !\Database::tableExists('team_members'),
            'adminActive'   => 'team',
        ]);
    }

    public static function createForm(): void
    {
        admin_view('team/form', [
            'member'     => self::old() ?? self::blank(),
            'formErrors' => null,
            'adminActive' => 'team',
        ]);
    }

    public static function editForm(int $id): void
    {
        $member = \TeamModel::byId($id);
        if ($member === null) {
            flash('error', t('admin.noRows'));
            redirect('/admin/team');
        }
        admin_view('team/form', [
            'member'     => $member,
            'formErrors' => null,
            'adminActive' => 'team',
        ]);
    }

    public static function create(): void
    {
        if (!\Database::tableExists('team_members')) {
            flash('error', t('admin.schemaMissing'));
            redirect('/admin/team');
        }
        $errors = self::validate($_POST);
        if ($errors !== null) {
            flash('error', $errors);
            $_SESSION[self::OLD_KEY] = self::old($_POST);
            redirect('/admin/team/create');
        }
        \TeamModel::create(self::payload($_POST));
        flash('success', t('admin.team.saved'));
        redirect('/admin/team');
    }

    public static function update(int $id): void
    {
        if (\TeamModel::byId($id) === null) {
            flash('error', t('admin.noRows'));
            redirect('/admin/team');
        }
        $errors = self::validate($_POST);
        if ($errors !== null) {
            flash('error', $errors);
            $_SESSION[self::OLD_KEY] = self::old($_POST) + ['id' => $id];
            redirect('/admin/team/' . $id . '/edit');
        }

        $before = \TeamModel::byId($id);
        $new    = self::payload($_POST);
        \TeamModel::update($id, $new);

        // If the photo was replaced, drop the old local file.
        if ($before !== null) {
            $oldImg = (string) $before['image'];
            if ($oldImg !== '' && $oldImg !== $new['image']) {
                UploadController::removeUploadFile($oldImg);
            }
        }
        flash('success', t('admin.team.saved'));
        redirect('/admin/team');
    }

    public static function delete(): void
    {
        $id = input_int('id');
        $member = \TeamModel::byId($id);
        if ($member === null) {
            flash('error', t('admin.noRows'));
            redirect('/admin/team');
        }
        \TeamModel::delete($id);
        UploadController::removeUploadFile((string) $member['image']);
        flash('success', t('admin.team.deleted'));
        redirect('/admin/team');
    }

    /** @param array<string,mixed> $in  @return string|null error message */
    private static function validate(array $in): ?string
    {
        $nameFa = trim((string) ($in['name_fa'] ?? ''));
        if ($nameFa === '' && trim((string) ($in['name_en'] ?? '')) === '') {
            return t('admin.team.needName');
        }
        $image = trim((string) ($in['image'] ?? ''));
        if ($image !== '' && !self::isAllowedImage($image)) {
            return t('admin.team.badImage');
        }
        return null;
    }

    /** Local /uploads/... or an http(s) URL; anything else is refused. */
    private static function isAllowedImage(string $image): bool
    {
        if (str_starts_with($image, '/uploads/')) {
            return true;
        }
        return (bool) preg_match('#^https?://#i', $image);
    }

    /** @param array<string,mixed> $in  @return array<string,mixed> */
    private static function payload(array $in): array
    {
        return [
            'name_fa' => str_cap(trim((string) ($in['name_fa'] ?? '')), self::LIMITS['name_fa']),
            'name_en' => str_cap(trim((string) ($in['name_en'] ?? '')), self::LIMITS['name_en']),
            'role_fa' => str_cap(trim((string) ($in['role_fa'] ?? '')), self::LIMITS['role_fa']),
            'role_en' => str_cap(trim((string) ($in['role_en'] ?? '')), self::LIMITS['role_en']),
            'desc_fa' => str_cap(trim((string) ($in['desc_fa'] ?? '')), self::LIMITS['desc_fa']),
            'desc_en' => str_cap(trim((string) ($in['desc_en'] ?? '')), self::LIMITS['desc_en']),
            'image'   => str_cap(trim((string) ($in['image'] ?? '')), self::LIMITS['image']),
            'sort_order' => (int) ($in['sort_order'] ?? 0),
        ];
    }

    /** @return array<string,mixed> */
    private static function blank(): array
    {
        return [
            'id' => 0, 'name_fa' => '', 'name_en' => '', 'role_fa' => '', 'role_en' => '',
            'desc_fa' => '', 'desc_en' => '', 'image' => '', 'sort_order' => 0,
        ];
    }

    /** @return array<string,mixed>|null */
    private static function old(?array $in = null): ?array
    {
        if ($in === null) {
            $o = $_SESSION[self::OLD_KEY] ?? null;
            unset($_SESSION[self::OLD_KEY]);
            return is_array($o) ? $o + self::blank() : null;
        }
        return self::payload($in);
    }
}
