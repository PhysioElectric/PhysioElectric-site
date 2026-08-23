<?php
declare(strict_types=1);

/**
 * Whitelist-based HTML sanitizer for rich-text (WYSIWYG) content.
 *
 *  - Disallowed "dangerous" tags (script, iframe, svg, ...) are removed
 *    together with their content.
 *  - Other disallowed tags are unwrapped (children kept, tag dropped).
 *  - Only whitelisted attributes survive; href/src must use a safe
 *    scheme (http, https, mailto, tel, relative, anchor).
 *  - target="_blank" is always paired with rel="noopener noreferrer".
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
        'base', 'frame', 'frameset', 'applet', 'marquee',
    ];

    private const ALLOWED_ATTRS = [
        'a'   => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title'],
        'td'  => ['colspan', 'rowspan'],
        'th'  => ['colspan', 'rowspan', 'scope'],
    ];

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $ok = $dom->loadHTML(
            '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>'
            . '<body><pe-root>' . $html . '</pe-root></body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        if (!$ok) {
            return '';
        }

        $xpath = new DOMXPath($dom);
        $elements = $xpath->query('//body//*');

        foreach ($elements as $el) {
            if (!($el instanceof DOMElement) || $el->parentNode === null) {
                continue;
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

            // --- filter attributes -------------------------------------
            $allowed = self::ALLOWED_ATTRS[$tag] ?? [];
            $names = $el->hasAttributes()
                ? array_map('strval', array_keys($el->attributes))
                : [];

            foreach ($names as $name) {
                $name = strtolower($name);
                if (!in_array($name, $allowed, true)) {
                    $el->removeAttribute($name);
                    continue;
                }
                if (in_array($name, ['href', 'src'], true)
                    && !self::isSafeUrl((string) $el->getAttribute($name))) {
                    if ($tag === 'img') {
                        $el->parentNode?->removeChild($el);
                    } else {
                        $el->removeAttribute($name);
                    }
                    continue;
                }
                if ($tag === 'a' && $name === 'target'
                    && strtolower($el->getAttribute('target')) === '_blank') {
                    $el->setAttribute('rel', 'noopener noreferrer');
                }
            }
        }

        $out = $dom->saveHTML();
        $start = strpos($out, '<pe-root>');
        $end   = strpos($out, '</pe-root>');
        if ($start === false || $end === false) {
            return '';
        }
        return substr($out, $start + strlen('<pe-root>'), $end - $start - strlen('<pe-root>'));
    }

    /**
     * Safe URL schemes: relative paths, anchors and http(s)/mailto/tel.
     * Everything else (javascript:, data:, vbscript:, ...) is rejected.
     */
    private static function isSafeUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || $url[0] === '#' || $url[0] === '/') {
            return true;
        }
        // Strip whitespace/control chars attackers use to obfuscate schemes.
        $compact = strtolower((string) preg_replace('/[\s\x00-\x20]+/', '', $url));
        return (bool) preg_match('#^(https?|mailto|tel):#', $compact);
    }
}
