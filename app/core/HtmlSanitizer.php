<?php
declare(strict_types=1);

/**
 * Whitelist-based HTML sanitizer for rich-text (WYSIWYG) content.
 *
 *  - Disallowed "dangerous" tags (script, iframe, svg, ...) are removed
 *    together with their content.
 *  - Other disallowed tags are unwrapped (children kept, tag dropped).
 *  - Only whitelisted attributes survive; href/src must use a safe scheme
 *    (http, https, mailto, tel, relative path or anchor).
 *  - Protocol-relative URLs (//evil.com, /\evil.com) are rejected: they used
 *    to pass the `$url[0] === '/'` test and could point anywhere.
 *  - target="_blank" is always paired with rel="noopener noreferrer".
 *
 * FIXED: the attribute loop called array_keys() on a DOMNamedNodeMap, which
 * throws a TypeError on PHP 8. That made *every* admin save containing a
 * link, image or table cell return HTTP 500.
 */
final class HtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'a', 'b', 'blockquote', 'br', 'code', 'div', 'em', 'h2', 'h3', 'h4',
        'hr', 'i', 'img', 'li', 'ol', 'p', 'pre', 'span', 'strong', 'sub',
        'sup', 'table', 'tbody', 'td', 'th', 'thead', 'tr', 'u', 'ul',
    ];

    private const STRIP_TAGS = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'input',
        'textarea', 'select', 'button', 'link', 'meta', 'title', 'svg',
        'canvas', 'audio', 'video', 'source', 'noscript', 'template',
        'base', 'frame', 'frameset', 'applet', 'marquee', 'math', 'xml',
        // (portal/dialog/details/slot are intentionally NOT listed: they are
        //  unwrapped instead, so their text content survives.)
    ];

    private const ALLOWED_ATTRS = [
        'a'   => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
        'td'  => ['colspan', 'rowspan'],
        'th'  => ['colspan', 'rowspan', 'scope'],
    ];

    /** Attributes that only accept a small set of keywords/numbers. */
    private const ENUM_ATTRS = [
        'target'  => ['_blank', '_self'],
        'loading' => ['lazy', 'eager'],
        'scope'   => ['col', 'row', 'colgroup', 'rowgroup'],
    ];

    /** Hard cap on raw input; DOM parsing of huge payloads is a DoS vector. */
    public const MAX_INPUT_BYTES = 512 * 1024;

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }
        if (strlen($html) > self::MAX_INPUT_BYTES) {
            Security::audit('sanitizer.input_too_large', ['bytes' => strlen($html)]);
            return '';
        }
        if (!class_exists('DOMDocument')) {
            // Fail closed rather than storing unsanitized markup.
            error_log('[HtmlSanitizer] ext-dom missing; refusing to store content.');
            return '';
        }

        // Random marker so user content cannot forge or close it.
        $marker = 'pe' . bin2hex(random_bytes(6));

        $dom = new DOMDocument('1.0', 'UTF-8');
        $prev = libxml_use_internal_errors(true);
        $ok = $dom->loadHTML(
            '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>'
            . '<body><' . $marker . '>' . $html . '</' . $marker . '></body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING
            | LIBXML_NONET
        );
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        if (!$ok) {
            Security::audit('sanitizer.parse_failed');
            return '';
        }

        $xpath = new DOMXPath($dom);

        // Locate the wrapper first: it must never be treated as content.
        $rootList = $xpath->query('//' . $marker);
        if ($rootList === false || $rootList->length === 0) {
            return '';
        }
        $rootNode = $rootList->item(0);
        if (!$rootNode instanceof DOMElement) {
            return '';
        }

        $elements = $xpath->query('.//*', $rootNode);
        if ($elements === false) {
            return '';
        }

        // Snapshot first: the loop mutates the tree.
        $nodes = [];
        foreach ($elements as $el) {
            if ($el instanceof DOMElement) {
                $nodes[] = $el;
            }
        }

        foreach ($nodes as $el) {
            if ($el->parentNode === null) {
                continue; // already removed as part of a stripped ancestor
            }
            $tag = strtolower($el->tagName);

            if (in_array($tag, self::STRIP_TAGS, true)) {
                $el->parentNode->removeChild($el);
                continue;
            }

            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                $parent = $el->parentNode;
                while (($first = $el->firstChild) !== null) {
                    $parent->insertBefore($first, $el);
                }
                $parent->removeChild($el);
                continue;
            }

            self::sanitizeAttributes($el, $tag);
        }

        // Serialize the marker's children only (no document wrapper).
        $out = '';
        foreach ($rootNode->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }
        return is_string($out) ? $out : '';
    }

    private static function sanitizeAttributes(DOMElement $el, string $tag): void
    {
        $allowed = self::ALLOWED_ATTRS[$tag] ?? [];

        // Collect names first — DOMNamedNodeMap is not an array and must not
        // be mutated while being walked.
        $names = [];
        if ($el->hasAttributes()) {
            foreach ($el->attributes as $attr) {
                if ($attr instanceof DOMAttr) {
                    $names[] = strtolower($attr->nodeName);
                }
            }
        }

        foreach ($names as $name) {
            // Reject namespaced / prefixed attributes (xlink:href, xml:...).
            if (!in_array($name, $allowed, true) || str_contains($name, ':')) {
                $el->removeAttribute($name);
                continue;
            }

            $value = trim((string) $el->getAttribute($name));

            if (in_array($name, ['href', 'src'], true)) {
                if ($value === '' || !self::isSafeUrl($value)) {
                    if ($tag === 'img') {
                        $el->parentNode?->removeChild($el);
                        return;
                    }
                    $el->removeAttribute($name);
                    continue;
                }
                $el->setAttribute($name, $value);
                continue;
            }

            if (isset(self::ENUM_ATTRS[$name])) {
                $value = strtolower($value);
                if (!in_array($value, self::ENUM_ATTRS[$name], true)) {
                    $el->removeAttribute($name);
                    continue;
                }
            }

            if ($tag === 'a' && $name === 'target') {
                if ($value !== '_blank') {
                    $el->removeAttribute('target');
                    continue;
                }
                $el->setAttribute('rel', 'noopener noreferrer');
            }

            if (in_array($name, ['colspan', 'rowspan', 'width', 'height'], true)) {
                if (!preg_match('/^\d{1,5}$/', $value)) {
                    $el->removeAttribute($name);
                    continue;
                }
            }

            if (in_array($name, ['title', 'alt'], true)) {
                $el->setAttribute($name, mb_substr($value, 0, 255));
                continue;
            }

            // rel is only ever set by us; drop any author-supplied value.
            if ($name === 'rel' && $el->getAttribute('target') !== '_blank') {
                $el->removeAttribute('rel');
                continue;
            }

            $el->setAttribute($name, $value);
        }
    }

    /**
     * Safe URL schemes: relative paths, anchors and http(s)/mailto/tel.
     * Everything else (javascript:, data:, vbscript:, blob:, and
     * protocol-relative //host) is rejected.
     */
    public static function isSafeUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }

        // Decode entities once so &#106;avascript: cannot sneak through.
        $decoded = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Strip control characters/whitespace attackers use to obfuscate schemes.
        $compact = strtolower((string) preg_replace('/[\s\x00-\x20]+/', '', $decoded));
        if ($compact === '') {
            return false;
        }

        // Protocol-relative and scheme-obfuscating forms.
        if (str_starts_with($compact, '//')
            || str_starts_with($compact, '/\\')
            || str_starts_with($compact, '\\')
            || str_contains($compact, '%2f%2f')
            || str_contains($compact, '%5c')) {
            return false;
        }

        if ($compact[0] === '#' || $compact[0] === '/') {
            return true; // same-document anchor or absolute path
        }
        if (!str_contains($compact, ':')) {
            return true; // bare relative path
        }
        return (bool) preg_match('#^(https?|mailto|tel):#', $compact);
    }
}
