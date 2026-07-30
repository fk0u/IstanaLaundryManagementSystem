<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Workshop;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Display the dynamic dashboard based on user role.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance', 'CS_Marketing'])) {
            return $this->ownerDashboard();
        } elseif ($user->hasRole('Branch_Admin')) {
            return $this->branchAdminDashboard();
        } elseif ($user->hasRole('Cashier')) {
            return $this->cashierDashboard();
        } elseif ($user->hasAnyRole(['Workshop_Admin', 'Workshop_Staff'])) {
            return $this->workshopDashboard();
        }

        abort(403, 'Peran Anda tidak dikenali oleh sistem.');
    }

    /**
     * Dashboard for Owner, Super Admin, Developer, CS Marketing, and Finance.
     */
    protected function ownerDashboard()
    {
        // Global-role users (Owner, etc.) default to consolidated view (null).
        // session('scoped_branch_id') overrides ONLY when it has a truthy value,
        // i.e. user explicitly picked a branch.  After switching back to "global"
        // the route forgets the session key → null → consolidated view.
        $branchId = session('scoped_branch_id') ?: null;

        $ordersQuery = $branchId ? Order::where('branch_id', $branchId) : Order::withoutGlobalScopes();
        $customersQuery = $branchId ? Customer::where('branch_id', $branchId) : Customer::query();
        $workshopsQuery = $branchId ? Workshop::where('branch_id', $branchId) : Workshop::query();

        $totalRevenue = (float) $ordersQuery->sum('total');

        // Finance Specific Metrics
        $piutangQuery = $branchId ? Order::where('branch_id', $branchId) : Order::withoutGlobalScopes();
        $totalPiutang = (float) $piutangQuery->whereIn('payment_status', ['pending', 'partial'])->selectRaw('SUM(total - paid_amount) as total_unpaid')->value('total_unpaid') ?? 0;

        $monthCashFlowQuery = $branchId ? Order::where('branch_id', $branchId) : Order::withoutGlobalScopes();
        $monthCashFlow = (float) $monthCashFlowQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('paid_amount');

        $activeOrdersQuery = $branchId ? Order::where('branch_id', $branchId) : Order::withoutGlobalScopes();
        $activeOrdersCount = $activeOrdersQuery->where('production_status', '!=', 'DIAMBIL')->count();

        $newCustomersCount = $customersQuery->where('created_at', '>=', now()->startOfMonth())->count();
        $activeWorkshops = $workshopsQuery->where('is_active', true)->count();

        $totalTransactionsQuery = $branchId ? Order::where('branch_id', $branchId) : Order::withoutGlobalScopes();
        $totalTransactions = $totalTransactionsQuery->count();

        // Latest 5 orders
        $recentOrdersQuery = $branchId ? Order::where('branch_id', $branchId) : Order::withoutGlobalScopes();
        $recentOrders = $recentOrdersQuery->with(['customer', 'items.service', 'branch'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // MoM growth calculations
        $currentMonthRev = $branchId ? Order::where('branch_id', $branchId) : Order::withoutGlobalScopes();
        $lastMonthRev = $branchId ? Order::where('branch_id', $branchId) : Order::withoutGlobalScopes();
        $currentMonthTotal = (float) $currentMonthRev->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total');
        $lastMonthTotal = (float) $lastMonthRev->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->sum('total');
        $growthPercent = 0;
        if ($lastMonthTotal > 0) {
            $growthPercent = (($currentMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100;
        }

        // Top Performing Branch
        $topBranch = Branch::withoutGlobalScopes()
            ->withSum(['orders as total_revenue' => function ($q) {
                $q->withoutGlobalScopes();
            }], 'total')
            ->orderByDesc('total_revenue')
            ->first();
        $topBranchName = $topBranch ? $topBranch->name : 'N/A';
        $topBranchRevenue = $topBranch ? (float) $topBranch->total_revenue : 0;

        // Chart.js data
        $chartLabels = [];
        $chartValues = [];
        if (! $branchId) {
            // Global view: compare branches — one aggregated query without global scope
            $perBranch = Order::withoutGlobalScopes()
                ->selectRaw('branch_id, SUM(total) as revenue')
                ->whereNotNull('branch_id')
                ->groupBy('branch_id')
                ->pluck('revenue', 'branch_id');

            $branchesListCached = Cache::remember('branches:list', 300, fn () => Branch::all());
            foreach ($branchesListCached as $branch) {
                if (is_object($branch) && isset($branch->name)) {
                    $chartLabels[] = $branch->name;
                    $chartValues[] = (float) ($perBranch[$branch->id] ?? 0);
                } elseif (is_string($branch)) {
                    $chartLabels[] = $branch;
                    $chartValues[] = 0;
                }
            }
            $chartTitle = 'Komparasi Pendapatan Cabang';
            $chartSub = 'Total akumulasi pendapatan per cabang (Rupiah)';
        } else {
            // Branch view: 7-day trend built from a single grouped query.
            [$chartLabels, $chartValues] = $this->weeklyRevenueTrend($branchId);
            $chartTitle = 'Tren Pendapatan Mingguan';
            $chartSub = 'Data 7 hari terakhir (Rupiah)';
        }

        $branchesList = Cache::remember('branches:list', 300, fn () => Branch::all());

        return view('dashboard.owner', compact(
            'totalRevenue',
            'totalPiutang',
            'monthCashFlow',
            'activeOrdersCount',
            'newCustomersCount',
            'activeWorkshops',
            'totalTransactions',
            'recentOrders',
            'chartLabels',
            'chartValues',
            'chartTitle',
            'chartSub',
            'branchId',
            'growthPercent',
            'topBranchName',
            'topBranchRevenue',
            'branchesList'
        ));
    }

    /**
     * Dashboard for Branch Admin.
     */
    protected function branchAdminDashboard()
    {
        $branchId = Auth::user()->branch_id;
        $branch = Branch::findOrFail($branchId);

        // Scoped values
        $totalRevenue = Order::where('branch_id', $branchId)->sum('total');
        $activeOrdersCount = Order::where('branch_id', $branchId)
            ->where('production_status', '!=', 'DIAMBIL')
            ->count();

        $lowStockCount = InventoryItem::where('branch_id', $branchId)
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->count();

        $todayTransactions = Order::where('branch_id', $branchId)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $recentOrders = Order::where('branch_id', $branchId)
            ->with(['customer', 'items.service'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 7 days daily revenue trend for chart — single grouped query.
        [$chartLabels, $chartValues] = $this->weeklyRevenueTrend($branchId);

        return view('dashboard.branch_admin', compact(
            'branch',
            'totalRevenue',
            'activeOrdersCount',
            'lowStockCount',
            'todayTransactions',
            'recentOrders',
            'chartLabels',
            'chartValues'
        ));
    }

    /**
     * Dashboard for Cashier.
     */
    protected function cashierDashboard()
    {
        $branchId = Auth::user()->branch_id;
        $branch = Branch::findOrFail($branchId);

        // Scoped cashier stats
        $todayTransactionsCount = Order::where('branch_id', $branchId)
            ->where('cashier_id', Auth::id())
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $todayRevenue = Order::where('branch_id', $branchId)
            ->where('cashier_id', Auth::id())
            ->whereDate('created_at', now()->toDateString())
            ->sum('total');

        $customerCount = Customer::where('branch_id', $branchId)->count();

        $recentOrders = Order::where('branch_id', $branchId)
            ->where('cashier_id', Auth::id())
            ->with(['customer'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.cashier', compact(
            'branch',
            'todayTransactionsCount',
            'todayRevenue',
            'customerCount',
            'recentOrders'
        ));
    }

    /**
     * Dashboard for Workshop Admin and Staff.
     */
    protected function workshopDashboard()
    {
        $branchId = Auth::user()->branch_id;
        $branch = Branch::findOrFail($branchId);

        // Group status stats
        $statusCounts = Order::where('branch_id', $branchId)
            ->selectRaw('production_status, count(*) as count')
            ->groupBy('production_status')
            ->pluck('count', 'production_status')
            ->toArray();

        // Standardize keys
        $statuses = ['TERIMA', 'CUCI', 'KERING', 'SETRIKA', 'PACKING', 'SIAP'];
        $stats = [];
        $totalActive = 0;
        foreach ($statuses as $status) {
            $stats[$status] = $statusCounts[$status] ?? 0;
            if ($status !== 'SIAP') {
                $totalActive += $stats[$status];
            }
        }

        $completedTodayCount = Order::where('branch_id', $branchId)
            ->where('production_status', 'SIAP')
            ->whereDate('updated_at', now()->toDateString())
            ->count();

        $activeProductionOrders = Order::where('branch_id', $branchId)
            ->whereIn('production_status', ['TERIMA', 'CUCI', 'KERING', 'SETRIKA', 'PACKING'])
            ->with(['customer', 'items.service'])
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        return view('dashboard.workshop', compact(
            'branch',
            'stats',
            'totalActive',
            'completedTodayCount',
            'activeProductionOrders'
        ));
    }

    /**
     * Build a 7-day revenue trend for a branch from a single grouped query,
     * filling zero-revenue days instead of running one query per day.
     *
     * @return array{0: array<int, string>, 1: array<int, float>}
     */
    protected function weeklyRevenueTrend(int $branchId): array
    {
        $start = now()->subDays(6)->startOfDay();

        $daily = Order::query()
            ->selectRaw('DATE(created_at) as day, SUM(total) as revenue')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$start, now()->endOfDay()])
            ->groupByRaw('DATE(created_at)')
            ->pluck('revenue', 'day');

        $labels = [];
        $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->toDateString();
            $labels[] = $date->format('D, d M');
            $values[] = (float) ($daily[$key] ?? 0);
        }

        return [$labels, $values];
    }
}
