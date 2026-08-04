<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaTest extends TestCase
{
    public function test_manifest_is_valid_and_declares_the_icons_play_requires(): void
    {
        $path = public_path('manifest.json');
        $this->assertFileExists($path);

        $manifest = json_decode(file_get_contents($path), true);
        $this->assertSame(JSON_ERROR_NONE, json_last_error(), 'manifest.json must be valid JSON.');

        foreach (['name', 'short_name', 'start_url', 'scope', 'display', 'icons'] as $key) {
            $this->assertArrayHasKey($key, $manifest, "Manifest is missing required key: {$key}");
        }

        $this->assertSame('standalone', $manifest['display'], 'TWA requires a standalone display mode.');

        $sizes = array_column($manifest['icons'], 'sizes');
        $this->assertContains('192x192', $sizes);
        $this->assertContains('512x512', $sizes, 'Play requires a 512px icon.');

        $purposes = array_column($manifest['icons'], 'purpose');
        $this->assertContains('maskable', $purposes, 'Android crops icons without a maskable variant.');
    }

    public function test_every_icon_referenced_by_the_manifest_exists(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.json')), true);

        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(
                public_path(ltrim($icon['src'], '/')),
                "Manifest references a missing icon: {$icon['src']}"
            );
        }
    }

    public function test_service_worker_never_intercepts_non_get_requests(): void
    {
        $sw = file_get_contents(public_path('sw.js'));

        $this->assertStringContainsString(
            "request.method !== 'GET'",
            $sw,
            'Payments and logins must bypass the service worker entirely.'
        );
    }

    public function test_service_worker_excludes_authenticated_and_payment_paths_from_cache(): void
    {
        $sw = file_get_contents(public_path('sw.js'));

        foreach (['/admin', '/citizen', '/payment'] as $path) {
            $this->assertStringContainsString(
                "'{$path}'",
                $sw,
                "Caching {$path} risks serving one citizen's page to another."
            );
        }
    }

    public function test_offline_fallback_page_exists(): void
    {
        $this->assertFileExists(public_path('offline.html'));
    }

    public function test_layout_links_the_manifest_and_registers_the_worker(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertStringContainsString('rel="manifest"', $layout);
        $this->assertStringContainsString('serviceWorker', $layout);
        $this->assertStringContainsString('theme-color', $layout);
    }

    public function test_service_worker_is_served_from_the_document_root(): void
    {
        // Scope matters: a worker served from a subdirectory cannot control "/",
        // so the file has to sit in public/ rather than under a route.
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('manifest.json'));
    }
}
