<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\InventoryItem;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $branchId = session('scoped_branch_id') ?? Auth::user()->branch_id;
        
        $ordersQuery = Order::query();
        $customersQuery = Customer::query();
        $workshopsQuery = Workshop::query();

        if ($branchId) {
            $ordersQuery->where('branch_id', $branchId);
            $customersQuery->where('branch_id', $branchId);
            $workshopsQuery->where('branch_id', $branchId);
        }

        $totalRevenue = $ordersQuery->sum('total');
        
        $activeOrdersCount = Order::query();
        if ($branchId) {
            $activeOrdersCount->where('branch_id', $branchId);
        }
        $activeOrdersCount = $activeOrdersCount->where('production_status', '!=', 'DIAMBIL')->count();

        $newCustomersCount = $customersQuery->where('created_at', '>=', now()->startOfMonth())->count();
        $activeWorkshops = $workshopsQuery->where('is_active', true)->count();
        
        $totalTransactionsQuery = Order::query();
        if ($branchId) {
            $totalTransactionsQuery->where('branch_id', $branchId);
        }
        $totalTransactions = $totalTransactionsQuery->count();

        // Latest 5 orders
        $recentOrders = Order::query();
        if ($branchId) {
            $recentOrders->where('branch_id', $branchId);
        }
        $recentOrders = $recentOrders->with(['customer', 'orderItems.service'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // MoM growth calculations
        $currentMonthRev = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
        $lastMonthRev = Order::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year);
        if ($branchId) {
            $currentMonthRev->where('branch_id', $branchId);
            $lastMonthRev->where('branch_id', $branchId);
        }
        $currentMonthTotal = (float)$currentMonthRev->sum('total');
        $lastMonthTotal = (float)$lastMonthRev->sum('total');
        $growthPercent = 0;
        if ($lastMonthTotal > 0) {
            $growthPercent = (($currentMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100;
        }

        // Top Performing Branch
        $topBranch = Branch::withSum('orders as total_revenue', 'total')
            ->orderByDesc('total_revenue')
            ->first();
        $topBranchName = $topBranch ? $topBranch->name : 'N/A';
        $topBranchRevenue = $topBranch ? (float)$topBranch->total_revenue : 0;

        // Chart.js data
        $chartLabels = [];
        $chartValues = [];
        if (!$branchId) {
            // Global view: compare branches
            $branches = Branch::all();
            foreach ($branches as $branch) {
                $revenue = Order::where('branch_id', $branch->id)->sum('total');
                $chartLabels[] = $branch->name;
                $chartValues[] = (float)$revenue;
            }
            $chartTitle = "Komparasi Pendapatan Cabang";
            $chartSub = "Total akumulasi pendapatan per cabang (Rupiah)";
        } else {
            // Branch view: show 7 days trend
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $revenue = Order::where('branch_id', $branchId)->whereDate('created_at', $date->toDateString())->sum('total');
                $chartLabels[] = $date->format('D, d M');
                $chartValues[] = (float)$revenue;
            }
            $chartTitle = "Tren Pendapatan Mingguan";
            $chartSub = "Data 7 hari terakhir (Rupiah)";
        }

        $branchesList = Branch::all();

        return view('dashboard.owner', compact(
            'totalRevenue', 
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
            ->with(['customer', 'orderItems.service'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 7 days daily revenue trend for chart
        $chartLabels = [];
        $chartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenue = Order::where('branch_id', $branchId)->whereDate('created_at', $date->toDateString())->sum('total');
            $chartLabels[] = $date->format('D, d M');
            $chartValues[] = (float)$revenue;
        }

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
            ->with(['customer', 'orderItems.service'])
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
}
