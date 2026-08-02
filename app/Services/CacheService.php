<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Payroll;
use App\Models\Service;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheService
{
    /**
     * Cache TTL definitions in seconds.
     */
    const TTL_SETTINGS = 86400;   // 24 Hours
    const TTL_BRANCHES = 3600;    // 1 Hour
    const TTL_SERVICES = 3600;    // 1 Hour
    const TTL_COA = 86400;         // 24 Hours
    const TTL_DASHBOARD = 60;     // 60 Seconds (Ultra responsive)

    /**
     * Get cached System Settings key-value pairs.
     */
    public function getSystemSettings(): array
    {
        return Cache::remember('system:settings:all', self::TTL_SETTINGS, function () {
            return SystemSetting::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get a specific system setting by key with fallback.
     */
    public function getSetting(string $key, ?string $default = null): ?string
    {
        $settings = $this->getSystemSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Clear system settings cache.
     */
    public function clearSystemSettingsCache(): void
    {
        Cache::forget('system:settings:all');
    }

    /**
     * Get cached active branches list.
     */
    public function getActiveBranches(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('branches:active:list', self::TTL_BRANCHES, function () {
            return Branch::where('is_active', true)->orderBy('id')->get();
        });
    }

    /**
     * Clear active branches cache.
     */
    public function clearBranchesCache(): void
    {
        Cache::forget('branches:active:list');
        Cache::forget('branches:list');
    }

    /**
     * Get cached Service Catalog for a specific branch or global.
     */
    public function getServicesCatalog(?int $branchId = null): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = 'catalog:services:branch_' . ($branchId ?? 'global');

        return Cache::remember($cacheKey, self::TTL_SERVICES, function () use ($branchId) {
            $query = Service::where('is_active', true)->with(['branchPrices']);
            if ($branchId) {
                $query->with(['branchPrices' => function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                }]);
            }
            return $query->orderBy('name')->get();
        });
    }

    /**
     * Clear services catalog cache for all branches.
     */
    public function clearServicesCatalogCache(): void
    {
        Cache::forget('catalog:services:branch_global');
        $branches = Branch::pluck('id');
        foreach ($branches as $bId) {
            Cache::forget("catalog:services:branch_{$bId}");
        }
    }

    /**
     * Get cached Chart of Accounts hierarchy tree.
     */
    public function getChartOfAccountsTree(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('coa:hierarchy:tree', self::TTL_COA, function () {
            return ChartOfAccount::whereNull('parent_code')
                ->orderBy('code')
                ->get();
        });
    }

    /**
     * Clear Chart of Accounts cache.
     */
    public function clearChartOfAccountsCache(): void
    {
        Cache::forget('coa:hierarchy:tree');
    }

    /**
     * Get fast cached dashboard metrics for a specific branch or all branches.
     */
    public function getDashboardMetrics(?int $branchId = null): array
    {
        $cacheKey = 'dashboard:metrics:branch_' . ($branchId ?? 'all');

        return Cache::remember($cacheKey, self::TTL_DASHBOARD, function () use ($branchId) {
            $today = now()->toDateString();

            // Revenue query
            $revenueQuery = Order::where('payment_status', 'paid')
                ->whereDate('created_at', $today);
            if ($branchId) {
                $revenueQuery->where('branch_id', $branchId);
            }
            $todayRevenue = (float) $revenueQuery->sum('total');

            // Active orders query (not completed/diambil)
            $activeOrdersQuery = Order::whereNotIn('production_status', ['DIAMBIL']);
            if ($branchId) {
                $activeOrdersQuery->where('branch_id', $branchId);
            }
            $activeOrdersCount = $activeOrdersQuery->count();

            // Low stock items count
            $lowStockQuery = InventoryItem::whereRaw('current_stock <= min_stock');
            if ($branchId) {
                $lowStockQuery->where('branch_id', $branchId);
            }
            $lowStockCount = $lowStockQuery->count();

            // Pending payroll count
            $pendingPayrollQuery = Payroll::where('status', 'draft');
            if ($branchId) {
                $pendingPayrollQuery->where('branch_id', $branchId);
            }
            $pendingPayrollCount = $pendingPayrollQuery->count();

            return [
                'today_revenue' => $todayRevenue,
                'active_orders' => $activeOrdersCount,
                'low_stock_items' => $lowStockCount,
                'pending_payrolls' => $pendingPayrollCount,
                'cached_at' => now()->toTimeString(),
            ];
        });
    }

    /**
     * Invalidate dashboard metrics cache.
     */
    public function clearDashboardCache(?int $branchId = null): void
    {
        Cache::forget('dashboard:metrics:branch_all');
        if ($branchId) {
            Cache::forget("dashboard:metrics:branch_{$branchId}");
        }
    }

    /**
     * Flush all application domain caches.
     */
    public function clearAllCaches(): void
    {
        $this->clearSystemSettingsCache();
        $this->clearBranchesCache();
        $this->clearServicesCatalogCache();
        $this->clearChartOfAccountsCache();
        $this->clearDashboardCache();
    }
}
