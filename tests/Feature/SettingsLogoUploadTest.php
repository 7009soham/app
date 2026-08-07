<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Role;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsLogoUploadTest extends TestCase
{
    use RefreshDatabase;

    private ?Admin $admin = null;

    /** Memoised: submit() is called more than once per test. */
    private function admin(): Admin
    {
        if ($this->admin) {
            return $this->admin;
        }

        $role = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'type' => 'superadmin',
            'is_active' => true,
        ]);

        return $this->admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    private function submit(array $payload): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->admin(), 'admin')
            ->put(route('admin.settings.general.update'), array_merge([
                'site_name' => 'Neral Gram Panchayat',
            ], $payload));
    }

    public function test_an_uploaded_logo_is_stored_and_recorded(): void
    {
        Storage::fake('public');

        $this->submit([
            'site_logo' => UploadedFile::fake()->image('emblem.png', 400, 200),
        ])->assertRedirect();

        $stored = SiteSetting::get('site_logo', '');

        $this->assertNotSame('', $stored, 'The upload must be recorded in site_settings.');
        Storage::disk('public')->assertExists($stored);
    }

    public function test_the_stored_logo_then_renders_on_the_public_site(): void
    {
        Storage::fake('public');

        $this->submit([
            'site_logo' => UploadedFile::fake()->image('emblem.png', 400, 200),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('logo-mark', false)
            ->assertSee(SiteSetting::get('site_logo'), false);
    }

    public function test_saving_without_a_file_keeps_the_existing_logo(): void
    {
        Storage::fake('public');

        $this->submit(['site_logo' => UploadedFile::fake()->image('first.png', 400, 200)]);
        $first = SiteSetting::get('site_logo');

        // Editing the name must not wipe the logo.
        $this->submit(['site_name' => 'Renamed Panchayat']);

        $this->assertSame($first, SiteSetting::get('site_logo'));
        $this->assertSame('Renamed Panchayat', SiteSetting::get('site_name'));
    }

    public function test_replacing_the_logo_deletes_the_previous_file(): void
    {
        Storage::fake('public');

        $this->submit(['site_logo' => UploadedFile::fake()->image('first.png', 400, 200)]);
        $first = SiteSetting::get('site_logo');

        $this->submit(['site_logo' => UploadedFile::fake()->image('second.png', 400, 200)]);
        $second = SiteSetting::get('site_logo');

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertExists($second);
        // Old emblems should not accumulate on disk.
        Storage::disk('public')->assertMissing($first);
    }

    public function test_an_svg_is_rejected(): void
    {
        Storage::fake('public');

        // SVG can carry script and would be served from our own origin.
        $this->submit([
            'site_logo' => UploadedFile::fake()->create('emblem.svg', 10, 'image/svg+xml'),
        ])->assertSessionHasErrors('site_logo');

        $this->assertSame('', SiteSetting::get('site_logo', ''));
    }

    public function test_an_oversized_image_is_rejected(): void
    {
        Storage::fake('public');

        $this->submit([
            'site_logo' => UploadedFile::fake()->image('huge.png', 3000, 1000),
        ])->assertSessionHasErrors('site_logo');
    }

    public function test_every_branding_field_is_editable_together(): void
    {
        Storage::fake('public');

        $this->submit([
            'site_name' => 'Neral Gram Panchayat',
            'site_tagline' => 'Serving Neral',
            'site_description' => 'Official portal.',
            'address' => 'Neral, Raigad, Maharashtra',
            'contact_phone' => '02148 000000',
            'contact_email' => 'office@neralgov.com',
        ])->assertRedirect();

        $this->assertSame('Neral Gram Panchayat', SiteSetting::get('site_name'));
        $this->assertSame('Serving Neral', SiteSetting::get('site_tagline'));
        $this->assertSame('Neral, Raigad, Maharashtra', SiteSetting::get('address'));
        $this->assertSame('02148 000000', SiteSetting::get('contact_phone'));
        $this->assertSame('office@neralgov.com', SiteSetting::get('contact_email'));
    }

    public function test_the_contact_email_reaches_the_privacy_policy_grievance_officer_block(): void
    {
        $this->submit(['contact_email' => 'office@neralgov.com']);

        // This address is the DPDP grievance contact, so it must actually render.
        $this->get('/privacy-policy')
            ->assertOk()
            ->assertSee('Grievance Officer')
            ->assertSee('office@neralgov.com');
    }

    public function test_an_invalid_contact_email_is_rejected(): void
    {
        $this->submit(['contact_email' => 'not-an-email'])
            ->assertSessionHasErrors('contact_email');
    }
}
