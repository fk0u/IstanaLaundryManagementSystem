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
        $this->app->singleton(\App\Services\AuditLogService::class, function ($app) {
            return new \App\Services\AuditLogService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\Order::observe(\App\Observers\OrderObserver::class);
        \App\Models\GoodsReceivedNote::observe(\App\Observers\GRNObserver::class);

        // Fix for Ngrok / Reverse Proxy Asset & HTTPS loading
        $isNgrok = str_contains(request()->header('host', ''), 'ngrok') || request()->header('x-forwarded-proto') === 'https';
        
        if ($isNgrok) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            $schemeAndHost = 'https://' . (request()->header('x-forwarded-host') ?: request()->header('host'));
            \Illuminate\Support\Facades\URL::forceRootUrl($schemeAndHost);
            config(['app.url' => $schemeAndHost]);
            config(['app.asset_url' => $schemeAndHost]);
        } elseif (request()->header('x-forwarded-host')) {
            $schemeAndHost = 'http://' . request()->header('x-forwarded-host');
            \Illuminate\Support\Facades\URL::forceRootUrl($schemeAndHost);
            config(['app.url' => $schemeAndHost]);
            config(['app.asset_url' => $schemeAndHost]);
        }
    }
}
