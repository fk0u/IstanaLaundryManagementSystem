<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/track', function (\Illuminate\Http\Request $request) {
    $orderNumber = $request->input('order_number');
    
    if (!$orderNumber) {
        return redirect('/')->with('error', 'Masukkan nomor nota terlebih dahulu.');
    }

    $order = \App\Models\Order::with(['customer', 'branch', 'orderItems.service', 'productionStatusLogs.updater'])
        ->where('order_number', $orderNumber)
        ->first();

    return view('track', compact('order', 'orderNumber'));
})->name('track');

Route::get('/dashboard', function () {
    $branchId = session('scoped_branch_id') ?? auth()->user()->branch_id;
    
    // Scoped queries if branchId exists
    $ordersQuery = \App\Models\Order::query();
    $customersQuery = \App\Models\Customer::query();
    $workshopsQuery = \App\Models\Workshop::query();

    if ($branchId) {
        $ordersQuery->where('branch_id', $branchId);
        $customersQuery->where('branch_id', $branchId);
        $workshopsQuery->where('branch_id', $branchId);
    }

    $totalRevenue = $ordersQuery->sum('total');
    $activeOrdersCount = $ordersQuery->where('production_status', '!=', 'DIAMBIL')->count();
    $newCustomersCount = $customersQuery->where('created_at', '>=', now()->startOfMonth())->count();
    $activeWorkshops = $workshopsQuery->where('is_active', true)->count();
    $totalTransactions = $ordersQuery->count();

    // Latest 5 orders
    $recentOrders = \App\Models\Order::query();
    if ($branchId) {
        $recentOrders->where('branch_id', $branchId);
    }
    $recentOrders = $recentOrders->with(['customer', 'orderItems.service'])
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

    // Branch revenue comparison or Weekly revenue data for chart
    $chartData = [];
    if (!$branchId) {
        // Global: compare all branches
        $branches = \App\Models\Branch::all();
        foreach ($branches as $branch) {
            $revenue = \App\Models\Order::where('branch_id', $branch->id)->sum('total');
            $chartData[] = [
                'label' => $branch->name,
                'amount' => $revenue,
                'formatted' => 'Rp ' . number_format($revenue / 1000, 0, ',', '.') . 'K',
            ];
        }
        $chartTitle = "Komparasi Pendapatan Cabang";
        $chartSub = "Total akumulasi pendapatan per cabang (Rupiah)";
    } else {
        // Scoped branch: show last 7 days daily revenue trend
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenue = \App\Models\Order::where('branch_id', $branchId)->whereDate('created_at', $date->toDateString())->sum('total');
            $chartData[] = [
                'label' => $date->format('D'),
                'amount' => $revenue,
                'formatted' => 'Rp ' . number_format($revenue / 1000, 0, ',', '.') . 'K',
            ];
        }
        $chartTitle = "Tren Pendapatan Mingguan";
        $chartSub = "Data 7 hari terakhir (Rupiah)";
    }

    // Find max revenue for chart scaling
    $maxRevenue = collect($chartData)->max('amount') ?: 1000;

    return view('dashboard', compact(
        'totalRevenue', 
        'activeOrdersCount', 
        'newCustomersCount', 
        'activeWorkshops', 
        'totalTransactions',
        'recentOrders',
        'chartData',
        'chartTitle',
        'chartSub',
        'maxRevenue',
        'branchId'
    ));
})->middleware(['auth', 'verified', 'branch.scope'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// POS & Production Scoped Routes
Route::middleware(['auth', 'branch.scope'])->group(function () {
    // POS (Kasir)
    Route::get('/pos', [\App\Http\Controllers\POSController::class, 'index'])->name('pos.index');
    Route::post('/pos', [\App\Http\Controllers\POSController::class, 'store'])->name('pos.store');

    // Production Tracking
    Route::get('/production', [\App\Http\Controllers\ProductionController::class, 'index'])->name('production.index');
    Route::post('/production/update/{id}', [\App\Http\Controllers\ProductionController::class, 'updateStatus'])->name('production.update');

    // CRM & Customers
    Route::get('/customers', function () {
        $customers = \App\Models\Customer::with('branch')->orderBy('name', 'asc')->paginate(10);
        return view('customers.index', compact('customers'));
    })->name('customers.index');

    Route::post('/customers', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $branchId = session('scoped_branch_id') ?? auth()->user()->branch_id ?? \App\Models\Branch::first()->id;

        \App\Models\Customer::create([
            'branch_id' => $branchId,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'member_code' => 'CUST-' . now()->format('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4)),
            'loyalty_tier' => 'Bronze',
            'loyalty_points' => 0,
        ]);

        return redirect()->back()->with('success', 'Pelanggan baru berhasil didaftarkan.');
    })->name('customers.store');

    // Promotions
    Route::get('/promotions', function () {
        $promotions = \App\Models\Promotion::orderBy('created_at', 'desc')->paginate(10);
        return view('promotions.index', compact('promotions'));
    })->name('promotions.index');

    // Inventory
    Route::get('/inventory', function () {
        $inventoryItems = \App\Models\InventoryItem::orderBy('name', 'asc')->paginate(10);
        return view('inventory.index', compact('inventoryItems'));
    })->name('inventory.index');

    // HR & Employees
    Route::get('/hr', function () {
        $employees = \App\Models\Employee::orderBy('name', 'asc')->paginate(10);
        return view('hr.index', compact('employees'));
    })->name('hr.index');

    // Fixed Assets
    Route::get('/assets', function () {
        $assets = \App\Models\FixedAsset::orderBy('name', 'asc')->paginate(10);
        return view('assets.index', compact('assets'));
    })->name('assets.index');

    // Finance & COA
    Route::get('/finance', function () {
        $coas = \App\Models\ChartOfAccount::orderBy('code', 'asc')->paginate(20);
        return view('finance.index', compact('coas'));
    })->name('finance.index');

    // Audit Logs
    Route::get('/audit-logs', function () {
        $logs = \App\Models\AuditLog::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('audit_logs.index', compact('logs'));
    })->name('audit-logs.index');
});

require __DIR__.'/auth.php';
