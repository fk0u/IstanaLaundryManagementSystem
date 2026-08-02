<?php

namespace App\Observers;

use App\Models\Service;
use App\Services\CacheService;

class ServiceObserver
{
    public function saved(Service $service): void
    {
        app(CacheService::class)->clearServicesCatalogCache();
    }

    public function deleted(Service $service): void
    {
        app(CacheService::class)->clearServicesCatalogCache();
    }
}
