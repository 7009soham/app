<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Role;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Each settings section is its own page with its own form. Saving one section
 * must never write, blank, or otherwise disturb another section's keys - the
 * bug that made SMTP credentials appear to leak across every settings tab.
 */
class SettingsSectionIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): Admin
    {
        $role = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'type' => 'superadmin',
        ]);

        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin');

        return $admin;
    }

    /**
     * Seed one recognisable value in every group so we can prove the others
     * survive a save.
     */
    private function seedAllSections(): void
    {
        SiteSetting::set('site_name', 'Neral Gram Panchayat', 'general');
        SiteSetting::set('facebook_url', 'https://facebook.com/neral', 'social');
        SiteSetting::set('firebase_api_key', 'AIzaSyOriginal', 'firebase');
        SiteSetting::set('phonepe_merchant_id', 'MERCHANTLIVE', 'payment');
        SiteSetting::set('phonepe_salt_key', 'original-salt-key', 'payment');
        SiteSetting::set('smtp_host', 'smtp.gmail.com', 'notifications');
        SiteSetting::set('smtp_username', 'mailer@neralgov.com', 'notifications');
        SiteSetting::set('smtp_password', 'original-smtp-password', 'notifications');
    }

    public function test_saving_general_settings_leaves_smtp_and_payment_untouched(): void
    {
        $this->actingAsAdmin();
        $this->seedAllSections();

        $this->put(route('admin.settings.general.update'), [
            'site_name' => 'Updated Panchayat Name',
            'contact_email' => 'office@neralgov.com',
        ])->assertRedirect();

        $this->assertSame('Updated Panchayat Name', SiteSetting::get('site_name'));
        $this->assertSame('office@neralgov.com', SiteSetting::get('contact_email'));

        // Nothing outside the general group may change.
        $this->assertSame('smtp.gmail.com', SiteSetting::get('smtp_host'));
        $this->assertSame('original-smtp-password', SiteSetting::get('smtp_password'));
        $this->assertSame('MERCHANTLIVE', SiteSetting::get('phonepe_merchant_id'));
        $this->assertSame('original-salt-key', SiteSetting::get('phonepe_salt_key'));
        $this->assertSame('AIzaSyOriginal', SiteSetting::get('firebase_api_key'));
        $this->assertSame('https://facebook.com/neral', SiteSetting::get('facebook_url'));
    }

    public function test_saving_payment_settings_does_not_disable_smtp(): void
    {
        $this->actingAsAdmin();
        $this->seedAllSections();
        SiteSetting::set('smtp_enabled', '1', 'notifications', 'boolean');

        // The payment form carries no smtp_enabled checkbox. Before the split,
        // the shared form treated its absence as "unchecked" and switched SMTP off.
        $this->put(route('admin.settings.payment.update'), [
            'active_payment_gateway' => 'phonepe',
            'phonepe_merchant_id' => 'MERCHANTUAT',
        ])->assertRedirect();

        $this->assertSame('MERCHANTUAT', SiteSetting::get('phonepe_merchant_id'));
        $this->assertSame('1', SiteSetting::get('smtp_enabled'), 'Saving payment settings must not switch SMTP off.');
        $this->assertSame('original-smtp-password', SiteSetting::get('smtp_password'));
    }

    public function test_saving_notifications_does_not_clear_payment_credentials(): void
    {
        $this->actingAsAdmin();
        $this->seedAllSections();
        SiteSetting::set('phonepe_enabled', '1', 'payment', 'boolean');

        $this->put(route('admin.settings.notifications.update'), [
            'smtp_from_name' => 'Neral Gram Panchayat',
        ])->assertRedirect();

        $this->assertSame('Neral Gram Panchayat', SiteSetting::get('smtp_from_name'));
        $this->assertSame('1', SiteSetting::get('phonepe_enabled'), 'Saving notifications must not disable PhonePe.');
        $this->assertSame('original-salt-key', SiteSetting::get('phonepe_salt_key'));
    }

    public function test_foreign_keys_posted_to_a_section_are_ignored(): void
    {
        $this->actingAsAdmin();
        $this->seedAllSections();

        // A crafted request posting another section's keys must be rejected.
        $this->put(route('admin.settings.social.update'), [
            'facebook_url' => 'https://facebook.com/updated',
            'smtp_password' => 'attacker-supplied',
            'phonepe_salt_key' => 'attacker-supplied',
        ])->assertRedirect();

        $this->assertSame('https://facebook.com/updated', SiteSetting::get('facebook_url'));
        $this->assertSame('original-smtp-password', SiteSetting::get('smtp_password'));
        $this->assertSame('original-salt-key', SiteSetting::get('phonepe_salt_key'));
    }

    public function test_blank_secret_fields_keep_their_stored_value(): void
    {
        $this->actingAsAdmin();
        $this->seedAllSections();

        $this->put(route('admin.settings.payment.update'), [
            'phonepe_merchant_id' => 'MERCHANTUAT',
            'phonepe_salt_key' => '',
        ])->assertRedirect();

        $this->assertSame('original-salt-key', SiteSetting::get('phonepe_salt_key'), 'A blank salt field must not wipe the stored salt.');
    }

    public function test_every_section_page_renders(): void
    {
        $this->actingAsAdmin();
        $this->seedAllSections();

        foreach (['general', 'social', 'firebase', 'payment', 'notifications'] as $section) {
            $this->get(route('admin.settings.' . $section))
                ->assertOk()
                ->assertSee('settings-layout', false);
        }
    }

    public function test_settings_index_redirects_to_general(): void
    {
        $this->actingAsAdmin();

        $this->get(route('admin.settings.index'))
            ->assertRedirect(route('admin.settings.general'));
    }
}
