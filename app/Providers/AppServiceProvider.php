<?php

namespace App\Providers;

use App\Models\QuickLink;
use App\Models\SiteSetting;
use App\Models\Slider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
        $this->shareFooterMeta();
    }

    /**
     * A "last updated" stamp that reflects when the published content actually
     * changed, shared with every view that renders the layout.
     *
     * Printing today's date, which is the usual way this gets built, would have
     * the portal claim it was updated today on every single request. Instead
     * this takes the newest updated_at across the three things a citizen sees
     * change: the settings, the hero slides and the footer links. Cached for a
     * day so it costs nothing per request, and the cache is keyed by date so a
     * stale entry cannot outlive the day it was computed.
     */
    private function shareFooterMeta(): void
    {
        View::composer('layouts.app', function ($view) {
            $updatedAt = null;

            try {
                $updatedAt = Cache::remember('footer.content_updated_at', now()->addDay(), function () {
                    $stamps = [];

                    foreach ([SiteSetting::class, Slider::class, QuickLink::class] as $model) {
                        if (!Schema::hasTable((new $model)->getTable())) {
                            continue;
                        }

                        $latest = $model::max('updated_at');

                        if (!empty($latest)) {
                            $stamps[] = $latest;
                        }
                    }

                    return $stamps === [] ? null : max($stamps);
                });
            } catch (\Throwable $e) {
                // A missing database or cache store must not take the layout down.
            }

            $view->with('contentUpdatedAt', $updatedAt ? Carbon::parse($updatedAt) : null);
        });
    }

    private function configureSmtpFromSettings(): void
    {
        try {
            if (!Schema::hasTable('site_settings')) {
                return;
            }

            $smtpEnabled = strtolower(trim((string) SiteSetting::get('smtp_enabled', '0')));
            if (!in_array($smtpEnabled, ['1', 'true', 'yes', 'on'], true)) {
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

            // Symfony mailer supports only smtp / smtps as scheme values.
            $scheme = $encryption === 'ssl' ? 'smtps' : 'smtp';
            $resolvedPort = $port > 0 ? $port : ($encryption === 'ssl' ? 465 : 587);

            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.url', null);
            Config::set('mail.mailers.smtp.host', $host);
            Config::set('mail.mailers.smtp.port', $resolvedPort);
            Config::set('mail.mailers.smtp.username', $username);
            Config::set('mail.mailers.smtp.password', $password);
            Config::set('mail.mailers.smtp.scheme', $scheme);
            Config::set('mail.mailers.smtp.timeout', $timeout > 0 ? $timeout : 30);

            Config::set('mail.from.address', $fromAddress);
            Config::set('mail.from.name', $fromName !== '' ? $fromName : config('app.name'));
        } catch (\Throwable $e) {
            // Keep default environment mail configuration when settings are unavailable.
        }
    }
}
