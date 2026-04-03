<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        $this->configureSmtpFromSettings();
    }

    private function configureSmtpFromSettings(): void
    {
        try {
            if (!Schema::hasTable('site_settings')) {
                return;
            }

            if (SiteSetting::get('smtp_enabled', '0') !== '1') {
                return;
            }

            $host = (string) SiteSetting::get('smtp_host', '');
            $username = (string) SiteSetting::get('smtp_username', '');
            $fromAddress = (string) SiteSetting::get('smtp_from_address', '');

            if ($host === '' || $username === '' || $fromAddress === '') {
                return;
            }

            $port = (int) SiteSetting::get('smtp_port', '587');
            $password = (string) SiteSetting::get('smtp_password', '');
            $encryption = strtolower((string) SiteSetting::get('smtp_encryption', 'tls'));
            $fromName = (string) SiteSetting::get('smtp_from_name', config('app.name'));
            $timeout = (int) SiteSetting::get('smtp_timeout', '30');

            $scheme = in_array($encryption, ['tls', 'ssl'], true) ? $encryption : null;

            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.host', $host);
            Config::set('mail.mailers.smtp.port', $port > 0 ? $port : 587);
            Config::set('mail.mailers.smtp.username', $username);
            if ($password !== '') {
                Config::set('mail.mailers.smtp.password', $password);
            }
            Config::set('mail.mailers.smtp.scheme', $scheme);
            Config::set('mail.mailers.smtp.timeout', $timeout > 0 ? $timeout : 30);

            Config::set('mail.from.address', $fromAddress);
            Config::set('mail.from.name', $fromName !== '' ? $fromName : config('app.name'));
        } catch (\Throwable $e) {
            // Keep default environment mail configuration when settings are unavailable.
        }
    }
}
