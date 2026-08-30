<?php
declare(strict_types=1);

/** Current language (set by the router before any view renders). */
$GLOBALS['__lang'] = 'fa';

/**
 * Escape for HTML output (XSS-safe).
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Truncate to $max characters (not bytes) so a value can never overflow a
 * VARCHAR column and turn into a SQLSTATE[22001] 500.
 */
function str_cap(string $value, int $max): string
{
    $value = trim($value);
    if ($max <= 0) {
        return '';
    }
    return mb_strlen($value) > $max ? mb_substr($value, 0, $max) : $value;
}

/**
 * Read a scalar request value. Arrays (e.g. `field[]`) collapse to '' so a
 * malformed body can never reach a `(string)` cast and emit a warning.
 */
function input_str(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? null;
    return is_scalar($v) ? (string) $v : $default;
}

/** Slugs are ASCII, 2-150 chars, hyphen separated. */
function is_valid_slug(string $s): bool
{
    return (bool) preg_match('/^[A-Za-z0-9\-]{2,150}$/', $s);
}

/** Read a request value that must be a whole number. */
function input_int(string $key, int $default = 0): int
{
    $v = $_POST[$key] ?? null;
    if (!is_scalar($v)) {
        return $default;
    }
    $s = trim((string) $v);
    return preg_match('/^-?\d{1,9}$/', $s) === 1 ? (int) $s : $default;
}

/**
 * UI translation for the active language with {placeholders}.
 */
function t(string $key, array $replacements = []): string
{
    static $dict = null;
    if ($dict === null) {
        $dict = require BASE_PATH . '/core/lang.php';
    }
    $lang = lang();
    $value = $dict[$lang][$key] ?? $dict['fa'][$key] ?? $key;
    if ($replacements) {
        foreach ($replacements as $k => $v) {
            $value = str_replace('{' . $k . '}', (string) $v, $value);
        }
    }
    return $value;
}

function lang(): string
{
    return $GLOBALS['__lang'] ?? 'fa';
}

function setLang(string $lang): void
{
    $GLOBALS['__lang'] = in_array($lang, ['fa', 'en'], true) ? $lang : 'fa';
}

function altLang(): string
{
    return lang() === 'fa' ? 'en' : 'fa';
}

/**
 * Language-aware column value: L($row, 'title') -> $row['title_fa'|'title_en'].
 * Falls back to the other language when the active one is empty.
 */
function L(array $row, string $field, ?string $default = ''): string
{
    $l = lang();
    $a = trim((string) ($row[$field . '_' . $l] ?? ''));
    if ($a !== '') {
        return $a;
    }
    $other = $l === 'fa' ? 'en' : 'fa';
    $b = trim((string) ($row[$field . '_' . $other] ?? ''));
    return $b !== '' ? $b : (string) $default;
}

/**
 * Slug of the row in the active language (with fallback).
 */
function slugOf(array $row, ?string $default = null): string
{
    $l = lang();
    $a = trim((string) ($row['slug_' . $l] ?? ''));
    if ($a !== '') {
        return $a;
    }
    $other = $l === 'fa' ? 'en' : 'fa';
    $b = trim((string) ($row['slug_' . $other] ?? ''));
    return $b !== '' ? $b : (string) $default;
}

/**
 * Internal URL with language prefix: url('fa', 'blog/my-post') -> /fa/blog/my-post
 */
function url(string $lang, string $path = ''): string
{
    $path = trim($path, '/');
    return '/' . $lang . ($path !== '' ? '/' . $path : '');
}

/** Absolute public URL (canonical / OG / hreflang). */
function absUrl(string $path = ''): string
{
    return Config::baseUrl() . ($path === '' ? '' : '/' . ltrim($path, '/'));
}

/**
 * Absolute URL of this page in the other language (hreflang).
 * $path must be the language-specific path (e.g. /fa/projects/foo).
 */
function altAbsUrl(string $path): string
{
    $current = lang();
    $other   = altLang();
    $newPath = preg_replace('#^/' . $current . '(?=/|$)#', '/' . $other, $path);
    if ($newPath === $path) {
        $newPath = '/' . $other . $path;
    }
    return Config::baseUrl() . $newPath;
}

function redirect(string $to, int $code = 302): never
{
    header('Location: ' . $to, true, $code);
    exit;
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Render a view inside the public layout.
 * Expects $seo = [title, description, keywords?, image?, type?, url?, jsonld?].
 */
function view(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $seo = $data['seo'] ?? [];

    // <head> with meta/hreflang/OG/JSON-LD, then <body> + nav
    seo_head($seo);
    require BASE_PATH . '/views/layouts/header.php';
    require BASE_PATH . '/views/' . $template . '.php';
    require BASE_PATH . '/views/layouts/footer.php';
}

/** Render an admin view inside the admin layout. */
function admin_view(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);
    // 'login' ships its own full layout.
    if ($template !== 'login') {
        require BASE_PATH . '/views/admin/layouts/header.php';
    }
    require BASE_PATH . '/views/admin/' . $template . '.php';
    if ($template !== 'login') {
        require BASE_PATH . '/views/admin/layouts/footer.php';
    }
}

// ---------------------------------------------------------------------
//  Settings (cached per request)
// ---------------------------------------------------------------------
$GLOBALS['__settings'] = null;

function settings(): array
{
    if ($GLOBALS['__settings'] === null) {
        $rows = Database::pdo()->query('SELECT skey, svalue FROM settings')->fetchAll();
        $map  = [];
        foreach ($rows as $r) {
            $map[$r['skey']] = (string) $r['svalue'];
        }
        $GLOBALS['__settings'] = $map;
    }
    return $GLOBALS['__settings'];
}

function setting(string $key, ?string $default = null): ?string
{
    $v = settings()[$key] ?? null;
    return ($v === null || $v === '') ? $default : $v;
}

// ---------------------------------------------------------------------
//  Dates & text helpers
// ---------------------------------------------------------------------
/**
 * Format a DATETIME string for the active language.
 * FA -> Jalali calendar with Persian month names (pure PHP, no ICU
 * dependency), EN -> "18 Jul, 2026" via intl (fallback: PHP date()).
 */
function format_date(?string $datetime): string
{
    if ($datetime === null || $datetime === '') {
        return '';
    }
    $ts = strtotime($datetime);
    if ($ts === false) {
        return $datetime;
    }

    if (lang() === 'fa') {
        [$jy, $jm, $jd] = gregorian_to_jalali(
            (int) date('Y', $ts),
            (int) date('n', $ts),
            (int) date('j', $ts)
        );
        $months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
                   'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
        return to_fa_digits($jd) . ' ' . $months[$jm - 1] . ' ' . to_fa_digits($jy);
    }

    if (class_exists('IntlDateFormatter')) {
        try {
            $fmt = new IntlDateFormatter('en-GB', IntlDateFormatter::SHORT,
                IntlDateFormatter::NONE, null, null, 'd MMM, Y');
            $out = $fmt->format($ts);
            if (is_string($out) && $out !== '') {
                return $out;
            }
        } catch (Throwable) {
            // fall through
        }
    }
    return date('j M, Y', $ts);
}

/** Gregorian -> Jalali (solar) calendar. Classic jdf algorithm. */
function gregorian_to_jalali(int $gy, int $gm, int $gd): array
{
    $gDaysInMonth = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    // The 355666 epoch is calibrated so that the 365-day term uses the
    // raw Gregorian year while the leap corrections use the year that
    // has already advanced past February.
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = 355666
        + (365 * $gy)
        + (int) (($gy2 + 3) / 4)
        - (int) (($gy2 + 99) / 100)
        + (int) (($gy2 + 399) / 400)
        + $gd
        + $gDaysInMonth[$gm - 1];

    $jy = -1595 + (33 * (int) ($days / 12053));
    $days %= 12053;
    $jy += 4 * (int) ($days / 1461);
    $days %= 1461;

    if ($days > 365) {
        $jy += (int) (($days - 1) / 365);
        $days = ($days - 1) % 365;
    }

    if ($days < 186) {
        $jm = 1 + (int) ($days / 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + (int) (($days - 186) / 30);
        $jd = 1 + (($days - 186) % 30);
    }
    return [$jy, $jm, $jd];
}

/** Convert Latin digits to Persian digits. */
function to_fa_digits(int|string $value): string
{
    $map = ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹'];
    return strtr((string) $value, $map);
}

/** Rough reading time in minutes from stripped HTML. */
function reading_time(?string $html): int
{
    $text = trim(strip_tags((string) $html));
    $words = $text === '' ? 0 : str_word_count($text);
    return max(1, (int) ceil($words / 180));
}

/** Split comma separated tech tags into a clean list. */
function tech_tags(?string $csv): array
{
    $parts = array_map('trim', explode(',', (string) $csv));
    return array_values(array_filter($parts, fn($p) => $p !== ''));
}

// ---------------------------------------------------------------------
//  CTA: Telegram (tg:// deep link + https fallback) and mailto
// ---------------------------------------------------------------------
function telegram_user(): string
{
    return (string) setting('telegram_user', 'physioelectric');
}

/**
 * Primary CTA target. Mobile users get the tg:// deep link (the JS in
 * main.js falls back to the web link if the app is not installed),
 * desktop users get the web version. The href itself is https so the
 * link works even without JavaScript.
 */
function cta_telegram_url(?string $projectTitle = null): string
{
    return 'https://t.me/' . rawurlencode(telegram_user());
}

function cta_tg_scheme(): string
{
    return 'tg://resolve?domain=' . rawurlencode(telegram_user());
}

function cta_mailto_url(?string $projectTitle = null): string
{
    $email = (string) setting('contact_email', 'info@physioelectric.com');
    // Email addresses never contain ?&#+, so the address itself needs no
    // encoding; only the subject and body are percent-encoded.
    if ($projectTitle !== null && $projectTitle !== '') {
        $subject = t('cta.mailSubj', ['title' => $projectTitle]);
        $body    = t('cta.tgMsg', ['title' => $projectTitle]);
        return 'mailto:' . $email
            . '?subject=' . rawurlencode($subject)
            . '&body=' . rawurlencode($body);
    }
    return 'mailto:' . $email;
}

// ---------------------------------------------------------------------
//  SEO: <head> meta, hreflang, OG, Twitter Cards, JSON-LD
// ---------------------------------------------------------------------

/**
 * $seo = [
 *   'title'       => string (required)
 *   'description' => string (required)
 *   'keywords'    => string (optional)
 *   'image'       => absolute or /-relative image (optional)
 *   'type'        => 'website' | 'article' (default website)
 *   'url'         => current language path e.g. '/fa/blog/foo' (required for canonical)
 *   'jsonld'      => array of raw arrays (extra JSON-LD blocks, optional)
 *   'noindex'     => bool
 * ]
 */
function seo_head(array $seo): void
{
    $lang  = lang();
    $dir   = $lang === 'fa' ? 'rtl' : 'ltr';
    $title = (string) ($seo['title'] ?? t('meta.home'));
    $desc  = (string) ($seo['description'] ?? '');
    $kw    = (string) ($seo['keywords'] ?? t('meta.keywords'));
    $type  = (string) ($seo['type'] ?? 'website');
    $path  = (string) ($seo['url'] ?? url($lang));
    $site  = (string) setting('site_name', 'PhysioElectric');
    $tagline = setting($lang === 'fa' ? 'site_tagline_fa' : 'site_tagline_en',
                       setting('site_tagline_fa', 'Engineering Ideas, Intelligent Solutions'));

    $image = (string) ($seo['image'] ?? '');
    if ($image !== '' && $image[0] !== '/') {
        $image = '/' . $image;
    }
    $absImage = $image !== ''
        ? Config::baseUrl() . $image
        : Config::baseUrl() . '/assets/images/og-default.svg';

    $locale = $lang === 'fa' ? 'fa_IR' : 'en_US';

    // Hreflang pair. Controllers may override 'hreflang_fa'/'hreflang_en'
    // when the two languages use different slugs.
    $faUrl = (string) ($seo['hreflang_fa'] ?? ($lang === 'fa' ? Config::baseUrl() . $path : altAbsUrl($path)));
    $enUrl = (string) ($seo['hreflang_en'] ?? ($lang === 'en' ? Config::baseUrl() . $path : altAbsUrl($path)));
    $xdef  = (string) ($seo['x_default'] ?? $faUrl);

    $jsonld = [];
    $jsonld[] = [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => $site,
        'url'      => Config::baseUrl() . url($lang),
        'logo'     => Config::baseUrl() . '/assets/images/logo.svg',
        'description' => $desc,
        'sameAs'  => [
            'https://t.me/' . rawurlencode(telegram_user()),
            'mailto:' . (string) setting('contact_email', 'info@physioelectric.com'),
        ],
    ];
    if (isset($seo['jsonld']) && is_array($seo['jsonld'])) {
        foreach ($seo['jsonld'] as $block) {
            if (is_array($block)) {
                $jsonld[] = $block;
            }
        }
    }
    ?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>" dir="<?= e($dir) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title . ' | ' . $site) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta name="keywords" content="<?= e($kw) ?>">
<meta name="generator" content="">
<?php if (!empty($seo['noindex'])): ?><meta name="robots" content="noindex, nofollow"><?php endif; ?>
<link rel="canonical" href="<?= e(Config::baseUrl() . $path) ?>">

<!-- Hreflang: tell search engines about the translated versions -->
<link rel="alternate" hreflang="fa" href="<?= e($faUrl) ?>">
<link rel="alternate" hreflang="en" href="<?= e($enUrl) ?>">
<link rel="alternate" hreflang="x-default" href="<?= e($xdef) ?>">

<!-- Open Graph -->
<meta property="og:site_name" content="<?= e($site) ?>">
<meta property="og:locale" content="<?= e($locale) ?>">
<meta property="og:type" content="<?= e($type) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e(Config::baseUrl() . $path) ?>">
<meta property="og:image" content="<?= e($absImage) ?>">
<meta property="og:image:alt" content="<?= e($title) ?>">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e($desc) ?>">
<meta name="twitter:image" content="<?= e($absImage) ?>">

<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="apple-touch-icon" href="/assets/images/favicon.svg">
<meta name="theme-color" content="#0f172a">

<!-- Fonts (local, offline-safe) -->
<link rel="stylesheet" href="/assets/fonts/fonts.css">

<!-- Tailwind (local build of the play CDN used by the original design) -->
<script src="/assets/js/tailwind.js"></script>
<script nonce="<?= e(\Security::nonce()) ?>">
tailwind.config = {
    theme: {
        extend: {
            fontFamily: { sans: ['Inter', 'Vazirmatn', 'sans-serif'] },
            colors: {
                physio: {
                    50:  '#f0f9ff',
                    100: '#e0f2fe',
                    200: '#bae6fd',
                    300: '#7dd3fc',
                    400: '#38bdf8',
                    500: '#0ea5e9',
                    600: '#0284c7',
                    900: '#0f172a',
                    950: '#020617',
                }
            },
            boxShadow: {
                'premium': '0 4px 24px -4px rgba(0, 0, 0, 0.05), 0 16px 32px -8px rgba(0, 0, 0, 0.02)',
                'glow': '0 0 40px -10px rgba(14, 165, 233, 0.5)',
            },
            backgroundImage: { 'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))' },
            animation: { 'spin-slow': 'spin 12s linear infinite' },
        }
    }
}
</script>
<link rel="stylesheet" href="/assets/css/style.css">
<?php foreach ($jsonld as $block): ?>
<script type="application/ld+json" nonce="<?= e(\Security::nonce()) ?>"><?= json_encode($block, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
<?php endforeach; ?>
</head>
<body class="antialiased font-sans bg-[var(--bg-color)] text-[var(--text-main)] transition-colors duration-300">
<?php
}
