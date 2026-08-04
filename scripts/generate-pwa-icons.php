<?php

/**
 * Generates the PWA icon set from the site's own palette.
 *
 * Draws a classical panchayat-office facade (pediment, columns, plinth) in
 * white on the navy already used as --color-primary, so the installed app
 * matches the site header. Everything is rendered on a 1024px master and
 * resampled down, which is what keeps the small sizes from looking ragged.
 *
 * Replace public/icons/* with the official emblem when one exists - no code
 * references the artwork itself, only the filenames.
 *
 * Usage: php scripts/generate-pwa-icons.php
 */

const MASTER = 1024;
const NAVY = [0x1a, 0x36, 0x5d];

/**
 * @param float $glyphScale fraction of the canvas the building occupies.
 *                          Maskable icons need a smaller glyph because Android
 *                          crops to a circle inscribed in the middle 80%.
 */
function drawMaster(float $glyphScale)
{
    $im = imagecreatetruecolor(MASTER, MASTER);
    imagealphablending($im, true);

    $navy = imagecolorallocate($im, NAVY[0], NAVY[1], NAVY[2]);
    $white = imagecolorallocate($im, 255, 255, 255);

    imagefilledrectangle($im, 0, 0, MASTER, MASTER, $navy);

    // Work in a centred box of $glyphScale, then map 0..1 coords into it.
    $box = MASTER * $glyphScale;
    $ox = (MASTER - $box) / 2;
    $oy = (MASTER - $box) / 2;
    $px = fn(float $u) => (int) round($ox + $u * $box);
    $py = fn(float $v) => (int) round($oy + $v * $box);

    // Pediment (roof triangle).
    imagefilledpolygon($im, [
        $px(0.50), $py(0.02),
        $px(0.97), $py(0.30),
        $px(0.03), $py(0.30),
    ], $white);

    // Architrave under the roof.
    imagefilledrectangle($im, $px(0.07), $py(0.32), $px(0.93), $py(0.40), $white);

    // Four columns.
    $colTop = 0.44;
    $colBottom = 0.80;
    $left = 0.13;
    $right = 0.87;
    $colWidth = 0.10;
    $span = $right - $left - $colWidth;
    for ($i = 0; $i < 4; $i++) {
        $u = $left + ($span * $i / 3);
        imagefilledrectangle($im, $px($u), $py($colTop), $px($u + $colWidth), $py($colBottom), $white);
    }

    // Plinth and step.
    imagefilledrectangle($im, $px(0.07), $py(0.83), $px(0.93), $py(0.90), $white);
    imagefilledrectangle($im, $px(0.01), $py(0.93), $px(0.99), $py(1.00), $white);

    return $im;
}

function emit($master, string $path, int $size): void
{
    $out = imagecreatetruecolor($size, $size);
    imagecopyresampled($out, $master, 0, 0, 0, 0, $size, $size, MASTER, MASTER);
    imagepng($out, $path, 9);
    imagedestroy($out);
    printf("%-46s %dx%d\n", $path, $size, $size);
}

$dir = __DIR__ . '/../public/icons';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$standard = drawMaster(0.72);
emit($standard, "$dir/icon-192.png", 192);
emit($standard, "$dir/icon-512.png", 512);
emit($standard, "$dir/apple-touch-icon.png", 180);
emit($standard, "$dir/favicon-32.png", 32);
emit($standard, "$dir/favicon-16.png", 16);

// Android crops maskable icons to a circle, so the glyph sits well inside.
$maskable = drawMaster(0.54);
emit($maskable, "$dir/icon-maskable-192.png", 192);
emit($maskable, "$dir/icon-maskable-512.png", 512);

imagedestroy($standard);
imagedestroy($maskable);

echo "\nDone. Replace these with the official emblem when available.\n";
