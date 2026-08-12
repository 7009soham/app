<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

/**
 * Cache-busting for the hand-maintained files in public/.
 *
 * nginx serves public/css with `Cache-Control: max-age=315360000` and an
 * Expires date in 2037, and these filenames never change. A returning citizen
 * therefore keeps the stylesheet their browser fetched on their first visit -
 * a redesign shipped to production simply never appears for them.
 *
 * Appending the file's modification time gives the URL a new identity whenever
 * the file actually changes, so the long cache still applies to everything that
 * has not. Assets built through Vite are already fingerprinted and do not need
 * this.
 */
class Asset
{
    /**
     * asset() plus a ?v= stamp taken from the file's last modification time.
     */
    public static function versioned(string $path): string
    {
        $url = asset($path);
        $version = self::version($path);

        return $version === null
            ? $url
            : $url . (str_contains($url, '?') ? '&' : '?') . 'v=' . $version;
    }

    /**
     * Modification time, or null when the file is missing.
     *
     * Cached for a minute: this runs on every page render, and stat() on a
     * network filesystem is not free. A minute is short enough that a deploy is
     * visible almost immediately.
     */
    private static function version(string $path): ?int
    {
        return Cache::remember('asset_version_' . $path, 60, function () use ($path) {
            $full = public_path($path);

            return is_file($full) ? filemtime($full) : null;
        });
    }
}
