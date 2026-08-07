<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FirebaseConfigTest extends TestCase
{
    use RefreshDatabase;

    private function configure(): void
    {
        SiteSetting::set('firebase_api_key', 'AIzaTestKey', 'firebase');
        SiteSetting::set('firebase_auth_domain', 'neral-gov.firebaseapp.com', 'firebase');
        SiteSetting::set('firebase_project_id', 'neral-gov', 'firebase');
        SiteSetting::set('firebase_storage_bucket', 'neral-gov.firebasestorage.app', 'firebase');
        SiteSetting::set('firebase_messaging_sender_id', '735228300916', 'firebase');
        SiteSetting::set('firebase_app_id', '1:735228300916:web:abc', 'firebase');
        SiteSetting::set('firebase_measurement_id', 'G-TESTID', 'firebase');
        SiteSetting::set('firebase_enabled', '1', 'firebase', 'boolean');
    }

    public function test_the_login_page_renders_the_configured_project(): void
    {
        $this->configure();

        $this->get('/citizen/login')
            ->assertOk()
            ->assertSee('neral-gov', false)
            ->assertSee('G-TESTID', false);
    }

    /**
     * The view used to fall back to a hardcoded, unrelated Firebase project.
     * Clearing the settings would then have authenticated citizens against
     * someone else's project instead of failing visibly.
     */
    public function test_no_foreign_project_credentials_are_hardcoded_in_the_view(): void
    {
        $view = file_get_contents(resource_path('views/citizen/auth/login.blade.php'));

        foreach (['tanay-gram-panchayat', 'AIzaSyDIeHdDdsADHz6C16y7bFrhzLWX9nmfUhs', 'G-62RN0ZLRM8'] as $stale) {
            $this->assertStringNotContainsString($stale, $view);
        }
    }

    public function test_an_unconfigured_site_renders_empty_values_rather_than_a_default_project(): void
    {
        // Nothing configured at all.
        $response = $this->get('/citizen/login')->assertOk();

        $response->assertDontSee('tanay-gram-panchayat', false);
        $response->assertSee('apiKey: ""', false);
    }

    public function test_measurement_id_is_stored_in_the_firebase_group(): void
    {
        $this->configure();

        $row = SiteSetting::where('key', 'firebase_measurement_id')->first();

        $this->assertNotNull($row);
        $this->assertSame('firebase', $row->group);
    }
}
