<?php

namespace App\Observers;

use App\Models\SystemSetting;
use App\Services\CacheService;

class SystemSettingObserver
{
    public function saved(SystemSetting $setting): void
    {
        app(CacheService::class)->clearSystemSettingsCache();
    }

    public function deleted(SystemSetting $setting): void
    {
        app(CacheService::class)->clearSystemSettingsCache();
    }
}
