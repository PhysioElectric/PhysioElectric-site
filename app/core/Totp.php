<?php
declare(strict_types=1);

/**
 * Minimal RFC 6238 TOTP implementation (HMAC-SHA1, 6 digits, 30 s period)
 * with no external dependency — the project intentionally ships without
 * composer/npm packages.
 *
 * Used for the optional second factor on the admin login:
 *   - Auth::attemptLogin() starts a challenge when the account has
 *     totp_enabled = 1
 *   - Admin\AuthController verifies the 6-digit code with Totp::verify()
 *   - Admin\AccountController generates the secret with Totp::generateSecret()
 *     and shows the otpauth:// URI for authenticator apps.
 *
 * Secret format: RFC 4648 base32 (A–Z, 2–7). The verify window tolerates
 * ±1 period (30 s) of clock drift, matching Google Authenticator behaviour.
 */
final class Totp
{
    public const PERIOD      = 30;
    public const DIGITS      = 6;
    public const ALGORITHM   = 'sha1';
    /** Number of extra periods accepted on each side of the current one. */
    public const DRIFT_WINDOW = 1;

    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Random base32 secret (160 bits → 32 chars), suitable for storing and
     * for the otpauth:// URI.
     */
    public static function generateSecret(int $bytes = 20): string
    {
        $bytes = max(10, min(64, $bytes));
        return self::base32Encode(random_bytes($bytes));
    }

    /** otpauth:// URI for authenticator apps / QR generators. */
    public static function otpauthUri(string $secret, string $label, string $issuer = 'PhysioElectric'): string
    {
        $label = trim($label);
        if ($label === '') {
            $label = $issuer;
        }
        $params = [
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => self::ALGORITHM,
            'digits' => (string) self::DIGITS,
            'period' => (string) self::PERIOD,
        ];
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        return 'otpauth://totp/'
            . rawurlencode($issuer) . ':' . rawurlencode($label)
            . '?' . $query;
    }

    /**
     * Verify a submitted code against the current period plus/minus
     * DRIFT_WINDOW periods. Constant-time comparison. Returns false for
     * anything that is not exactly DIGITS digits.
     */
    public static function verify(string $secret, string $code): bool
    {
        $code = trim($code);
        if (preg_match('/^\d{' . self::DIGITS . '}$/', $code) !== 1) {
            return false;
        }
        if (self::base32Decode($secret) === null) {
            return false; // malformed stored secret → fail closed
        }

        $counter = (int) floor(time() / self::PERIOD);
        for ($offset = -self::DRIFT_WINDOW; $offset <= self::DRIFT_WINDOW; $offset++) {
            if (hash_equals(self::codeAt($secret, $counter + $offset), $code)) {
                return true;
            }
        }
        return false;
    }

    // ------------------------------------------------------------------

    /** HOTP value for a given counter (RFC 4226 truncation). */
    private static function codeAt(string $secret, int $counter): string
    {
        $key = self::base32Decode($secret);
        if ($key === null) {
            return str_repeat('0', self::DIGITS);
        }
        $hash = hash_hmac(self::ALGORITHM, pack('N', $counter), $key, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);
        return str_pad((string) ($binary % 10 ** self::DIGITS), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /** RFC 4648 base32 encode. */
    private static function base32Encode(string $data): string
    {
        $out = '';
        $bits = 0;
        $buffer = 0;
        $len = strlen($data);
        for ($i = 0; $i < $len; $i++) {
            $buffer = ($buffer << 8) | ord($data[$i]);
            $bits += 8;
            while ($bits >= 5) {
                $out .= self::BASE32_ALPHABET[($buffer >> ($bits - 5)) & 0x1F];
                $bits -= 5;
            }
        }
        if ($bits > 0) {
            $out .= self::BASE32_ALPHABET[($buffer << (5 - $bits)) & 0x1F];
        }
        return $out;
    }

    /** RFC 4648 base32 decode; null when the string is not valid base32. */
    private static function base32Decode(string $data): ?string
    {
        $data = strtoupper(rtrim(trim($data), "=\x20\x09\x0A\x0D"));
        if ($data === '' || strlen($data) % 8 === 1) {
            return null;
        }
        $out = '';
        $bits = 0;
        $buffer = 0;
        foreach (str_split($data) as $char) {
            $val = strpos(self::BASE32_ALPHABET, $char);
            if ($val === false) {
                return null;
            }
            $buffer = ($buffer << 5) | $val;
            $bits += 5;
            if ($bits >= 8) {
                $out .= chr(($buffer >> ($bits - 8)) & 0xFF);
                $bits -= 8;
            }
        }
        return $out;
    }
}
