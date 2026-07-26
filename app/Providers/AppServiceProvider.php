<?php

namespace App\Providers;

use App\Models\GoodsReceivedNote;
use App\Models\Order;
use App\Observers\GRNObserver;
use App\Observers\OrderObserver;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AuditLogService::class, function ($app) {
            return new AuditLogService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        GoodsReceivedNote::observe(GRNObserver::class);

        // Fix for Ngrok / Reverse Proxy Asset & HTTPS loading
        $isNgrok = str_contains(request()->header('host', ''), 'ngrok') || request()->header('x-forwarded-proto') === 'https';

        if ($isNgrok) {
            URL::forceScheme('https');
            $schemeAndHost = 'https://'.(request()->header('x-forwarded-host') ?: request()->header('host'));
            URL::forceRootUrl($schemeAndHost);
            config(['app.url' => $schemeAndHost]);
            config(['app.asset_url' => $schemeAndHost]);
        } elseif (request()->header('x-forwarded-host')) {
            $schemeAndHost = 'http://'.request()->header('x-forwarded-host');
            URL::forceRootUrl($schemeAndHost);
            config(['app.url' => $schemeAndHost]);
            config(['app.asset_url' => $schemeAndHost]);
        }
    }
}
