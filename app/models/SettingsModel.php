<?php
declare(strict_types=1);

final class SettingsModel
{
    /** Keys exposed in the admin settings form (label key in lang.php). */
    public const FORM_KEYS = [
        'site_name', 'telegram_user', 'contact_email', 'contact_phone',
        'address_fa', 'address_en',
        'hero_badge_fa', 'hero_badge_en',
        'hero_title_fa', 'hero_title_en',
        'hero_subtitle_fa', 'hero_subtitle_en',
        'footer_desc_fa', 'footer_desc_en',
    ];

    /**
     * Save many settings. Only known keys are accepted.
     * @param array<string,string> $values
     */
    public static function saveMany(array $values): int
    {
        $st = Database::pdo()->prepare(
            'INSERT INTO settings (skey, svalue) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)'
        );
        $count = 0;
        foreach (self::FORM_KEYS as $key) {
            if (!array_key_exists($key, $values)) {
                continue;
            }
            $st->execute([
                ':k' => $key,
                ':v' => trim((string) $values[$key]),
            ]);
            $count++;
        }
        $GLOBALS['__settings'] = null; // invalidate per-request cache
        return $count;
    }
}
