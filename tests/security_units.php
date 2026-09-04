<?php
declare(strict_types=1);

/**
 * Unit tests for the CHANGES-SECURITY-2.md core classes:
 *   - PasswordPolicy (shared password policy)
 *   - Totp (RFC 6238, no external dependency)
 *
 * Run:   php tests/security_units.php          (no DB required)
 *
 * Kept deliberately dependency-free (no composer, no phpunit):
 * a tiny assertion harness is all this needs.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../app/config.php';
Config::boot();
require __DIR__ . '/../app/core/PasswordPolicy.php';
require __DIR__ . '/../app/core/Totp.php';

$passCount = 0;
$failCount = 0;

function check(string $name, bool $cond, string $detail = ''): void
{
    global $passCount, $failCount;
    if ($cond) {
        $passCount++;
        echo "  ✔ {$name}\n";
    } else {
        $failCount++;
        echo "  ✘ {$name}" . ($detail !== '' ? " — {$detail}" : '') . "\n";
    }
}

function setEnv(string $appEnv): void
{
    putenv('APP_ENV=' . $appEnv);
    Config::flush();
}

// =====================================================================
//  PasswordPolicy
// =====================================================================
echo "\n== PasswordPolicy (APP_ENV=production) ==\n";
setEnv('production');

// --- length ---
check('empty rejected', !PasswordPolicy::validate('')['ok']);
check('8 chars rejected in production', !PasswordPolicy::validate('Abcd1234')['ok']);
check('15 chars rejected in production', !PasswordPolicy::validate('Abcd1234efgh567')['ok']);
$r = PasswordPolicy::validate('Abcd1234efgh5678'); // 16 chars, 3 classes
check('16 chars + 3 classes accepted', $r['ok'], (string) $r['reason']);
$r = PasswordPolicy::validate('abcdefgh1234!xqz'); // 16, lower+digit+special
check('16 chars lower+digit+special accepted', $r['ok'], (string) $r['reason']);

// --- class mixing below 20 chars ---
$r = PasswordPolicy::validate('abcdefghijklmnop'); // 16, one class only
check('<20 chars with a single class rejected', !$r['ok'], (string) $r['reason']);
$r = PasswordPolicy::validate('abcdefghijklmnopABCD'); // 20 chars, 2 classes → exempt
check('20 chars exempt from class mixing (accepted)', $r['ok'], (string) $r['reason']);

// --- ≥20 chars exempt (openssl-style output must pass) ---
$opensslStyle = trim((string) shell_exec('openssl rand -base64 24'));
$r = PasswordPolicy::validate((string) $opensslStyle);
check('openssl rand -base64 24 accepted', strlen((string) $opensslStyle) >= 20 && $r['ok'],
    (string) $opensslStyle . ' | ' . (string) $r['reason']);
$urandomStyle = trim((string) shell_exec("tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 32 2>/dev/null"));
$r = PasswordPolicy::validate((string) $urandomStyle);
check('tr -dc A-Za-z0-9 </dev/urandom | head -c 32 accepted', strlen((string) $urandomStyle) === 32 && $r['ok']);
$dicewareStyle = 'correct-horse-battery-staple';
$r = PasswordPolicy::validate($dicewareStyle);
check('diceware (5 words, no digits) accepted', $r['ok'], (string) $r['reason']);

// --- leaked/common list ---
$r = PasswordPolicy::validate('Welcome1234567890'); // 17 chars, 3 classes — but leaked
check('common leaked password (16+ chars) rejected', !$r['ok'], (string) $r['reason']);
$r = PasswordPolicy::validate('Welcome1234567891'); // 17 chars, 3 classes, NOT on the list
check('non-leaked look-alike accepted (exact-match list only)', $r['ok'], (string) $r['reason']);

// --- identity substrings (email / name) ---
$r = PasswordPolicy::validate('amirhosseini-Gz9#kQ2w', 'a.hosseini@example.com', 'Amir Hosseini');
check('password embedding the compacted name rejected', !$r['ok'], (string) $r['reason']);
$r = PasswordPolicy::validate('hosseini-Gz9#kQ2wXp4', 'a.hosseini@example.com', 'Amir Hosseini');
check('password embedding a name word rejected', !$r['ok'], (string) $r['reason']);
$r = PasswordPolicy::validate('admin!Xx9#2026Secure', 'admin@physioelectric.com', 'Admin');
check('password embedding the email local-part rejected', !$r['ok'], (string) $r['reason']);
$r = PasswordPolicy::validate('Tr9#zLw2$qBn7!Vx4mK', 'admin@physioelectric.com', 'Admin');
check('unrelated strong password accepted for admin@ too', $r['ok'], (string) $r['reason']);

echo "\n== PasswordPolicy (APP_ENV=development) ==\n";
setEnv('development');
check('11 chars rejected in development', !PasswordPolicy::validate('Abcd1234efg')['ok']);
$r = PasswordPolicy::validate('Abcd1234efgh'); // 12 chars + 3 classes
check('12 chars accepted in development', $r['ok'], (string) $r['reason']);

// =====================================================================
//  Totp (RFC 6238 / RFC 4226 style checks, secret "12345678901234567890")
// =====================================================================
echo "\n== Totp ==\n";
setEnv('production');

$secret = Totp::generateSecret(20);
check('generateSecret(): 32 chars of RFC4648 base32', strlen($secret) === 32
    && preg_match('/^[A-Z2-7]+$/', $secret) === 1, $secret);

// RFC 6238 appendix B secret, ASCII "12345678901234567890".
$rfc = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
check('verify() rejects non-6-digit input', !Totp::verify($rfc, '1234567'));
check('verify() rejects non-digit input', !Totp::verify($rfc, '12ab56'));
check('verify() rejects empty input', !Totp::verify($rfc, ''));
check('verify() rejects malformed stored secret (fail closed)',
    !Totp::verify('!!NOTBASE32!!', '123456'));

// Self-consistency with an independent mirrored implementation, including
// the ±1 period drift window.
$mirrorCode = static function (string $s, int $t): string {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $key = '';
    $buffer = 0;
    $bits = 0;
    foreach (str_split(rtrim($s, '=')) as $ch) {
        $buffer = ($buffer << 5) | strpos($alphabet, $ch);
        $bits += 5;
        if ($bits >= 8) {
            $key .= chr(($buffer >> ($bits - 8)) & 0xFF);
            $bits -= 8;
        }
    }
    $hash = hash_hmac('sha1', pack('N', intdiv($t, 30)), $key, true);
    $off = ord($hash[19]) & 0x0F;
    $bin = ((ord($hash[$off]) & 0x7F) << 24) | (ord($hash[$off + 1]) << 16)
        | (ord($hash[$off + 2]) << 8) | ord($hash[$off + 3]);
    return str_pad((string) ($bin % 1000000), 6, '0', STR_PAD_LEFT);
};

$s = Totp::generateSecret();
$now = time();
check('verify() accepts the current code (mirror implementation)',
    Totp::verify($s, $mirrorCode($s, $now)));
check('verify() tolerates one period of clock drift (±30 s)',
    Totp::verify($s, $mirrorCode($s, $now - 30)));
check('verify() rejects a code 3 periods old (outside ±1 window)',
    !Totp::verify($s, $mirrorCode($s, $now - 90)));

$uri = Totp::otpauthUri($s, 'admin@x.test', 'PhysioElectric');
check('otpauth:// URI carries the secret and issuer',
    str_starts_with($uri, 'otpauth://totp/PhysioElectric:admin%40x.test?')
    && str_contains($uri, 'secret=' . $s)
    && str_contains($uri, 'issuer=PhysioElectric'));

echo "\n------------------------------------------------------------\n";
echo "RESULT: {$passCount} passed, {$failCount} failed\n";
exit($failCount === 0 ? 0 : 1);
