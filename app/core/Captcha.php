<?php
declare(strict_types=1);

/**
 * Optional CAPTCHA layer for the public inquiry form (contact /
 * project-order wizard). The only supported provider is Cloudflare
 * Turnstile; the provider is opt-in and the whole layer is inert when the
 * CAPTCHA_* environment variables are not set, so existing installs are
 * unaffected.
 *
 *   CAPTCHA_PROVIDER=turnstile
 *   CAPTCHA_SITE_KEY=...
 *   CAPTCHA_SECRET_KEY=...      (never rendered or logged; env-only)
 *
 * Verification is always performed server side (siteverify) — the widget
 * token alone proves nothing. Failures are fail-closed: a missing/refused
 * token, an unreachable provider or a missing curl backend all reject the
 * submission and are written to the security audit log.
 */
final class Captcha
{
    /** Provider → verification endpoint (kept local to this class). */
    private const TURNSTILE_VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /** Turnstile is enabled only when every key is present. */
    public static function enabled(): bool
    {
        return self::provider() === 'turnstile'
            && self::siteKey() !== ''
            && self::secretKey() !== '';
    }

    public static function provider(): string
    {
        $p = Config::get('CAPTCHA_PROVIDER', '');
        return is_string($p) ? strtolower(trim($p)) : '';
    }

    /** Public site key — safe to render into the page. */
    public static function siteKey(): string
    {
        return trim((string) Config::get('CAPTCHA_SITE_KEY', ''));
    }

    /** Secret key — never output anywhere. */
    public static function secretKey(): string
    {
        return trim((string) Config::get('CAPTCHA_SECRET_KEY', ''));
    }

    /**
     * Validate a submitted challenge token against the provider.
     * Returns true when the CAPTCHA is disabled (nothing to prove).
     */
    public static function verify(?string $token): bool
    {
        if (!self::enabled()) {
            return true;
        }
        if (!is_string($token) || trim($token) === '' || strlen($token) > 2048) {
            Security::audit('captcha.token_missing', ['provider' => self::provider()]);
            return false;
        }
        if (!function_exists('curl_init')) {
            // Fail closed: without an HTTP client the token cannot be
            // checked server side, so the submission must not pass.
            Security::audit('captcha.backend_unavailable', ['provider' => self::provider()]);
            return false;
        }

        $post = [
            'secret'   => self::secretKey(),
            'response' => trim($token),
        ];
        $ip = Security::clientIp();
        if (Security::isValidIp($ip)) {
            $post['remoteip'] = $ip;
        }

        $ch = curl_init(self::TURNSTILE_VERIFY_URL);
        if ($ch === false) {
            Security::audit('captcha.backend_unavailable', ['provider' => self::provider()]);
            return false;
        }
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($post),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 6,          // fail closed, never hang the form
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT      => 'PhysioElectric-site/1.0 (+captcha-verify)',
        ]);
        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if (!is_string($raw) || $errno !== 0) {
            Security::audit('captcha.verify_failed', [
                'provider' => self::provider(),
                'reason'   => 'http_error_' . $errno,
            ]);
            return false;
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || empty($decoded['success'])) {
            Security::audit('captcha.verify_failed', [
                'provider' => self::provider(),
                'reason'   => is_array($decoded) && isset($decoded['error-codes'])
                    ? implode(',', array_map('strval', (array) $decoded['error-codes']))
                    : 'invalid_response',
            ]);
            return false;
        }
        return true;
    }
}
