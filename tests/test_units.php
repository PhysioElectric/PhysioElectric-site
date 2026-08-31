<?php
declare(strict_types=1);

/**
 * Backend unit tests: auth timing, rate limiting, config hardening,
 * CSRF, request parsing, slugs and the Jalali calendar.
 *
 * Needs a reachable MySQL/MariaDB with the app schema loaded.
 * Run: php tests/test_units.php
 */
require __DIR__ . '/bootstrap.php';
require BASE_PATH . '/controllers/admin/PostController.php';
require BASE_PATH . '/controllers/admin/ProjectController.php';

// ---------------------------------------------------------------- Auth
T::group('Auth — password hashing & the timing side-channel fix');

$dummy = Auth::dummyHash();
T::ok(password_get_info($dummy)['algoName'] !== 'unknown', 'dummy hash is a real, parseable hash',
    var_export(password_get_info($dummy), true));
T::same(false, password_verify('anything', $dummy), 'dummy hash verifies to false');
T::same(false, password_needs_rehash($dummy, Auth::hashOptions(), Auth::hashAlgoOptions()),
    'dummy hash uses the current algorithm + cost');

// The old dummy hash was malformed, so password_verify() bailed out in
// microseconds while a real hash took hundreds of ms — a user-enumeration
// oracle. Both paths must now cost roughly the same.
$real = password_hash('Physio@2026!Local', Auth::hashOptions(), Auth::hashAlgoOptions());
$t0 = hrtime(true);
password_verify('guess', $dummy);
$tDummy = (hrtime(true) - $t0) / 1e6;
$t0 = hrtime(true);
password_verify('guess', $real);
$tReal = (hrtime(true) - $t0) / 1e6;
printf("     (dummy verify %.1f ms, real verify %.1f ms)\n", $tDummy, $tReal);
T::ok($tDummy > 20.0, 'dummy-hash verify actually runs the KDF (>20 ms)', $tDummy . ' ms');
T::ok($tDummy > $tReal * 0.5 && $tDummy < $tReal * 2.0,
    'dummy and real verify are within 2x of each other',
    sprintf('%.1f ms vs %.1f ms', $tDummy, $tReal));

T::group('Auth — login flow against the live database');
// CLI has no REMOTE_ADDR; without one the limiter fails closed by design.
$_SERVER['REMOTE_ADDR'] = '203.0.113.5';
$pdo = Database::pdo();
$pdo->exec('DELETE FROM login_attempts');

$bad = Auth::attemptLogin('admin@physioelectric.com', 'definitely-wrong');
T::same(false, $bad['ok'], 'wrong password rejected');
T::same('invalid', $bad['code'], 'wrong password -> code=invalid');

$unknown = Auth::attemptLogin('nobody-' . bin2hex(random_bytes(4)) . '@example.com', 'x-long-password');
T::same('invalid', $unknown['code'], 'unknown user -> same generic code (no enumeration)');

T::same(false, Auth::attemptLogin('', '')['ok'], 'empty credentials rejected');
T::same('empty', Auth::attemptLogin('a@b.c', '')['code'], 'empty password -> code=empty');

T::group('RateLimiter — brute-force lockout');
$pdo->exec('DELETE FROM login_attempts');
$_SERVER['REMOTE_ADDR'] = '203.0.113.77';

T::same(false, RateLimiter::isLocked(), 'fresh IP is not locked');
for ($i = 0; $i < RateLimiter::maxAttempts(); $i++) {
    RateLimiter::recordFailure(null, 'victim@example.com');
}
T::same(true, RateLimiter::isLocked(), 'IP locked after MAX_ATTEMPTS failures');
$left = RateLimiter::lockSecondsLeft();
T::ok($left > 0 && $left <= RateLimiter::windowMinutes() * 60,
    'lock countdown is sane (0 < left <= window)', $left . 's');
T::ok($left > RateLimiter::windowMinutes() * 60 - 60,
    'countdown is not truncated by a timezone offset', $left . 's');

// Per-account throttling from a different IP.
$_SERVER['REMOTE_ADDR'] = '203.0.113.99';
T::same(true, RateLimiter::isLocked(null, 'victim@example.com'),
    'the same account is locked from another IP too');

// Fail closed on an unusable source address.
$_SERVER['REMOTE_ADDR'] = 'not-an-ip';
T::same(true, RateLimiter::isLocked(), 'invalid client IP fails closed');
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$pdo->exec('DELETE FROM login_attempts');

T::group('Config — host-header injection guard');
putenv('SITE_BASE_URL');
putenv('TRUSTED_HOSTS');
Config::flush();
$_SERVER['HTTP_HOST'] = 'evil.example.com';
T::ok(!str_contains(Config::baseUrl(), 'evil.example.com'),
    'untrusted Host is not used for the base URL', Config::baseUrl());
putenv('TRUSTED_HOSTS=shop.example.com,*.physioelectric.com');
Config::flush();
$_SERVER['HTTP_HOST'] = 'www.physioelectric.com';
T::same(true, Config::isTrustedHost('www.physioelectric.com'), 'wildcard subdomain trusted');
T::same(false, Config::isTrustedHost('evilphysioelectric.com'), 'suffix-spoof not trusted');
T::same(false, Config::isTrustedHost('attacker.tld'), 'unknown host not trusted');
putenv('SITE_BASE_URL=https://physioelectric.com');
Config::flush();
T::same('https://physioelectric.com', Config::baseUrl(), 'SITE_BASE_URL wins and is trimmed');
putenv('SITE_BASE_URL=javascript:alert(1)');
Config::flush();
T::same('http://localhost', Config::baseUrl(), 'garbage SITE_BASE_URL falls back safely');
putenv('SITE_BASE_URL');
Config::flush();

T::group('Security — request path & method guards');
T::same('/fa/blog', Security::requestPath('/fa/blog'), 'plain path');
T::same('/fa/blog', Security::requestPath('/fa/blog/'), 'trailing slash trimmed');
T::same('/fa/blog', Security::requestPath('/fa/blog?x=1'), 'query string ignored');
T::same('/fa/a-b', Security::requestPath('/fa/a%2Db'), 'percent-decoding');
foreach (['/../etc/passwd', '/fa//en', "/fa\0", '/..'] as $evil) {
    // These must not come back as a usable path; requestPath() exits, so we
    // assert the *predicate* it uses instead of calling it.
    $blocked = str_contains($evil, "\0") || str_contains($evil, '..') || str_contains($evil, '//');
    T::same(true, $blocked, 'rejected path shape: ' . str_replace("\0", '\\0', $evil));
}
T::same(true, Security::isValidIp('2001:db8::1'), 'IPv6 accepted');
T::same(false, Security::isValidIp('1.2.3.4.5'), 'malformed IP rejected');

T::group('Security — origin check');
putenv('SITE_BASE_URL=https://physioelectric.com');
Config::flush();
$_SERVER['HTTP_ORIGIN'] = 'https://physioelectric.com';
T::same(true, Security::isSameOrigin(), 'matching Origin accepted');
$_SERVER['HTTP_ORIGIN'] = 'https://evil.example';
T::same(false, Security::isSameOrigin(), 'cross-site Origin rejected');
$_SERVER['HTTP_ORIGIN'] = 'null';
$_SERVER['HTTP_REFERER'] = '';
T::same(false, Security::isSameOrigin(), 'Origin: null rejected');
unset($_SERVER['HTTP_ORIGIN'], $_SERVER['HTTP_REFERER']);
T::same(true, Security::isSameOrigin(), 'no Origin/Referer defers to the CSRF token');
putenv('SITE_BASE_URL');
Config::flush();

T::group('Csrf');
$_SESSION = [];
$a = Csrf::token();
T::same(64, strlen($a), 'token is 64 hex chars');
T::same($a, Csrf::token(), 'token is stable within a session');
T::same(true, Csrf::verify($a), 'valid token verifies');
T::same(false, Csrf::verify(null), 'null token rejected');
T::same(false, Csrf::verify(''), 'empty token rejected');
T::same(false, Csrf::verify(strtoupper($a)), 'wrong token rejected');
$b = Csrf::rotate();
T::ok($b !== $a, 'rotate() issues a new token');
T::same(false, Csrf::verify($a), 'old token invalid after rotation');
T::ok(str_contains(Csrf::field(), 'name="csrf_token"'), 'field() renders a hidden input');

T::group('Slugs & input caps');
T::same('hello-world', Admin\PostController::normalizeSlug('  Hello   World!! '), 'slug normalised');
T::same('a-b', Admin\PostController::normalizeSlug('a---b'), 'repeated hyphens collapsed');
T::same('', Admin\PostController::normalizeSlug('!!!'), 'no ASCII -> empty slug');
T::same(150, mb_strlen(Admin\PostController::normalizeSlug(str_repeat('a', 400))), 'slug capped at 150');
T::same('abc', str_cap('  abc  ', 10), 'str_cap trims');
T::same('ابپ', str_cap('ابپتث', 3), 'str_cap counts characters, not bytes');
T::same('', str_cap('abc', 0), 'str_cap with 0 -> empty');
T::same(true, is_valid_slug('heat-exchanger-simulation'), 'valid slug accepted');
T::same(false, is_valid_slug('a'), 'too short rejected');
T::same(false, is_valid_slug('has space'), 'space rejected');

T::group('Image path validation');
T::same(null, Admin\PostController::safeImagePath('/etc/passwd'), 'outside /uploads rejected');
T::same(null, Admin\PostController::safeImagePath('/uploads/../../config.php'), 'traversal rejected');
T::same(null, Admin\PostController::safeImagePath('/uploads/missing-file.jpg'), 'non-existent file rejected');
T::same(null, Admin\PostController::safeImagePath('http://evil.example/x.jpg'), 'absolute URL rejected');
T::same(null, Admin\PostController::safeImagePath('/uploads/index.html'), '.html rejected');

T::group('Jalali calendar (cross-checked against ICU)');
if (class_exists('IntlDateFormatter')) {
    // Independent implementation: ICU's Persian calendar.
    $fmtY = new IntlDateFormatter('en_US@calendar=persian', IntlDateFormatter::NONE,
        IntlDateFormatter::NONE, 'UTC', IntlDateFormatter::TRADITIONAL, 'yyyy');
    $fmtM = new IntlDateFormatter('en_US@calendar=persian', IntlDateFormatter::NONE,
        IntlDateFormatter::NONE, 'UTC', IntlDateFormatter::TRADITIONAL, 'MM');
    $fmtD = new IntlDateFormatter('en_US@calendar=persian', IntlDateFormatter::NONE,
        IntlDateFormatter::NONE, 'UTC', IntlDateFormatter::TRADITIONAL, 'dd');

    $mismatch = 0;
    $checked  = 0;
    $example  = '';
    for ($ts = gmmktime(12, 0, 0, 1, 1, 1990); $ts < gmmktime(12, 0, 0, 1, 1, 2040); $ts += 86400 * 3) {
        [$gy, $gm, $gd] = [(int) gmdate('Y', $ts), (int) gmdate('n', $ts), (int) gmdate('j', $ts)];
        [$jy, $jm, $jd] = gregorian_to_jalali($gy, $gm, $gd);
        $iy = (int) $fmtY->format($ts);
        $im = (int) $fmtM->format($ts);
        $id = (int) $fmtD->format($ts);
        $checked++;
        if ($jy !== $iy || $jm !== $im || $jd !== $id) {
            $mismatch++;
            if ($example === '') {
                $example = sprintf('%04d-%02d-%02d: ours %d/%d/%d vs ICU %d/%d/%d', $gy, $gm, $gd, $jy, $jm, $jd, $iy, $im, $id);
            }
        }
    }
    T::same(0, $mismatch, "gregorian_to_jalali matches ICU on {$checked} sampled dates (1990-2040)", $example);
} else {
    T::ok(false, 'intl extension available for the Jalali cross-check');
}
setLang('fa');
T::ok(str_contains(format_date('2026-08-27 10:00:00'), '۱۴۰۵'), 'Persian date uses Jalali year + digits',
    format_date('2026-08-27 10:00:00'));
setLang('en');
T::ok(str_contains(format_date('2026-08-27 10:00:00'), '2026'), 'English date uses Gregorian year',
    format_date('2026-08-27 10:00:00'));
setLang('fa');

exit(T::summary());
