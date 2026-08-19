<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\QuickLink;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\Slider;
use App\Models\TaxType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $superAdminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'superadmin',
            'type' => 'superadmin',
            'description' => 'Full system access with all permissions',
            'permissions' => array_keys(Role::getAvailablePermissions()),
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'type' => 'admin',
            'description' => 'Administrative access',
            'permissions' => [
                'dashboard.view',
                'sliders.view', 'sliders.create', 'sliders.edit', 'sliders.delete',
                'quick_links.view', 'quick_links.create', 'quick_links.edit', 'quick_links.delete',
                'settings.view', 'settings.edit',
                // Payment gateway credentials decide where citizens' money lands,
                // so they are gated separately from ordinary settings.
                'settings.payment',
                // Deleting tax liability is separated from managing it.
                'water_tax.delete', 'property_tax.delete',
                'tax_types.view', 'tax_types.edit',
                'payments.view', 'payments.export',
                'analytics.view',
                'roles.view',
                'admins.view',
            ],
        ]);

        $employeeRole = Role::create([
            'name' => 'Employee',
            'slug' => 'employee',
            'type' => 'employee',
            'description' => 'Basic access for employees',
            'permissions' => [
                'dashboard.view',
                'payments.view',
            ],
        ]);

        // Create Super Admin User
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@grampanchayat.in',
            'password' => Hash::make('superadmin@123'),
            'role_id' => $superAdminRole->id,
            'is_active' => true,
        ]);

        // Create Admin User
        Admin::create([
            'name' => 'Admin',
            'email' => 'admin@grampanchayat.in',
            'password' => Hash::make('admin@123'),
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        // Site Settings
        $settings = [
            // General Settings
            ['key' => 'site_name', 'value' => 'Gram Panchayat', 'group' => 'general', 'type' => 'text'],
            ['key' => 'site_tagline', 'value' => 'Serving Our Community', 'group' => 'general', 'type' => 'text'],
            ['key' => 'site_description', 'value' => 'Official Gram Panchayat Portal - Pay your taxes online, access government services, and stay connected with your local governance.', 'group' => 'general', 'type' => 'textarea'],
            ['key' => 'contact_email', 'value' => 'contact@grampanchayat.in', 'group' => 'general', 'type' => 'text'],
            ['key' => 'contact_phone', 'value' => '+91 9876543210', 'group' => 'general', 'type' => 'text'],
            ['key' => 'address', 'value' => 'Gram Panchayat Office, Village Center, District, State - 123456', 'group' => 'general', 'type' => 'textarea'],
            
            // Social Links
            ['key' => 'facebook_url', 'value' => '', 'group' => 'social', 'type' => 'text'],
            ['key' => 'twitter_url', 'value' => '', 'group' => 'social', 'type' => 'text'],
            ['key' => 'instagram_url', 'value' => '', 'group' => 'social', 'type' => 'text'],
            ['key' => 'youtube_url', 'value' => '', 'group' => 'social', 'type' => 'text'],
            
            // PhonePe Settings
            ['key' => 'phonepe_merchant_id', 'value' => '', 'group' => 'payment', 'type' => 'text'],
            ['key' => 'phonepe_salt_key', 'value' => '', 'group' => 'payment', 'type' => 'text'],
            ['key' => 'phonepe_salt_index', 'value' => '1', 'group' => 'payment', 'type' => 'text'],
            ['key' => 'phonepe_env', 'value' => 'sandbox', 'group' => 'payment', 'type' => 'text'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::create($setting);
        }

        // Tax Types
        // The slug must stay "property-tax": PaymentController resolves the tax
        // type by this value, and the old "house-tax" slug never matched, so
        // property payments failed at that lookup.
        TaxType::create([
            'name' => 'Property Tax',
            'slug' => 'property-tax',
            'description' => 'Annual property tax for residential and commercial properties',
            'monthly_rate' => 100.00,
            'quarterly_rate' => 280.00,
            'yearly_rate' => 1000.00,
            'icon' => 'fa-home',
            'is_active' => true,
        ]);

        TaxType::create([
            'name' => 'Water Tax',
            'slug' => 'water-tax',
            'description' => 'Water supply and maintenance charges',
            'monthly_rate' => 50.00,
            'quarterly_rate' => 140.00,
            'yearly_rate' => 500.00,
            'icon' => 'fa-tint',
            'is_active' => true,
        ]);

        // Quick Links for Footer
        $quickLinks = [
            ['title' => 'Privacy Policy', 'url' => '/privacy-policy', 'location' => 'footer', 'order' => 1],
            ['title' => 'Terms & Conditions', 'url' => '/terms-conditions', 'location' => 'footer', 'order' => 2],
            ['title' => 'Refund Policy', 'url' => '/refund-policy', 'location' => 'footer', 'order' => 3],
            ['title' => 'Contact Us', 'url' => '/contact', 'location' => 'footer', 'order' => 4],
            ['title' => 'About Us', 'url' => '/about', 'location' => 'footer', 'order' => 5],
        ];

        foreach ($quickLinks as $link) {
            QuickLink::create($link);
        }

        // Sample Sliders
        Slider::create([
            'title' => 'Welcome to Gram Panchayat',
            'subtitle' => 'Pay your taxes online with ease',
            'image' => 'sliders/welcome.jpg',
            'button_text' => 'Pay Now',
            'link' => '/pay-tax',
            'order' => 1,
            'is_active' => true,
        ]);

        Slider::create([
            'title' => 'Digital Services',
            'subtitle' => 'Access government services from anywhere',
            'image' => 'sliders/services.jpg',
            'button_text' => 'View Services',
            'link' => '/digital-services',
            'order' => 2,
            'is_active' => true,
        ]);

        // Keep transactional data empty by default (production-safe).
        // Set SEED_SAMPLE_TAX_DATA=true only when you explicitly need demo data.
        if (filter_var(env('SEED_SAMPLE_TAX_DATA', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->call([
                WaterTaxSeeder::class,
                PropertyTaxSeeder::class,
            ]);
        }
    }
}
