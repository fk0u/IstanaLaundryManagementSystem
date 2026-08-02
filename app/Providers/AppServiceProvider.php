<?php

namespace App\Providers;

use App\Models\ChartOfAccount;
use App\Models\GoodsReceivedNote;
use App\Models\Order;
use App\Models\Service;
use App\Models\SystemSetting;
use App\Observers\ChartOfAccountObserver;
use App\Observers\GRNObserver;
use App\Observers\OrderObserver;
use App\Observers\ServiceObserver;
use App\Observers\SystemSettingObserver;
use App\Services\AuditLogService;
use App\Services\CacheService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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

        $this->app->singleton(CacheService::class, function ($app) {
            return new CacheService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Model Observers
        Order::observe(OrderObserver::class);
        GoodsReceivedNote::observe(GRNObserver::class);
        Service::observe(ServiceObserver::class);
        SystemSetting::observe(SystemSettingObserver::class);
        ChartOfAccount::observe(ChartOfAccountObserver::class);

        // Configure Rate Limiters
        $this->configureRateLimiting();

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

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('pos-checkout', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('public-tracking', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });
    }
}
