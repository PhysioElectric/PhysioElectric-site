<?php
declare(strict_types=1);

/**
 * Self-healing schema for the feature tables (team_members, messages).
 *
 * On an existing install whose database predates these tables, the very first
 * web request creates (and seeds) them, so the contact wizard and the admin
 * inbox work without anyone having to remember to run `setup/migrate.php`.
 *
 * This is best-effort and never throws: if the DB user lacks DDL privileges or
 * the base schema is absent, we silently leave things to `setup/migrate.php`
 * / `db/init.sql` and the callers degrade gracefully via Database::tableExists().
 */
final class Schema
{
    private static bool $ran = false;

    public static function ensureFeatureTables(): void
    {
        if (self::$ran) {
            return;
        }
        self::$ran = true;

        try {
            // Only attempt when the base schema is already present; a brand-new
            // database is initialised by db/init.sql / migrate.php instead.
            if (!Database::schemaReady()) {
                return;
            }
            $pdo = Database::pdo();

            if (!Database::tableExists('team_members')) {
                $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `team_members` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name_fa`    VARCHAR(120)  NOT NULL DEFAULT '',
  `name_en`    VARCHAR(120)  NOT NULL DEFAULT '',
  `role_fa`    VARCHAR(160)  NOT NULL DEFAULT '',
  `role_en`    VARCHAR(160)  NOT NULL DEFAULT '',
  `desc_fa`    VARCHAR(600)  NOT NULL DEFAULT '',
  `desc_en`    VARCHAR(600)  NOT NULL DEFAULT '',
  `image`      VARCHAR(255)  NOT NULL DEFAULT '',
  `sort_order` INT           NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_team_sort` (`sort_order`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
                self::seedTeam($pdo);
            }

            if (!Database::tableExists('messages')) {
                $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS `messages` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `kind`           VARCHAR(20)   NOT NULL DEFAULT 'contact',
  `category`       VARCHAR(190)  NOT NULL DEFAULT '',
  `name`           VARCHAR(120)  NOT NULL,
  `company`        VARCHAR(160)  NOT NULL DEFAULT '',
  `email`          VARCHAR(190)  NOT NULL,
  `phone`          VARCHAR(40)   NOT NULL DEFAULT '',
  `contact_method` VARCHAR(20)   NOT NULL DEFAULT '',
  `contact_id`     VARCHAR(120)  NOT NULL DEFAULT '',
  `timeline`       VARCHAR(60)   NOT NULL DEFAULT '',
  `body`           TEXT          NOT NULL,
  `notes`          VARCHAR(500)  NOT NULL DEFAULT '',
  `lang`           VARCHAR(5)    NOT NULL DEFAULT 'fa',
  `attachments`    TEXT          NULL,
  `is_read`        TINYINT(1)    NOT NULL DEFAULT 0,
  `ip`             VARCHAR(45)   NOT NULL DEFAULT '',
  `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_msg_read` (`is_read`, `created_at`),
  KEY `idx_msg_ip_time` (`ip`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
            }
        } catch (Throwable) {
            // Never break the request because of self-heal; migrate.php remains
            // the authoritative path.
        }
    }

    private static function seedTeam(PDO $pdo): void
    {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM `team_members`')->fetchColumn();
        if ($count > 0) {
            return;
        }
        $st = $pdo->prepare(
            'INSERT INTO `team_members`
               (`name_fa`, `name_en`, `role_fa`, `role_en`, `desc_fa`, `desc_en`, `image`, `sort_order`)
             VALUES (:nfa, :nen, :rfa, :ren, :dfa, :den, :img, :sort)'
        );
        foreach (self::teamSeed() as $row) {
            $st->execute($row);
        }
    }

    /** @return array<int, array<string,mixed>> */
    private static function teamSeed(): array
    {
        return [
            [
                ':nfa' => 'دکتر امیر حسینی', ':nen' => 'Dr. Amir Hosseini',
                ':rfa' => 'مهندس ارشد / سیستم‌های AI', ':ren' => 'Lead Engineer / AI Systems',
                ':dfa' => 'طراحی معماری سیستم‌های هوشمند و نظارت بر مدل‌های پیچیده محاسباتی.',
                ':den' => 'Architecting intelligent systems and overseeing complex computational models.',
                ':img' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=800&auto=format&fit=crop',
                ':sort' => 1,
            ],
            [
                ':nfa' => 'سارا رادمنش', ':nen' => 'Sara Radmanesh',
                ':rfa' => 'آرشیتکت نرم‌افزار', ':ren' => 'Software Architect',
                ':dfa' => 'طراحی زیرساخت‌های مقیاس‌پذیر وب و پر کردن شکاف بین ریاضیات و کد.',
                ':den' => 'Designing scalable web infrastructure and bridging the gap between math and code.',
                ':img' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=800&auto=format&fit=crop',
                ':sort' => 2,
            ],
            [
                ':nfa' => 'محمدرضا افراز', ':nen' => 'Mohammad Reza Afraz',
                ':rfa' => 'شبیه‌سازی و تحلیل', ':ren' => 'Simulation & Analysis',
                ':dfa' => 'تبدیل پدیده‌های فیزیکی دنیای واقعی به مدل‌های کامسول با دقت بالا.',
                ':den' => 'Translating real-world physical phenomena into highly accurate COMSOL models.',
                ':img' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=800&auto=format&fit=crop',
                ':sort' => 3,
            ],
            [
                ':nfa' => 'ندا وحدتی', ':nen' => 'Neda Vahdati',
                ':rfa' => 'سیستم‌های نهفته', ':ren' => 'Embedded Systems',
                ':dfa' => 'توسعه سخت‌افزارهای IoT و بهینه‌سازی میکروکنترلرها برای محاسبات لبه.',
                ':den' => 'Developing IoT hardware and optimizing microcontrollers for edge computing.',
                ':img' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=800&auto=format&fit=crop',
                ':sort' => 4,
            ],
        ];
    }
}
