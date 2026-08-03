<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemEmailSimulationService;
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
        'active_payment_gateway' => 'payment',
        'phonepe_enabled' => 'payment',
        'phonepe_merchant_id' => 'payment',
        'phonepe_salt_key' => 'payment',
        'phonepe_salt_index' => 'payment',
        'phonepe_env' => 'payment',
        'razorpay_enabled' => 'payment',
        'razorpay_key_id' => 'payment',
        'razorpay_key_secret' => 'payment',
        'razorpay_env' => 'payment',
        'payu_enabled' => 'payment',
        'payu_merchant_key' => 'payment',
        'payu_merchant_salt' => 'payment',
        'payu_merchant_id' => 'payment',
        'payu_env' => 'payment',
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
        'email_verification_enabled' => 'notifications',

        // Email content template settings
        'email_template_invoice_mode' => 'notifications',
        'email_template_invoice_subject' => 'notifications',
        'email_template_invoice_body' => 'notifications',
        'email_template_due_reminder_mode' => 'notifications',
        'email_template_due_reminder_subject' => 'notifications',
        'email_template_due_reminder_body' => 'notifications',
        'email_template_payment_success_mode' => 'notifications',
        'email_template_payment_success_subject' => 'notifications',
        'email_template_payment_success_body' => 'notifications',
        'email_template_payment_failure_mode' => 'notifications',
        'email_template_payment_failure_subject' => 'notifications',
        'email_template_payment_failure_body' => 'notifications',
        'email_template_completion_mode' => 'notifications',
        'email_template_completion_subject' => 'notifications',
        'email_template_completion_body' => 'notifications',
    ];

    public function index()
    {
        if (SiteSetting::get('email_verification_enabled', null) === null) {
            SiteSetting::set('email_verification_enabled', '1', 'notifications', 'boolean');
        }

        $settings = SiteSetting::all()->groupBy('group');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $existingSmtpPassword = SiteSetting::get('smtp_password', '');
        $existingRazorpaySecret = SiteSetting::get('razorpay_key_secret', '');
        $existingPayuSalt = SiteSetting::get('payu_merchant_salt', '');

        $validationRules = [
            'convenience_fee_percentage' => 'nullable|numeric|min:0|max:20',
            'due_reminder_days_before' => 'nullable|integer|min:0|max:30',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_timeout' => 'nullable|integer|min:5|max:300',
            'smtp_encryption' => 'nullable|in:tls,ssl,none',
            'smtp_from_address' => 'nullable|email|max:255',
            'smtp_from_name' => 'nullable|string|max:255',
            'email_template_invoice_mode' => 'nullable|in:current,custom',
            'email_template_invoice_subject' => 'nullable|string|max:255',
            'email_template_due_reminder_mode' => 'nullable|in:current,custom',
            'email_template_due_reminder_subject' => 'nullable|string|max:255',
            'email_template_payment_success_mode' => 'nullable|in:current,custom',
            'email_template_payment_success_subject' => 'nullable|string|max:255',
            'email_template_payment_failure_mode' => 'nullable|in:current,custom',
            'email_template_payment_failure_subject' => 'nullable|string|max:255',
            'email_template_completion_mode' => 'nullable|in:current,custom',
            'email_template_completion_subject' => 'nullable|string|max:255',
            'email_template_invoice_body' => 'nullable|string|max:50000',
            'email_template_due_reminder_body' => 'nullable|string|max:50000',
            'email_template_payment_success_body' => 'nullable|string|max:50000',
            'email_template_payment_failure_body' => 'nullable|string|max:50000',
            'email_template_completion_body' => 'nullable|string|max:50000',
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

        $modeKeys = [
            'email_template_invoice_mode',
            'email_template_due_reminder_mode',
            'email_template_payment_success_mode',
            'email_template_payment_failure_mode',
            'email_template_completion_mode',
        ];

        $subjectKeys = [
            'email_template_invoice_subject',
            'email_template_due_reminder_subject',
            'email_template_payment_success_subject',
            'email_template_payment_failure_subject',
            'email_template_completion_subject',
        ];

        $bodyKeys = [
            'email_template_invoice_body',
            'email_template_due_reminder_body',
            'email_template_payment_success_body',
            'email_template_payment_failure_body',
            'email_template_completion_body',
        ];

        foreach ($subjectKeys as $key) {
            if (array_key_exists($key, $settingsData)) {
                $settingsData[$key] = trim(strip_tags((string) $settingsData[$key]));
            }
        }

        foreach ($modeKeys as $key) {
            if (!array_key_exists($key, $settingsData)) {
                $settingsData[$key] = 'custom';
                continue;
            }

            $value = strtolower(trim((string) $settingsData[$key]));
            $settingsData[$key] = in_array($value, ['current', 'custom'], true) ? $value : 'custom';
        }

        foreach ($bodyKeys as $key) {
            if (array_key_exists($key, $settingsData)) {
                $settingsData[$key] = $this->sanitizeEmailTemplateHtml((string) $settingsData[$key]);
            }
        }

        $booleanKeys = [
            'partial_payment_enabled',
            'partial_payment_allow_50',
            'partial_payment_allow_75',
            'due_reminder_enabled',
            'smtp_enabled',
            'email_verification_enabled',
            'phonepe_enabled',
            'razorpay_enabled',
            'payu_enabled',
        ];

        foreach ($booleanKeys as $key) {
            $settingsData[$key] = $request->has($key) ? '1' : '0';
        }

        foreach ($settingsData as $key => $value) {
            // Skip readonly callback URL fields
            if (in_array($key, ['phonepe_callback_url', 'razorpay_callback_url', 'payu_callback_url'], true)) {
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

            // Keep existing Razorpay key secret when the field is intentionally left blank.
            if ($key === 'razorpay_key_secret' && trim((string) $value) === '') {
                if (!empty($existingRazorpaySecret)) {
                    $value = $existingRazorpaySecret;
                } else {
                    continue;
                }
            }

            // Keep existing PayU merchant salt when the field is intentionally left blank.
            if ($key === 'payu_merchant_salt' && trim((string) $value) === '') {
                if (!empty($existingPayuSalt)) {
                    $value = $existingPayuSalt;
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
            
            $type = in_array($key, $booleanKeys, true) ? 'boolean' : 'text';
            SiteSetting::set($key, $value, $group, $type);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully.');
    }

    public function simulateSystemEmails(Request $request, SystemEmailSimulationService $simulationService)
    {
        $adminUser = auth('admin')->user();
        if (!$adminUser || !$adminUser->isSuperAdmin()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'simulation_email' => 'required|email|max:255',
            'simulation_name' => 'nullable|string|max:100',
        ]);

        try {
            $recipientName = trim((string) ($validated['simulation_name'] ?? ''));
            if ($recipientName === '') {
                $recipientName = 'Soham Tare';
            }

            $result = $simulationService->simulate(
                $validated['simulation_email'],
                $recipientName,
                !$request->boolean('preview_only')
            );

            $summary = $result['summary'];
            $message = $request->boolean('preview_only')
                ? 'System email previews generated successfully.'
                : 'System email simulation completed. Sent: ' . $summary['sent'] . ', Failed: ' . $summary['failed'] . '.';

            return redirect()->route('admin.settings.index')
                ->with('success', $message)
                ->with('email_simulation_result', $result);
        } catch (\Throwable $e) {
            return redirect()->route('admin.settings.index')
                ->with('error', 'System email simulation failed: ' . $e->getMessage());
        }
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

    private function sanitizeEmailTemplateHtml(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html) ?? $html;
        $html = preg_replace('/<(iframe|object|embed|form|input|button|textarea|select)[^>]*>.*?<\/\1>/is', '', $html) ?? $html;
        $html = preg_replace('/<(iframe|object|embed|form|input|button|textarea|select)[^>]*\/?>/is', '', $html) ?? $html;

        $allowedTags = '<p><br><strong><b><em><i><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><blockquote><hr><div><span><table><thead><tbody><tr><th><td>';
        $html = strip_tags($html, $allowedTags);

        $html = preg_replace('/\son[a-z]+\s*=\s*"[^"]*"/i', '', $html) ?? $html;
        $html = preg_replace('/\son[a-z]+\s*=\s*\'[^\']*\'/i', '', $html) ?? $html;
        $html = preg_replace('/javascript\s*:/i', '', $html) ?? $html;

        return $html;
    }
}
