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
    ];

    public function index()
    {
        $settings = SiteSetting::all()->groupBy('group');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settingsData = $request->except('_token', '_method');

        foreach ($settingsData as $key => $value) {
            // Skip callback URL field (readonly)
            if ($key === 'phonepe_callback_url') {
                continue;
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

