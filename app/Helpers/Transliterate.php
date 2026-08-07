<?php

namespace App\Helpers;

use Transliterator;

/**
 * Romanises Devanagari so English typing finds Marathi and Hindi names.
 *
 * Records were imported with names in Devanagari ("सोहम तारे"), but staff at
 * the counter type on an English keyboard. A LIKE against the stored name can
 * never match, so each searchable name is also stored romanised
 * ("sohama tare") and the search runs against both.
 *
 * ICU keeps the inherent vowel, so सोहम becomes "sohama" rather than "soham".
 * That is fine for substring matching - "soham" is contained in "sohama" - and
 * is why the search uses LIKE %term% rather than an exact comparison.
 */
class Transliterate
{
    private static ?Transliterator $transliterator = null;
    private static bool $attempted = false;

    /**
     * Romanised, lowercased form suitable for storing in a *_roman column.
     * Returns '' when there is nothing useful to index.
     */
    public static function toRoman(?string $text): string
    {
        $text = trim((string) $text);

        if ($text === '') {
            return '';
        }

        // Already plain ASCII: just normalise case and spacing.
        if (!preg_match('/[^\x20-\x7E]/', $text)) {
            return self::tidy(mb_strtolower($text));
        }

        $transliterator = self::transliterator();

        if ($transliterator === null) {
            // Without intl we cannot romanise; storing the original keeps the
            // column consistent and search simply falls back to exact matching.
            return self::tidy(mb_strtolower($text));
        }

        $romanised = $transliterator->transliterate($text);

        return self::tidy(mb_strtolower($romanised !== false ? $romanised : $text));
    }

    /**
     * The value actually stored in a *_roman column and used for matching.
     *
     * Romanisation alone is not enough. ICU maps श to "ś" and Latin-ASCII then
     * drops the diacritic, so राजेश becomes "rajesa" while staff type
     * "rajesh" - no match. Likewise नीता becomes "nita" but is typed "neeta".
     *
     * Folding the same way on both the stored value and the query removes that
     * whole class of near-miss. It is deliberately lossy: for a search box a
     * few extra results are far better than a name that cannot be found.
     */
    public static function toSearchKey(?string $text): string
    {
        return self::fold(self::toRoman($text));
    }

    /**
     * True when the string contains Devanagari characters.
     */
    public static function isDevanagari(?string $text): bool
    {
        return (bool) preg_match('/\p{Devanagari}/u', (string) $text);
    }

    /**
     * Collapse spellings that sound alike to one canonical form.
     */
    private static function fold(string $text): string
    {
        if ($text === '') {
            return '';
        }

        // Long vowels as typed by hand vs as transliterated by ICU.
        $text = strtr($text, [
            'aa' => 'a',
            'ee' => 'i',
            'ii' => 'i',
            'oo' => 'u',
            'uu' => 'u',
        ]);

        // Aspirated consonants: ICU keeps the "h", hand-typed spellings vary,
        // and the diacritic-stripped forms lose it entirely.
        $text = strtr($text, [
            'kh' => 'k',
            'gh' => 'g',
            'ch' => 'c',
            'jh' => 'j',
            'th' => 't',
            'dh' => 'd',
            'ph' => 'p',
            'bh' => 'b',
            'sh' => 's',
            'zh' => 'z',
        ]);

        // v and w are used interchangeably in Indian names.
        $text = strtr($text, ['w' => 'v']);

        // Any remaining doubled letter.
        $text = preg_replace('/(.)\1+/u', '$1', $text) ?? $text;

        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
    }

    private static function transliterator(): ?Transliterator
    {
        if (self::$attempted) {
            return self::$transliterator;
        }

        self::$attempted = true;

        if (!class_exists(Transliterator::class)) {
            return null;
        }

        // Any-Latin rather than Devanagari-Latin so mixed-script names, and the
        // occasional record in another Indic script, are still handled.
        self::$transliterator = Transliterator::create('Any-Latin; Latin-ASCII; Lower');

        return self::$transliterator;
    }

    /**
     * Collapse whitespace and drop punctuation that would break substring
     * matching, so "tare, soham" and "Tare Soham" index the same way.
     */
    private static function tidy(string $text): string
    {
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }
}
