<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Models\WebsiteSetting;

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

        // Default settings jika tabel belum ada atau masih kosong
        // logo dibiarkan null supaya accessor getLogoUrlAttribute()
        // otomatis mengembalikan default_logo.png
        $defaultSettings = new WebsiteSetting([
            'nama'  => 'Website',
            'logo'  => null,
        ]);

        // Inject variabel $settings ke SEMUA view (admin + public)
        View::composer('*', function ($view) use ($defaultSettings) {
            $settings = $defaultSettings;

            if (Schema::hasTable('website_settings')) {
                $settings = WebsiteSetting::first() ?? $defaultSettings;
            }

            $view->with('settings', $settings);
        });
    }
}
