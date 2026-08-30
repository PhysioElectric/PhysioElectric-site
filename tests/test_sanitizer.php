<?php
declare(strict_types=1);

/**
 * HtmlSanitizer regression + XSS suite.
 * Run: php tests/test_sanitizer.php
 */
require __DIR__ . '/bootstrap.php';

T::group('HtmlSanitizer — the regression that caused HTTP 500');

// Any element carrying an attribute used to throw a TypeError.
$out = HtmlSanitizer::clean('<p>hi</p><a href="/fa/contact">link</a>');
T::ok(str_contains($out, 'href="/fa/contact"'), 'element with attributes survives', $out);
T::ok(str_contains($out, '>link</a>'), 'link text kept', $out);

$out = HtmlSanitizer::clean('<img src="/uploads/x.jpg" alt="a" title="t">');
T::ok(str_contains($out, 'src="/uploads/x.jpg"'), 'img attributes kept', $out);

$out = HtmlSanitizer::clean('<table><tr><td colspan="2">c</td></tr></table>');
T::ok(str_contains($out, 'colspan="2"'), 'td colspan kept', $out);

T::group('HtmlSanitizer — dangerous content is removed');

$cases = [
    'script tag'          => '<script>alert(1)</script>',
    'script w/ attrs'     => '<script src="//evil.example/x.js"></script>',
    'inline handler'      => '<p onclick="alert(1)">x</p>',
    'img onerror'         => '<img src="/uploads/a.jpg" onerror="alert(1)">',
    'iframe'              => '<iframe src="https://evil.example"></iframe>',
    'svg onload'          => '<svg onload="alert(1)"><circle r="9"/></svg>',
    'object'              => '<object data="x.swf"></object>',
    'form'                => '<form action="https://evil.example"><input name="a"></form>',
    'style tag'           => '<style>body{display:none}</style>',
    'meta refresh'        => '<meta http-equiv="refresh" content="0;url=https://evil.example">',
    'base href'           => '<base href="https://evil.example/">',
    'template'            => '<template><script>alert(1)</script></template>',
    'math mXSS'           => '<math><mtext><script>alert(1)</script></mtext></math>',
];
foreach ($cases as $label => $payload) {
    $out = HtmlSanitizer::clean($payload);
    T::ok(
        !preg_match('/<\s*(script|iframe|svg|object|form|style|meta|base|template|input|math)\b/i', $out)
            && !preg_match('/\bon(error|load|click|mouseover)\s*=/i', $out)
            && !str_contains($out, 'alert(1)'),
        "stripped: {$label}",
        $out
    );
}

T::group('HtmlSanitizer — URL scheme filtering');

$bad = [
    'javascript:'            => '<a href="javascript:alert(1)">x</a>',
    'JaVaScRiPt mixed case'  => '<a href="JaVaScRiPt:alert(1)">x</a>',
    'javascript w/ tab'      => "<a href=\"java\tscript:alert(1)\">x</a>",
    'javascript entity'      => '<a href="&#106;avascript:alert(1)">x</a>',
    'data: uri'              => '<a href="data:text/html,<script>alert(1)</script>">x</a>',
    'vbscript:'              => '<a href="vbscript:msgbox(1)">x</a>',
    'protocol-relative //'    => '<a href="//evil.example/x">x</a>',
    'protocol-relative /\\'   => '<a href="/\\evil.example/x">x</a>',
    'img data:'              => '<img src="data:image/svg+xml;base64,PHN2Zz48L3N2Zz4=">',
    'img external js host'   => '<img src="javascript:alert(1)">',
];
foreach ($bad as $label => $payload) {
    $out = HtmlSanitizer::clean($payload);
    T::ok(
        !preg_match('#(javascript|vbscript|data)\s*:#i', $out)
            && !preg_match('#(href|src)\s*=\s*"(//|/\\\\)#i', $out),
        "blocked: {$label}",
        $out
    );
}

$good = [
    'relative path'  => '<a href="/fa/projects">x</a>',
    'anchor'         => '<a href="#top">x</a>',
    'https'          => '<a href="https://example.com/a?b=1&amp;c=2">x</a>',
    'http'           => '<a href="http://example.com">x</a>',
    'mailto'         => '<a href="mailto:a@b.com">x</a>',
    'tel'            => '<a href="tel:+989120000000">x</a>',
    'uploads img'    => '<img src="/uploads/2026-a.jpg" alt="x">',
];
foreach ($good as $label => $payload) {
    $out = HtmlSanitizer::clean($payload);
    T::ok(str_contains($out, 'href=') || str_contains($out, 'src='), "allowed: {$label}", $out);
}

T::group('HtmlSanitizer — link hardening & edge cases');

$out = HtmlSanitizer::clean('<a href="https://x.example" target="_blank">x</a>');
T::ok(str_contains($out, 'rel="noopener noreferrer"'), 'target=_blank gets rel=noopener', $out);

$out = HtmlSanitizer::clean('<a href="/x" target="_self" rel="author">x</a>');
T::ok(!str_contains($out, 'target='), 'target=_self dropped', $out);
T::ok(!str_contains($out, 'rel='), 'author-supplied rel dropped', $out);

$out = HtmlSanitizer::clean('<a href="  /fa  ">x</a>');
T::ok(str_contains($out, 'href="/fa"'), 'href whitespace trimmed', $out);

$out = HtmlSanitizer::clean('</pe-root><script>alert(1)</script>');
T::ok(!str_contains($out, 'alert(1)'), 'marker-forging payload neutralised', $out);

$out = HtmlSanitizer::clean('<div><section>text</section></div>');
T::ok(str_contains($out, 'text') && !str_contains($out, '<section'), 'unknown tag unwrapped, text kept', $out);

T::same('', HtmlSanitizer::clean(null), 'null -> empty');
T::same('', HtmlSanitizer::clean('   '), 'blank -> empty');
T::same('', HtmlSanitizer::clean(str_repeat('a', HtmlSanitizer::MAX_INPUT_BYTES + 1)), 'oversized input -> empty (DoS guard)');

// UTF-8 must survive the DOM round-trip.
$out = HtmlSanitizer::clean('<p>شبیه‌سازی مبدل حرارتی — ۱۸٪</p>');
T::ok(str_contains($out, 'شبیه‌سازی'), 'Persian text preserved', $out);

exit(T::summary());
