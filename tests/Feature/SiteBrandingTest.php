<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_site_name_is_used_when_set(): void
    {
        SiteSetting::set('site_name', 'Neral Gram Panchayat', 'general');

        $this->get('/')
            ->assertOk()
            ->assertSee('Neral Gram Panchayat');
    }

    public function test_a_generic_fallback_is_shown_when_no_name_is_set(): void
    {
        // Better a generic label than an empty header.
        $this->get('/')->assertOk()->assertSee('Gram Panchayat');
    }

    public function test_the_uploaded_logo_is_rendered_when_present(): void
    {
        SiteSetting::set('site_logo', 'settings/emblem.png', 'general');
        SiteSetting::set('site_name', 'Neral Gram Panchayat', 'general');

        $this->get('/')
            ->assertOk()
            ->assertSee('storage/settings/emblem.png', false)
            ->assertSee('logo-mark', false);
    }

    public function test_the_building_icon_is_used_when_no_logo_is_uploaded(): void
    {
        $response = $this->get('/')->assertOk();

        $response->assertSee('fa-landmark', false);
        $response->assertDontSee('logo-mark', false);
    }

    public function test_the_tagline_appears_only_when_set(): void
    {
        $this->get('/')->assertDontSee('logo-tagline', false);

        SiteSetting::set('site_tagline', 'Serving Neral since 1962', 'general');

        $this->get('/')
            ->assertSee('logo-tagline', false)
            ->assertSee('Serving Neral since 1962');
    }

    public function test_site_logo_is_stored_in_the_general_group(): void
    {
        SiteSetting::set('site_logo', 'settings/emblem.png', 'general');

        $this->assertSame(
            'general',
            SiteSetting::where('key', 'site_logo')->first()->group
        );
    }
}
