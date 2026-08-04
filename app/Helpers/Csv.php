<?php

namespace App\Helpers;

/**
 * Helpers for CSV exports that Microsoft Excel opens correctly.
 *
 * Two Excel behaviours corrupt our exports if we ignore them:
 *
 * 1. Excel guesses a CSV's encoding. Without a UTF-8 byte order mark it falls
 *    back to the system codepage (Windows-1252 in India), which renders our
 *    Marathi and Hindi customer names as mojibake such as "à¤¨à¤¾à¤µ".
 *
 * 2. Excel converts long digit strings to numbers, so a 10-digit phone becomes
 *    8.55E+09 and a 12-digit Aadhaar becomes 8.55E+11 - the real value is lost
 *    on screen and on re-save. Wrapping the value in ="..." keeps it text.
 */
class Csv
{
    /**
     * UTF-8 byte order mark. Must be the first bytes of the response body.
     */
    public const BOM = "\xEF\xBB\xBF";

    /**
     * Write the BOM to an open output stream.
     */
    public static function writeBom($handle): void
    {
        fwrite($handle, self::BOM);
    }

    /**
     * Force a value to be treated as text by Excel.
     *
     * Used for identifiers that look numeric but must never be arithmetic:
     * phone numbers, Aadhaar numbers, receipt and bill numbers.
     */
    public static function text($value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        return '="' . str_replace('"', '""', $value) . '"';
    }
}
