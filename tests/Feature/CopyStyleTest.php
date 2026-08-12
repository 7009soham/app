<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Em and en dashes read as machine-written filler in this portal's copy, and
 * they are also the hardest punctuation to reproduce consistently across the
 * English, Hindi and Marathi strings. Prose here uses a comma, a colon or a
 * full stop instead; ranges spell out "to".
 *
 * Code comments are exempt: they are not copy, and rewriting them would churn
 * files with no visible effect.
 */
class CopyStyleTest extends TestCase
{
    private const DASHES = ['—', '–'];

    /** @return array<string, array{0: string}> */
    public static function copyFiles(): array
    {
        // A data provider is static and runs before the application boots, so
        // base_path() is not available here.
        $root = str_replace('\\', '/', dirname(__DIR__, 2));
        $files = [];

        foreach (["{$root}/resources/views", "{$root}/resources/lang"] as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $walker = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));

            foreach ($walker as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    // Windows hands back mixed separators, so normalise before
                    // trimming the root off to get a readable data set label.
                    $absolute = str_replace('\\', '/', $file->getPathname());
                    $files[str_replace("{$root}/", '', $absolute)] = [$absolute];
                }
            }
        }

        return $files;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('copyFiles')]
    public function test_a_view_or_translation_carries_no_typographic_dash(string $path): void
    {
        foreach ($this->codeLines($path) as $number => $line) {
            foreach (self::DASHES as $dash) {
                $this->assertStringNotContainsString(
                    $dash,
                    $line,
                    "Line {$number} uses a typographic dash. Use a comma, colon or full stop, or spell out \"to\" for a range."
                );
            }
        }
    }

    public function test_user_facing_controller_and_command_output_carries_no_typographic_dash(): void
    {
        foreach (['app/Http/Controllers', 'app/Console/Commands', 'app/Models', 'app/Services'] as $dir) {
            $walker = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(base_path($dir)));

            foreach ($walker as $file) {
                if (!$file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $relative = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());

                foreach ($this->codeLines($file->getPathname()) as $number => $line) {
                    foreach (self::DASHES as $dash) {
                        $this->assertStringNotContainsString($dash, $line, "{$relative} line {$number} uses a typographic dash in code.");
                    }
                }
            }
        }
    }

    /**
     * The file's lines with comments stripped, keyed by 1-based line number.
     *
     * Blade comments, docblocks and both slash forms are dropped so that only
     * text a citizen or officer could actually read is checked.
     *
     * @return array<int, string>
     */
    private function codeLines(string $path): array
    {
        $lines = [];
        $inBlock = false;

        foreach (file($path, FILE_IGNORE_NEW_LINES) as $index => $line) {
            $line = preg_replace('/\{\{--.*?--\}\}/s', '', $line);

            if ($inBlock) {
                if (!str_contains($line, '*/')) {
                    continue;
                }

                $line = substr($line, strpos($line, '*/') + 2);
                $inBlock = false;
            }

            $line = preg_replace('#/\*.*?\*/#s', '', $line);

            if (str_contains($line, '/*')) {
                $line = substr($line, 0, strpos($line, '/*'));
                $inBlock = true;
            }

            // A // inside a string literal is not a comment, so only strip the
            // slashes when nothing before them opens a quote.
            $line = preg_replace('#^([^\'"]*?)//.*$#', '$1', $line);

            $lines[$index + 1] = $line;
        }

        return $lines;
    }
}
