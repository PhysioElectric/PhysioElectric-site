<?php
declare(strict_types=1);

namespace Admin;

final class SettingsController
{
    public static function form(): void
    {
        $s = settings();
        $values = [];
        foreach (\SettingsModel::FORM_KEYS as $key) {
            $values[$key] = $s[$key] ?? '';
        }
        admin_view('settings/form', ['values' => $values]);
    }

    public static function save(): void
    {
        // Basic format validation for the contact fields.
        $email = trim((string) ($_POST['contact_email'] ?? ''));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'آدرس ایمیل معتبر نیست.');
            redirect('/admin/settings');
        }
        $tel = trim((string) ($_POST['telegram_user'] ?? ''));
        if ($tel !== '') {
            $tel = ltrim($tel, '@');
            $_POST['telegram_user'] = (string) preg_replace('/[^A-Za-z0-9_]/', '', $tel);
        }

        $count = \SettingsModel::saveMany($_POST);
        flash('success', $count > 0 ? t('admin.saved') : t('admin.fillAll'));
        redirect('/admin/settings');
    }
}
