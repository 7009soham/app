<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Define setting groups for proper categorization
     */
    private $settingGroups = [
        // General Settings
        'site_name' => 'general',
        'site_tagline' => 'general',
        'site_description' => 'general',
        'contact_email' => 'general',
        'contact_phone' => 'general',
        'address' => 'general',
        
        // Social Settings
        'facebook_url' => 'social',
        'twitter_url' => 'social',
        'instagram_url' => 'social',
        'youtube_url' => 'social',
        
        // Firebase Settings
        'firebase_api_key' => 'firebase',
        'firebase_auth_domain' => 'firebase',
        'firebase_project_id' => 'firebase',
        'firebase_storage_bucket' => 'firebase',
        'firebase_messaging_sender_id' => 'firebase',
        'firebase_app_id' => 'firebase',
        'firebase_enabled' => 'firebase',
        
        // Payment Settings
        'phonepe_enabled' => 'payment',
        'phonepe_merchant_id' => 'payment',
        'phonepe_salt_key' => 'payment',
        'phonepe_salt_index' => 'payment',
        'phonepe_env' => 'payment',
        'partial_payment_enabled' => 'payment',
        'partial_payment_allow_50' => 'payment',
        'partial_payment_allow_75' => 'payment',
        'convenience_fee_percentage' => 'payment',
        'due_reminder_enabled' => 'notifications',
        'due_reminder_days_before' => 'notifications',

        // Notifications Settings
        'smtp_enabled' => 'notifications',
        'smtp_host' => 'notifications',
        'smtp_port' => 'notifications',
        'smtp_username' => 'notifications',
        'smtp_password' => 'notifications',
        'smtp_encryption' => 'notifications',
        'smtp_from_address' => 'notifications',
        'smtp_from_name' => 'notifications',
        'smtp_timeout' => 'notifications',
    ];

    public function index()
    {
        $settings = SiteSetting::all()->groupBy('group');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $existingSmtpPassword = SiteSetting::get('smtp_password', '');

        $validationRules = [
            'convenience_fee_percentage' => 'nullable|numeric|min:0|max:20',
            'due_reminder_days_before' => 'nullable|integer|min:0|max:30',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_timeout' => 'nullable|integer|min:5|max:300',
            'smtp_encryption' => 'nullable|in:tls,ssl,none',
            'smtp_from_address' => 'nullable|email|max:255',
            'smtp_from_name' => 'nullable|string|max:255',
        ];

        if ($request->has('smtp_enabled')) {
            $validationRules = array_merge($validationRules, [
                'smtp_host' => 'required|string|max:255',
                'smtp_username' => 'required|string|max:255',
                'smtp_password' => empty($existingSmtpPassword) ? 'required|string|max:255' : 'nullable|string|max:255',
                'smtp_from_address' => 'required|email|max:255',
            ]);
        }

        $request->validate($validationRules);

        $settingsData = $request->except('_token', '_method');

        $booleanKeys = [
            'partial_payment_enabled',
            'partial_payment_allow_50',
            'partial_payment_allow_75',
            'due_reminder_enabled',
            'smtp_enabled',
        ];

        foreach ($booleanKeys as $key) {
            $settingsData[$key] = $request->has($key) ? '1' : '0';
        }

        foreach ($settingsData as $key => $value) {
            // Skip callback URL field (readonly)
            if ($key === 'phonepe_callback_url') {
                continue;
            }

            // Keep existing SMTP password when the field is intentionally left blank.
            if ($key === 'smtp_password' && trim((string) $value) === '') {
                if (!empty($existingSmtpPassword)) {
                    $value = $existingSmtpPassword;
                } else {
                    continue;
                }
            }

            // Determine the group for this setting
            $group = $this->settingGroups[$key] ?? 'general';
            
            // Handle file uploads
            if ($request->hasFile($key)) {
                $existingSetting = SiteSetting::where('key', $key)->first();
                if ($existingSetting && $existingSetting->value && Storage::disk('public')->exists($existingSetting->value)) {
                    Storage::disk('public')->delete($existingSetting->value);
                }
                $value = $request->file($key)->store('settings', 'public');
            }
            
            SiteSetting::set($key, $value, $group, 'text');
        }

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully.');
    }

    public function general()
    {
        $settings = SiteSetting::where('group', 'general')->pluck('value', 'key');
        return view('admin.settings.general', compact('settings'));
    }

    public function social()
    {
        $settings = SiteSetting::where('group', 'social')->pluck('value', 'key');
        return view('admin.settings.social', compact('settings'));
    }

    public function payment()
    {
        $settings = SiteSetting::where('group', 'payment')->pluck('value', 'key');
        return view('admin.settings.payment', compact('settings'));
    }

    public function firebase()
    {
        $settings = SiteSetting::where('group', 'firebase')->pluck('value', 'key');
        return view('admin.settings.firebase', compact('settings'));
    }
}
