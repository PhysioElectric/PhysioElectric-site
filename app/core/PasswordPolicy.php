<?php
declare(strict_types=1);

/**
 * Shared password policy — the single place that decides whether a password
 * is acceptable. Used by every code path that sets a password:
 *   - app/setup/create_admin.php        (bootstrap admin, from env)
 *   - Admin\AccountController           (own-password change + forced change)
 *   - Admin\AdminUserController         (creating new admins from the panel)
 *
 * Rules:
 *  - minimum length 16 when APP_ENV=production, 12 otherwise
 *  - passwords shorter than 20 characters must mix at least 3 of the 4
 *    character classes (upper / lower / digit / special). Very long random
 *    or diceware passwords (20+ chars) are exempt on purpose: forcing
 *    symbols onto an already-strong long password makes it harder to
 *    remember without making it meaningfully stronger.
 *  - checked against a small static list of the most common leaked
 *    passwords (app/setup/data/common-passwords.txt, < 500 KB). No external
 *    API: the container runs with allow_url_fopen=Off and no outbound
 *    dependency is wanted.
 *  - must not embed a significant substring of the account email or name.
 *
 * Messages are Persian (the admin UI language). The caller decides how to
 * surface them (flash message / stderr).
 */
final class PasswordPolicy
{
    /** Static local list of the most leaked passwords. */
    public const COMMON_LIST_FILE = __DIR__ . '/../setup/data/common-passwords.txt';

    /** @var array<string,true>|null lazily loaded, lower-cased keys */
    private static ?array $common = null;

    /**
     * @return array{ok:bool, reason:?string}
     */
    public static function validate(string $password, ?string $email = null, ?string $name = null): array
    {
        if ($password === '') {
            return self::fail('رمز عبور نمی‌تواند خالی باشد.');
        }
        if (mb_strlen($password) > 1000) {
            // Keep the hash function out of absurd-payload territory.
            return self::fail('رمز عبور بیش از حد طولانی است (حداکثر ۱۰۰۰ کاراکتر).');
        }

        $min = Config::isProduction() ? 16 : 12;
        $len = mb_strlen($password);
        if ($len < $min) {
            return self::fail(
                'رمز عبور باید حداقل ' . $min . ' کاراکتر باشد'
                . (Config::isProduction() ? '' : ' (در محیط production حداقل ۱۶ کاراکتر الزامی است)')
                . '.'
            );
        }

        // Long random / diceware passwords are exempt from class mixing.
        if ($len < 20 && !self::hasEnoughClasses($password)) {
            return self::fail(
                'رمزهای کوتاه‌تر از ۲۰ کاراکتر باید شامل حداقل ۳ دسته از ۴ دسته‌ی '
                . '«حرف بزرگ، حرف کوچک، عدد، کاراکتر خاص» باشند. برای رمزهای بلند (۲۰+ کاراکتر) '
                . 'این قید لازم نیست.'
            );
        }

        if (self::isCommon($password)) {
            return self::fail(
                'این رمز عبور در فهرست رایج‌ترین رمزهای عبور لو رفته قرار دارد و رد شد. '
                . 'لطفاً یک رمز کاملاً تصادفی (مثلاً خروجی openssl rand) یا diceware انتخاب کنید.'
            );
        }

        $fragment = self::embeddedIdentityFragment($password, $email, $name);
        if ($fragment !== null) {
            return self::fail(
                'رمز عبور نباید شامل بخش قابل‌توجهی از ایمیل یا نام کاربر باشد '
                . '(بخش «' . $fragment . '» داخل آن دیده می‌شود).'
            );
        }

        return ['ok' => true, 'reason' => null];
    }

    // ------------------------------------------------------------------

    /** @return array{ok:bool, reason:?string} */
    private static function fail(string $reason): array
    {
        return ['ok' => false, 'reason' => $reason];
    }

    /** At least 3 of: upper, lower, digit, special. */
    private static function hasEnoughClasses(string $password): bool
    {
        $classes = 0;
        if (preg_match('/[A-Z]/', $password) === 1) {
            $classes++;
        }
        if (preg_match('/[a-z]/', $password) === 1) {
            $classes++;
        }
        if (preg_match('/[0-9]/', $password) === 1) {
            $classes++;
        }
        if (preg_match('/[^A-Za-z0-9]/', $password) === 1) {
            $classes++;
        }
        return $classes >= 3;
    }

    /**
     * Exact (case-insensitive) match against the static common-password
     * list. The file is optional at runtime (e.g. stripped image): when it
     * is unreadable the check is skipped — the file ships with the repo and
     * is < 500 KB.
     */
    private static function isCommon(string $password): bool
    {
        $needle = strtolower(trim($password));
        if ($needle === '') {
            return false;
        }
        if (self::$common === null) {
            self::$common = [];
            $lines = @file(self::COMMON_LIST_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (is_array($lines)) {
                foreach ($lines as $line) {
                    $line = strtolower(trim((string) $line));
                    if ($line !== '') {
                        self::$common[$line] = true;
                    }
                }
            }
        }
        return isset(self::$common[$needle]);
    }

    /**
     * Simple substring guard: a password must not embed a meaningful slice
     * of the account identity. Fragments shorter than 4 characters are too
     * generic to matter (single letters are everywhere in strong passwords).
     */
    private static function embeddedIdentityFragment(string $password, ?string $email, ?string $name): ?string
    {
        $haystack = mb_strtolower($password);
        $fragments = [];

        if (is_string($email) && $email !== '') {
            $local = strtolower(trim(explode('@', $email, 2)[0]));
            if ($local !== '') {
                $fragments[] = $local;
            }
        }
        if (is_string($name) && $name !== '') {
            $compact = strtolower((string) preg_replace('/[^A-Za-z0-9\x{0600}-\x{06FF}]+/u', '', $name));
            if ($compact !== '') {
                $fragments[] = $compact;
            }
            foreach (preg_split('/[^A-Za-z0-9\x{0600}-\x{06FF}]+/u', strtolower($name)) ?: [] as $word) {
                if ($word !== '') {
                    $fragments[] = $word;
                }
            }
        }

        foreach (array_unique($fragments) as $fragment) {
            $fragment = trim($fragment);
            if (mb_strlen($fragment) < 4 || $fragment === '') {
                continue;
            }
            if (str_contains($haystack, $fragment)) {
                return $fragment;
            }
        }
        return null;
    }
}
