<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\RichText;
use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
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
        'site_logo' => 'general',
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
        'firebase_measurement_id' => 'firebase',
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
        'payu_env' => 'payment',
        // Each tax head settles into its own bank account, so each has its own
        // PayU merchant ID, key and salt.
        'payu_property_merchant_key' => 'payment',
        'payu_property_merchant_salt' => 'payment',
        'payu_property_merchant_id' => 'payment',
        'payu_water_merchant_key' => 'payment',
        'payu_water_merchant_salt' => 'payment',
        'payu_water_merchant_id' => 'payment',
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

    /**
     * Keys stored as '1'/'0'. An unchecked checkbox posts nothing, so these are
     * always written explicitly rather than read from the request.
     */
    private $booleanKeys = [
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

    public function index()
    {
        return redirect()->route('admin.settings.general');
    }

    /**
     * Render one settings section. Every section page receives the full grouped
     * collection so shared partials keep working, but its form posts only its
     * own fields.
     */
    private function section(string $view): \Illuminate\View\View
    {
        if (SiteSetting::get('email_verification_enabled', null) === null) {
            SiteSetting::set('email_verification_enabled', '1', 'notifications', 'boolean');
        }

        $settings = SiteSetting::all()->groupBy('group');

        return view('admin.settings.' . $view, compact('settings'));
    }

    /**
     * Persist only the keys that belong to $group. Anything posted that maps to
     * a different group is ignored, so one section can never overwrite another.
     */
    private function updateSection(Request $request, string $group)
    {
        $allowed = array_keys(array_filter(
            $this->settingGroups,
            fn ($mappedGroup) => $mappedGroup === $group
        ));

        $booleanKeys = array_values(array_intersect($this->booleanKeys, $allowed));

        $settingsData = $request->only($allowed);

        foreach ($booleanKeys as $key) {
            $settingsData[$key] = $request->has($key) ? '1' : '0';
        }

        $settingsData = $this->normaliseEmailTemplateFields($settingsData);

        $this->persistSettings($settingsData, $booleanKeys, $request);

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    public function update(Request $request)
    {
        $existingSmtpPassword = SiteSetting::get('smtp_password', '');
        $existingRazorpaySecret = SiteSetting::get('razorpay_key_secret', '');

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
                $settingsData[$key] = RichText::sanitize((string) $settingsData[$key]);
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

        $this->persistSettings($settingsData, $booleanKeys, $request);

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully.');
    }

    /**
     * Strip tags from template subjects and sanitise template bodies, leaving
     * every other key untouched. Modes fall back to 'custom' when unrecognised.
     */
    private function normaliseEmailTemplateFields(array $settingsData): array
    {
        foreach (['invoice', 'due_reminder', 'payment_success', 'payment_failure', 'completion'] as $template) {
            $subjectKey = "email_template_{$template}_subject";
            $bodyKey = "email_template_{$template}_body";
            $modeKey = "email_template_{$template}_mode";

            if (array_key_exists($subjectKey, $settingsData)) {
                $settingsData[$subjectKey] = trim(strip_tags((string) $settingsData[$subjectKey]));
            }

            if (array_key_exists($bodyKey, $settingsData)) {
                $settingsData[$bodyKey] = RichText::sanitize((string) $settingsData[$bodyKey]);
            }

            if (array_key_exists($modeKey, $settingsData)) {
                $mode = strtolower(trim((string) $settingsData[$modeKey]));
                $settingsData[$modeKey] = in_array($mode, ['current', 'custom'], true) ? $mode : 'custom';
            }
        }

        return $settingsData;
    }

    /**
     * Write a prepared key => value map to site_settings.
     *
     * Secrets posted blank keep their stored value, readonly callback URLs are
     * never written, and each key lands in the group declared by $settingGroups.
     */
    private function persistSettings(array $settingsData, array $booleanKeys, Request $request): void
    {
        $preservedSecrets = [
            'smtp_password' => SiteSetting::get('smtp_password', ''),
            'phonepe_salt_key' => SiteSetting::get('phonepe_salt_key', ''),
            'razorpay_key_secret' => SiteSetting::get('razorpay_key_secret', ''),
            'payu_property_merchant_salt' => SiteSetting::get('payu_property_merchant_salt', ''),
            'payu_water_merchant_salt' => SiteSetting::get('payu_water_merchant_salt', ''),
        ];

        foreach ($settingsData as $key => $value) {
            // Readonly callback URLs are rendered for copy/paste only.
            if (in_array($key, ['phonepe_callback_url', 'razorpay_callback_url', 'payu_callback_url'], true)) {
                continue;
            }

            // A blank secret field means "keep what is already stored".
            if (array_key_exists($key, $preservedSecrets) && trim((string) $value) === '') {
                if ($preservedSecrets[$key] === '') {
                    continue;
                }
                $value = $preservedSecrets[$key];
            }

            $group = $this->settingGroups[$key] ?? 'general';

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
        return $this->section('general');
    }

    public function updateGeneral(Request $request)
    {
        $request->validate([
            // SVG is excluded on purpose: it can carry script, and an uploaded
            // logo is served from our own origin.
            'site_logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:1024|dimensions:max_width=1200,max_height=400',
            'site_name' => 'nullable|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:1000',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:32',
            'address' => 'nullable|string|max:500',
        ]);

        return $this->updateSection($request, 'general');
    }

    public function social()
    {
        return $this->section('social');
    }

    public function updateSocial(Request $request)
    {
        $request->validate([
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
        ]);

        return $this->updateSection($request, 'social');
    }

    public function firebase()
    {
        return $this->section('firebase');
    }

    public function updateFirebase(Request $request)
    {
        $request->validate([
            'firebase_api_key' => 'nullable|string|max:255',
            'firebase_auth_domain' => 'nullable|string|max:255',
            'firebase_project_id' => 'nullable|string|max:255',
            'firebase_storage_bucket' => 'nullable|string|max:255',
            'firebase_messaging_sender_id' => 'nullable|string|max:64',
            'firebase_app_id' => 'nullable|string|max:255',
            'firebase_measurement_id' => 'nullable|string|max:64',
            'firebase_enabled' => 'nullable|in:0,1',
        ]);

        return $this->updateSection($request, 'firebase');
    }

    public function payment()
    {
        return $this->section('payment');
    }

    public function updatePayment(Request $request)
    {
        $request->validate([
            'active_payment_gateway' => 'nullable|in:phonepe,razorpay,payu',
            'convenience_fee_percentage' => 'nullable|numeric|min:0|max:20',
            'phonepe_merchant_id' => 'nullable|string|max:255',
            'phonepe_salt_index' => 'nullable|string|max:8',
            'phonepe_env' => 'nullable|in:sandbox,production',
            'razorpay_key_id' => 'nullable|string|max:255',
            'razorpay_env' => 'nullable|in:sandbox,production',
            'payu_property_merchant_key' => 'nullable|string|max:255',
            'payu_property_merchant_id' => 'nullable|string|max:255',
            'payu_water_merchant_key' => 'nullable|string|max:255',
            'payu_water_merchant_id' => 'nullable|string|max:255',
            'payu_env' => 'nullable|in:sandbox,production',
        ]);

        // Credentials that decide where citizens' tax money lands. Every write
        // is recorded with the actor; secret values are hashed by the audit
        // model so a salt rotation is visible without the salt being readable.
        $keys = [
            'active_payment_gateway', 'convenience_fee_percentage',
            'phonepe_merchant_id', 'phonepe_salt_key', 'phonepe_salt_index', 'phonepe_env',
            'razorpay_key_id', 'razorpay_env',
            'payu_property_merchant_id', 'payu_property_merchant_key', 'payu_property_merchant_salt',
            'payu_water_merchant_id', 'payu_water_merchant_key', 'payu_water_merchant_salt',
            'payu_env',
        ];

        $before = [];
        foreach ($keys as $key) {
            $before[$key] = SiteSetting::get($key, null);
        }

        $response = $this->updateSection($request, 'payment');

        $after = [];
        foreach ($keys as $key) {
            $after[$key] = SiteSetting::get($key, null);
        }

        // Only record what actually moved, so the trail stays readable.
        $changed = array_keys(array_filter(
            $after,
            fn ($value, $key) => (string) $value !== (string) ($before[$key] ?? ''),
            ARRAY_FILTER_USE_BOTH
        ));

        if ($changed !== []) {
            AdminAuditLog::record(
                'settings.payment.update',
                null,
                array_intersect_key($before, array_flip($changed)),
                array_intersect_key($after, array_flip($changed)),
                implode(', ', $changed)
            );
        }

        return $response;
    }

    public function notifications()
    {
        return $this->section('notifications');
    }

    public function updateNotifications(Request $request)
    {
        $existingSmtpPassword = SiteSetting::get('smtp_password', '');

        $rules = [
            'due_reminder_days_before' => 'nullable|integer|min:0|max:30',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_timeout' => 'nullable|integer|min:5|max:300',
            'smtp_encryption' => 'nullable|in:tls,ssl,none',
            'smtp_from_address' => 'nullable|email|max:255',
            'smtp_from_name' => 'nullable|string|max:255',
        ];

        foreach (['invoice', 'due_reminder', 'payment_success', 'payment_failure', 'completion'] as $template) {
            $rules["email_template_{$template}_mode"] = 'nullable|in:current,custom';
            $rules["email_template_{$template}_subject"] = 'nullable|string|max:255';
            $rules["email_template_{$template}_body"] = 'nullable|string|max:50000';
        }

        if ($request->has('smtp_enabled')) {
            $rules = array_merge($rules, [
                'smtp_host' => 'required|string|max:255',
                'smtp_username' => 'required|string|max:255',
                'smtp_password' => empty($existingSmtpPassword) ? 'required|string|max:255' : 'nullable|string|max:255',
                'smtp_from_address' => 'required|email|max:255',
            ]);
        }

        $request->validate($rules);

        return $this->updateSection($request, 'notifications');
    }

}
