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

        if ($user->hasRole('Developer')) {
            if (request('view') === 'business') {
                return $this->ownerDashboard();
            }
            return $this->developerDashboard();
        } elseif ($user->hasAnyRole(['Owner', 'Super_Admin', 'Finance', 'CS_Marketing'])) {
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
     * Comprehensive Developer Telemetry & System Operations Dashboard.
     */
    protected function developerDashboard()
    {
        $telemetryService = app(\App\Services\System\ServerTelemetryService::class);
        $telemetry = $telemetryService->getTelemetryData();

        return view('dashboard.developer', compact('telemetry'));
    }

    /**
     * Clear application cache from developer dashboard.
     */
    public function developerClearCache()
    {
        abort_unless(Auth::user()->hasRole('Developer'), 403);
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        \Illuminate\Support\Facades\Artisan::call('config:cache');
        \Illuminate\Support\Facades\Artisan::call('route:cache');
        \Illuminate\Support\Facades\Artisan::call('view:cache');

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Cache aplikasi berhasil dibersihkan dan dioptimasi!']);
        }

        return back()->with('success', 'Cache aplikasi berhasil dibersihkan dan dioptimasi!');
    }

    /**
     * Test database latency ping.
     */
    public function developerDbPing()
    {
        abort_unless(Auth::user()->hasRole('Developer'), 403);
        $start = microtime(true);
        \Illuminate\Support\Facades\DB::select('SELECT 1');
        $latency = round((microtime(true) - $start) * 1000, 2);

        return response()->json([
            'success' => true,
            'latency_ms' => $latency,
            'status' => 'Connected',
            'timestamp' => now()->format('H:i:s'),
        ]);
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

        // Branch comparison & ranking statistics (Real backend data for owner dashboard)
        $branches = Branch::withoutGlobalScopes()
            ->withCount(['orders as total_orders' => function ($q) {
                $q->withoutGlobalScopes();
            }])
            ->withSum(['orders as total_revenue' => function ($q) {
                $q->withoutGlobalScopes();
            }], 'total')
            ->orderByDesc('total_revenue')
            ->get();

        $topBranch = $branches->first();
        $topBranchName = $topBranch ? $topBranch->name : 'N/A';
        $topBranchRevenue = $topBranch ? (float) $topBranch->total_revenue : 0;

        $branchRankings = $branches->map(function ($branch) use ($totalRevenue) {
            $rev = (float) ($branch->total_revenue ?? 0);
            $share = $totalRevenue > 0 ? round(($rev / $totalRevenue) * 100, 1) : 0;
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code ?? '',
                'revenue' => $rev,
                'orders_count' => $branch->total_orders ?? 0,
                'share_percent' => $share,
            ];
        })->toArray();

        // Chart.js data
        $chartLabels = [];
        $chartValues = [];
        if (! $branchId) {
            // Global view: compare branches - populate labels and values directly from $branches collection
            foreach ($branches as $branch) {
                $chartLabels[] = $branch->name;
                $chartValues[] = (float) ($branch->total_revenue ?? 0);
            }
            $chartTitle = 'Komparasi Pendapatan Cabang';
            $chartSub = 'Total akumulasi pendapatan per cabang (Rupiah)';
        } else {
            // Branch view: 7-day trend built from a single grouped query.
            [$chartLabels, $chartValues] = $this->weeklyRevenueTrend($branchId);
            $chartTitle = 'Tren Pendapatan Mingguan';
            $chartSub = 'Data 7 hari terakhir (Rupiah)';
        }

        // Real Production breakdown counts from DB
        $prodStatusQuery = $branchId ? Order::where('branch_id', $branchId) : Order::withoutGlobalScopes();
        $productionCountsRaw = $prodStatusQuery->whereNotIn('production_status', ['DIAMBIL'])
            ->selectRaw('production_status, count(*) as count')
            ->groupBy('production_status')
            ->pluck('count', 'production_status')
            ->toArray();

        $productionBreakdown = [
            'TERIMA' => (int) ($productionCountsRaw['TERIMA'] ?? 0),
            'PILAH'  => (int) ($productionCountsRaw['PILAH'] ?? 0),
            'CUCI'   => (int) ($productionCountsRaw['CUCI'] ?? 0),
            'KERING' => (int) ($productionCountsRaw['KERING'] ?? 0),
            'LIPAT'  => (int) ($productionCountsRaw['LIPAT'] ?? 0),
            'CEK'    => (int) ($productionCountsRaw['CEK'] ?? 0),
            'SIAP'   => (int) ($productionCountsRaw['SIAP'] ?? 0),
        ];

        $branchesList = $branches;

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
            'branchesList',
            'branchRankings',
            'productionBreakdown'
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

        $productionCountsRaw = Order::where('branch_id', $branchId)
            ->whereNotIn('production_status', ['DIAMBIL'])
            ->selectRaw('production_status, count(*) as count')
            ->groupBy('production_status')
            ->pluck('count', 'production_status')
            ->toArray();

        $productionBreakdown = [
            'TERIMA' => (int) ($productionCountsRaw['TERIMA'] ?? 0),
            'PILAH'  => (int) ($productionCountsRaw['PILAH'] ?? 0),
            'CUCI'   => (int) ($productionCountsRaw['CUCI'] ?? 0),
            'KERING' => (int) ($productionCountsRaw['KERING'] ?? 0),
            'LIPAT'  => (int) ($productionCountsRaw['LIPAT'] ?? 0),
            'CEK'    => (int) ($productionCountsRaw['CEK'] ?? 0),
            'SIAP'   => (int) ($productionCountsRaw['SIAP'] ?? 0),
        ];

        return view('dashboard.branch_admin', compact(
            'branch',
            'totalRevenue',
            'activeOrdersCount',
            'lowStockCount',
            'todayTransactions',
            'recentOrders',
            'chartLabels',
            'chartValues',
            'productionBreakdown'
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

        $productionCountsRaw = Order::where('branch_id', $branchId)
            ->whereNotIn('production_status', ['DIAMBIL'])
            ->selectRaw('production_status, count(*) as count')
            ->groupBy('production_status')
            ->pluck('count', 'production_status')
            ->toArray();

        $productionBreakdown = [
            'TERIMA' => (int) ($productionCountsRaw['TERIMA'] ?? 0),
            'PILAH'  => (int) ($productionCountsRaw['PILAH'] ?? 0),
            'CUCI'   => (int) ($productionCountsRaw['CUCI'] ?? 0),
            'KERING' => (int) ($productionCountsRaw['KERING'] ?? 0),
            'LIPAT'  => (int) ($productionCountsRaw['LIPAT'] ?? 0),
            'CEK'    => (int) ($productionCountsRaw['CEK'] ?? 0),
            'SIAP'   => (int) ($productionCountsRaw['SIAP'] ?? 0),
        ];

        return view('dashboard.cashier', compact(
            'branch',
            'todayTransactionsCount',
            'todayRevenue',
            'customerCount',
            'recentOrders',
            'productionBreakdown'
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

        // Standardize keys with single source of truth
        $statuses = ['TERIMA', 'PILAH', 'CUCI', 'KERING', 'LIPAT', 'CEK', 'SIAP'];
        $stats = [];
        $totalActive = 0;
        foreach ($statuses as $status) {
            $stats[$status] = (int) ($statusCounts[$status] ?? 0);
            if ($status !== 'SIAP') {
                $totalActive += $stats[$status];
            }
        }

        $productionBreakdown = $stats;

        $completedTodayCount = Order::where('branch_id', $branchId)
            ->where('production_status', 'SIAP')
            ->whereDate('updated_at', now()->toDateString())
            ->count();

        $activeProductionOrders = Order::where('branch_id', $branchId)
            ->whereIn('production_status', ['TERIMA', 'PILAH', 'CUCI', 'KERING', 'LIPAT', 'CEK'])
            ->with(['customer', 'items.service'])
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        return view('dashboard.workshop', compact(
            'branch',
            'stats',
            'totalActive',
            'completedTodayCount',
            'activeProductionOrders',
            'productionBreakdown'
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
