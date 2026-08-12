<?php

namespace Tests\Feature;

use App\Helpers\Asset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * nginx serves public/css with a ten-year max-age and these filenames never
 * change, so without a version stamp a redesign is invisible to every citizen
 * who has visited the site before.
 */
class AssetVersioningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_an_existing_asset_gets_a_version_stamp(): void
    {
        $url = Asset::versioned('css/app.css');

        $this->assertStringContainsString('css/app.css?v=', $url);
        $this->assertMatchesRegularExpression('/\?v=\d+$/', $url);
    }

    public function test_the_stamp_tracks_the_files_modification_time(): void
    {
        $expected = filemtime(public_path('css/app.css'));

        $this->assertStringEndsWith('?v=' . $expected, Asset::versioned('css/app.css'));
    }

    public function test_a_missing_asset_falls_back_to_a_plain_url(): void
    {
        // Never emit "?v=" with nothing after it.
        $url = Asset::versioned('css/does-not-exist.css');

        $this->assertStringNotContainsString('?v=', $url);
        $this->assertStringEndsWith('css/does-not-exist.css', $url);
    }

    public function test_the_public_layout_requests_a_versioned_stylesheet(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('css/app.css?v=', false);
    }

    /**
     * The service worker script is served with the same long max-age, so the
     * browser must be told not to use its HTTP cache for it - otherwise the
     * cache-version bump that clears stale assets never runs.
     */
    public function test_the_service_worker_registration_bypasses_the_http_cache(): void
    {
        $this->get('/')->assertSee("updateViaCache: 'none'", false);
    }
}
