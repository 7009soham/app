<?php

namespace App\Helpers;

/**
 * Sanitiser for admin-authored HTML that is later rendered unescaped.
 *
 * Email templates and custom pages are written by administrators in a rich
 * text editor and then output with {!! !!}. An administrator is trusted, but
 * not unconditionally: a lower-privileged admin account, or a compromised one,
 * would otherwise have a direct stored-XSS path to every citizen who opens the
 * page or the email.
 *
 * The allowlist is intentionally narrow - formatting and links only. Anything
 * that can execute, submit, or embed is removed.
 */
class RichText
{
    /** Formatting tags an author legitimately needs. */
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><h1><h2><h3><h4><h5><h6>'
        . '<ul><ol><li><a><blockquote><hr><div><span><table><thead><tbody><tr><th><td><img>';

    /** Elements stripped whole, including any content between the tags. */
    private const FORBIDDEN_ELEMENTS = 'script|style|iframe|object|embed|form|input|button|textarea|select|link|meta|base';

    public static function sanitize(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        // Remove dangerous elements together with their contents, then any
        // self-closing or unclosed remnants.
        $html = preg_replace('/<(' . self::FORBIDDEN_ELEMENTS . ')\b[^>]*>.*?<\/\1>/is', '', $html) ?? $html;
        $html = preg_replace('/<(' . self::FORBIDDEN_ELEMENTS . ')\b[^>]*\/?>/is', '', $html) ?? $html;

        $html = strip_tags($html, self::ALLOWED_TAGS);

        // Inline event handlers, quoted and unquoted.
        $html = preg_replace('/\son[a-z]+\s*=\s*"[^"]*"/i', '', $html) ?? $html;
        $html = preg_replace("/\son[a-z]+\s*=\s*'[^']*'/i", '', $html) ?? $html;
        $html = preg_replace('/\son[a-z]+\s*=\s*[^\s>]+/i', '', $html) ?? $html;

        // Script-bearing URL schemes in href/src. \s* catches "java script:"
        // style padding that browsers historically tolerated.
        $html = preg_replace('/j\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t\s*:/i', '', $html) ?? $html;
        $html = preg_replace('/vbscript\s*:/i', '', $html) ?? $html;
        $html = preg_replace('/data\s*:\s*text\s*\/\s*html/i', '', $html) ?? $html;

        return trim($html);
    }

    /**
     * Plain-text excerpt, for meta descriptions and list previews.
     */
    public static function excerpt(?string $html, int $length = 160): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $html)) ?? '');

        if ($text === '' || mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length - 1) . '…';
    }
}
