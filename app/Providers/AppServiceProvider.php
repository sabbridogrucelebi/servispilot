<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);
        
        // Global Ayarları Mailer'a Uygula
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('global_settings')) {
                $settings = \Illuminate\Support\Facades\Cache::remember('global_settings', 3600, function () {
                    return \App\Models\GlobalSetting::all()->pluck('value', 'key');
                });

                if ($settings->has('smtp_host')) {
                    config([
                        'mail.mailers.smtp.host' => $settings->get('smtp_host'),
                        'mail.mailers.smtp.port' => $settings->get('smtp_port', 587),
                        'mail.mailers.smtp.username' => $settings->get('smtp_user'),
                        'mail.mailers.smtp.password' => $settings->get('smtp_pass'),
                        'mail.mailers.smtp.encryption' => $settings->get('smtp_encryption') === 'null' ? null : $settings->get('smtp_encryption'),
                        'mail.from.address' => $settings->get('smtp_from_address', $settings->get('smtp_user')),
                        'mail.from.name' => $settings->get('smtp_from_name', config('app.name')),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Migrations aşamasında veya DB yokken hata almamak için
        }

        view()->composer('*', function ($view) {
            $navItems = config('navigation.items', []);
            $view->with('navItems', $navItems);
        });
    }
}
