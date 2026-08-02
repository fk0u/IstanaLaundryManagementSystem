<?php

namespace App\Observers;

use App\Models\ChartOfAccount;
use App\Services\CacheService;

class ChartOfAccountObserver
{
    public function saved(ChartOfAccount $coa): void
    {
        app(CacheService::class)->clearChartOfAccountsCache();
    }

    public function deleted(ChartOfAccount $coa): void
    {
        app(CacheService::class)->clearChartOfAccountsCache();
    }
}
