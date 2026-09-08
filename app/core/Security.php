<?php
declare(strict_types=1);

/**
 * Transport & browser hardening: security headers, CSP with per-request
 * nonces, request-shape validation and a small audit logger.
 *
 * Everything is emitted from PHP so the same policy applies under Apache
 * (production) and under `php -S` (local simulation / CI) — .htaccess alone
 * does not protect the dev server.
 */
final class Security
{
    private static ?string $nonce = null;
    private static bool $headersSent = false;
    private static ?string $requestId = null;

    // ------------------------------------------------------------------
    //  Request identity
    // ------------------------------------------------------------------

    /** Short random id, echoed in logs and in the 500 page. Never sensitive. */
    public static function requestId(): string
    {
        if (self::$requestId === null) {
            self::$requestId = substr(bin2hex(random_bytes(8)), 0, 12);
        }
        return self::$requestId;
    }

    // ------------------------------------------------------------------
    //  CSP nonce
    // ------------------------------------------------------------------

    /**
     * Per-request nonce for inline <script> tags.
     * Views call: <script nonce="<?= Security::nonce() ?>">
     */
    public static function nonce(): string
    {
        if (self::$nonce === null) {
            self::$nonce = base64_encode(random_bytes(18));
        }
        return self::$nonce;
    }

    private static function cspEnabled(): bool
    {
        return Config::getBool('CSP_ENABLED', true);
    }

    /**
     * Extra origins required when the optional Cloudflare Turnstile widget
     * is active on the contact form. Empty when CAPTCHA is off, so the
     * default policy keeps its strict "no external connections" posture.
     *
     * @return string[]
     */
    private static function captchaOrigins(): array
    {
        if (class_exists('Captcha') && Captcha::enabled()) {
            return ['https://challenges.cloudflare.com'];
        }
        return [];
    }

    /**
     * Content-Security-Policy.
     *
     *  - scripts: same origin only + this request's nonce. The two static
     *    `onsubmit="return confirm(...)"` handlers used by the admin list
     *    views are allowed through 'unsafe-hashes' + their exact SHA-256,
     *    so no inline event handler from user content can ever run.
     *  - styles: 'unsafe-inline' is required because the bundled Tailwind
     *    build injects a <style> element at runtime.
     *  - no eval, no plugins, no framing, no external connections.
     */
    public static function csp(bool $adminContext = false): string
    {
        $directives = [];

        if (Config::getBool('CSP_ALLOW_UNSAFE_INLINE', false)) {
            $directives['script-src'] = ["'self'", "'unsafe-inline'"];
        } else {
            $script = ["'self'", "'nonce-" . self::nonce() . "'"];
            if ($adminContext) {
                // The two onsubmit="return confirm(...)" handlers live in the
                // admin list views; allow exactly those, nothing else.
                foreach (self::inlineHandlerHashes() as $hash) {
                    $script[] = "'sha256-" . $hash . "'";
                }
                $script[] = "'unsafe-hashes'";
            }
            // Optional CAPTCHA widget (Turnstile) needs its script origin.
            foreach (self::captchaOrigins() as $origin) {
                $script[] = $origin;
            }
            $directives['script-src'] = $script;
        }

        $directives['style-src']     = ["'self'", "'unsafe-inline'"];
        // Team photos seeded from the original design live on Unsplash; member
        // photos uploaded via the admin panel are served from 'self'.
        $directives['img-src']       = ["'self'", 'data:', 'https://images.unsplash.com'];
        $directives['font-src']      = ["'self'"];
        $connect = ["'self'"];
        foreach (self::captchaOrigins() as $origin) {
            $connect[] = $origin;
        }
        $directives['connect-src']   = $connect;
        $directives['media-src']     = ["'self'"];
        $directives['object-src']    = ["'none'"];
        $directives['base-uri']      = ["'self'"];
        $directives['form-action']   = ["'self'"];
        // Turnstile renders its widget in an iframe hosted on its own origin;
        // with CAPTCHA off the frame policy stays fully closed ('none').
        $frame = self::captchaOrigins();
        $directives['frame-src']     = $frame === [] ? ["'none'"] : $frame;
        $directives['frame-ancestors'] = ["'self'"];
        $directives['manifest-src']  = ["'self'"];
        $directives['worker-src']    = ["'self'", 'blob:'];

        if (Config::isProduction() && Config::isHttps()) {
            $directives['upgrade-insecure-requests'] = [];
        }

        $parts = [];
        foreach ($directives as $name => $values) {
            $parts[] = $values === [] ? $name : $name . ' ' . implode(' ', $values);
        }
        return implode('; ', $parts);
    }

    /**
     * SHA-256 (base64) of every inline event handler that ships with the
     * views. Recomputed from the live translation dictionary so it stays in
     * sync with the rendered markup.
     */
    private static function inlineHandlerHashes(): array
    {
        $hashes = [];
        if (function_exists('t')) {
            $handler = "return confirm('" . e(t('admin.confirmDelete')) . "');";
            $hashes[] = base64_encode(hash('sha256', $handler, true));
        }
        return $hashes;
    }

    // ------------------------------------------------------------------
    //  Headers
    // ------------------------------------------------------------------

    /**
     * Emit the security header set. Call before any output.
     * Idempotent — safe to call from the router and from json responses.
     */
    public static function sendHeaders(bool $adminContext = false): void
    {
        if (self::$headersSent || headers_sent()) {
            return;
        }
        self::$headersSent = true;

        header_remove('X-Powered-By');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=(), interest-cohort=()');
        header('Cross-Origin-Opener-Policy: same-origin');
        header('Cross-Origin-Resource-Policy: same-origin');
        header('X-Request-Id: ' . self::requestId());

        if (self::cspEnabled()) {
            header('Content-Security-Policy: ' . self::csp($adminContext));
        }

        // HSTS only when we actually served this request over TLS.
        if (Config::isProduction() && Config::isHttps()) {
            $maxAge = max(0, Config::getInt('HSTS_MAX_AGE', 31536000));
            if ($maxAge > 0) {
                header('Strict-Transport-Security: max-age=' . $maxAge . '; includeSubDomains');
            }
        }

        if ($adminContext) {
            // Admin HTML/JSON must never be cached by a shared proxy or the
            // browser's back/forward cache.
            header('Cache-Control: no-store, no-cache, must-revalidate, private, max-age=0');
            header('Pragma: no-cache');
        }
    }

    /** Cacheable, long-lived static asset headers (used for /assets and /uploads). */
    public static function sendStaticHeaders(bool $download = false): void
    {
        header('Cache-Control: public, max-age=2592000');
        if ($download) {
            header('Content-Disposition: attachment');
        }
    }

    // ------------------------------------------------------------------
    //  Request shape validation
    // ------------------------------------------------------------------

    private const ALLOWED_METHODS = ['GET', 'HEAD', 'POST'];

    /** Reject anything the app never uses (TRACE, PUT, DELETE, OPTIONS, ...). */
    public static function guardMethod(string $method): void
    {
        if (!in_array(strtoupper($method), self::ALLOWED_METHODS, true)) {
            http_response_code(405);
            header('Allow: ' . implode(', ', self::ALLOWED_METHODS));
            exit('405 - Method Not Allowed');
        }
    }

    /**
     * Normalised request path. Decodes %XX once and rejects the encodings
     * that are used to smuggle traversal sequences past a naive router.
     */
    public static function requestPath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = '/';
        }
        $decoded = rawurldecode($path);
        if ($decoded === false || $decoded === '') {
            $decoded = '/';
        }
        // Reject NUL bytes and re-encoded traversal sequences outright.
        if (str_contains($decoded, "\0")
            || str_contains($decoded, '..')
            || str_contains($decoded, '//')) {
            http_response_code(400);
            exit('400 - Bad Request');
        }
        $path = '/' . trim($decoded, '/');
        if ($path === '/') {
            return '/';
        }
        if (strlen($path) > 500) {
            http_response_code(414);
            exit('414 - URI Too Long');
        }
        return $path;
    }

    /** Body-size guard that runs before PHP materialises $_POST. */
    public static function guardBodySize(int $hardLimit = 8 * 1024 * 1024): void
    {
        $len = $_SERVER['CONTENT_LENGTH'] ?? null;
        if (is_string($len) && $len !== '' && (int) $len > $hardLimit) {
            http_response_code(413);
            exit('413 - Payload Too Large');
        }
    }

    /**
     * Defence-in-depth origin check that runs *in addition to* the CSRF token.
     *
     *  - No Origin/Referer header (typical for same-origin form GETs and for
     *    some HTTP clients): not a signal, so the token decides.
     *  - Header present: its host must match one of the hosts this app knows
     *    it is served from (HTTP_HOST, SERVER_NAME, X-Forwarded-Host or the
     *    configured SITE_BASE_URL). Anything else is cross-site and refused.
     */
    public static function isSameOrigin(): bool
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? null;
        if (is_string($origin) && $origin !== '') {
            // `Origin: null` comes from sandboxed iframes / file:// and is
            // never a same-origin request.
            if (trim($origin) === 'null') {
                return false;
            }
            return self::hostMatches(parse_url($origin, PHP_URL_HOST));
        }

        // No Origin header: fall back to Referer when the client sent one.
        $ref = $_SERVER['HTTP_REFERER'] ?? null;
        if (!is_string($ref) || $ref === '') {
            return true; // the CSRF token is the deciding factor
        }
        return self::hostMatches(parse_url($ref, PHP_URL_HOST));
    }

    private static function hostMatches(mixed $host): bool
    {
        if (!is_string($host) || $host === '') {
            return false;
        }
        $host = strtolower($host);
        if (Config::isTrustedHost($host)) {
            return true;
        }
        foreach (self::knownHosts() as $known) {
            if ($known !== '' && strcasecmp($host, $known) === 0) {
                return true;
            }
        }
        return false;
    }

    /** @return string[] lower-case, port-stripped hosts this app answers on */
    public static function knownHosts(): array
    {
        $candidates = [
            $_SERVER['HTTP_HOST'] ?? '',
            $_SERVER['SERVER_NAME'] ?? '',
            $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '',
            (string) parse_url((string) Config::get('SITE_BASE_URL', ''), PHP_URL_HOST),
            (string) Config::get('TRUSTED_HOSTS', ''),
        ];
        $out = [];
        foreach ($candidates as $c) {
            if (!is_string($c) || $c === '') {
                continue;
            }
            foreach (explode(',', $c) as $part) {
                $part = strtolower(trim($part));
                $part = (string) preg_replace('/:\d+$/', '', $part);
                if ($part !== '' && preg_match('/^[a-z0-9._\-]+$/', $part)) {
                    $out[$part] = true;
                }
            }
        }
        return array_keys($out);
    }

    /**
     * Client IP, or '' when the request does not carry a usable one.
     * An empty result makes RateLimiter fail *closed* rather than letting an
     * unattributable request bypass the brute-force limit.
     * Proxy headers are deliberately never trusted.
     */
    public static function clientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!is_string($ip) || !self::isValidIp($ip)) {
            return '';
        }
        return $ip;
    }

    public static function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /** Trim a user agent to something safe to log. */
    public static function userAgent(): string
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '-';
        if (!is_string($ua)) {
            return '-';
        }
        return substr(preg_replace('/[^\x20-\x7E]/', '', $ua) ?: '-', 0, 200);
    }

    // ------------------------------------------------------------------
    //  Audit log (security-relevant events only)
    // ------------------------------------------------------------------

    /**
     * Structured security event. Goes to the PHP error log (stderr under
     * Docker) so it lands in the normal log pipeline. Never logs passwords,
     * tokens or session ids.
     */
    public static function audit(string $event, array $context = []): void
    {
        if (!Config::getBool('SECURITY_LOG_ENABLED', true)) {
            return;
        }
        $safe = [];
        foreach ($context as $k => $v) {
            if (is_scalar($v) || $v === null) {
                $safe[$k] = is_string($v) ? substr($v, 0, 200) : $v;
            }
        }
        $safe['ip']  = self::clientIp() ?: 'unknown';
        $safe['req'] = self::requestId();
        error_log('[PE-SEC] ' . $event . ' ' . json_encode(
            $safe,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        ));
    }
}
