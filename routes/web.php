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

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'branch.scope'])
    ->name('dashboard');

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

    Route::put('/customers/{id}', function (\Illuminate\Http\Request $request, $id) {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone,' . $id,
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'loyalty_tier' => 'required|in:Bronze,Silver,Gold,Platinum',
            'loyalty_points' => 'required|integer|min:0',
        ]);

        $customer = \App\Models\Customer::findOrFail($id);
        $customer->update($request->only('name', 'phone', 'email', 'address', 'loyalty_tier', 'loyalty_points'));

        return redirect()->back()->with('success', 'Data pelanggan berhasil diperbarui.');
    })->name('customers.update');

    Route::delete('/customers/{id}', function ($id) {
        $customer = \App\Models\Customer::findOrFail($id);
        $customer->delete();
        return redirect()->back()->with('success', 'Pelanggan berhasil dihapus.');
    })->name('customers.destroy');

    // Promotions
    Route::get('/promotions', function () {
        $promotions = \App\Models\Promotion::orderBy('created_at', 'desc')->paginate(10);
        return view('promotions.index', compact('promotions'));
    })->name('promotions.index');

    Route::post('/promotions', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:promotions,code',
            'type' => 'required|in:percent,nominal',
            'value' => 'required|numeric|min:0',
            'min_transaction' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        \App\Models\Promotion::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'type' => $request->type,
            'value' => $request->value,
            'min_transaction' => $request->min_transaction,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Kupon promosi baru berhasil dibuat.');
    })->name('promotions.store');

    Route::delete('/promotions/{id}', function ($id) {
        $promo = \App\Models\Promotion::findOrFail($id);
        $promo->delete();
        return redirect()->back()->with('success', 'Kupon promosi berhasil dihapus.');
    })->name('promotions.destroy');

    // Inventory
    Route::get('/inventory', function () {
        $inventoryItems = \App\Models\InventoryItem::orderBy('name', 'asc')->paginate(10);
        return view('inventory.index', compact('inventoryItems'));
    })->name('inventory.index');

    Route::post('/inventory', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:inventory_items,sku',
            'category' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'min_stock' => 'required|numeric|min:0',
            'current_stock' => 'required|numeric|min:0',
        ]);

        $branchId = session('scoped_branch_id') ?? auth()->user()->branch_id ?? \App\Models\Branch::first()->id;

        \App\Models\InventoryItem::create([
            'branch_id' => $branchId,
            'name' => $request->name,
            'sku' => strtoupper($request->sku),
            'category' => $request->category,
            'unit' => $request->unit,
            'min_stock' => $request->min_stock,
            'current_stock' => $request->current_stock,
        ]);

        return redirect()->back()->with('success', 'Item inventori baru berhasil ditambahkan.');
    })->name('inventory.store');

    Route::put('/inventory/{id}/adjust', function (\Illuminate\Http\Request $request, $id) {
        $request->validate([
            'current_stock' => 'required|numeric|min:0',
        ]);

        $item = \App\Models\InventoryItem::findOrFail($id);
        $item->update(['current_stock' => $request->current_stock]);

        return redirect()->back()->with('success', 'Stok item inventori berhasil dikoreksi.');
    })->name('inventory.adjust');

    // HR & Employees
    Route::get('/hr', function () {
        $employees = \App\Models\Employee::orderBy('name', 'asc')->paginate(10);
        return view('hr.index', compact('employees'));
    })->name('hr.index');

    Route::post('/hr', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'nik' => 'required|string|unique:employees,nik',
            'position' => 'required|string|max:255',
            'base_salary' => 'required|numeric|min:0',
            'joined_at' => 'required|date',
        ]);

        $branchId = session('scoped_branch_id') ?? auth()->user()->branch_id ?? \App\Models\Branch::first()->id;

        \App\Models\Employee::create([
            'branch_id' => $branchId,
            'name' => $request->name,
            'nik' => $request->nik,
            'position' => $request->position,
            'base_salary' => $request->base_salary,
            'joined_at' => $request->joined_at,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Karyawan baru berhasil ditambahkan.');
    })->name('hr.store');

    Route::put('/hr/{id}', function (\Illuminate\Http\Request $request, $id) {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'base_salary' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        $employee = \App\Models\Employee::findOrFail($id);
        $employee->update($request->only('name', 'position', 'base_salary', 'is_active'));

        return redirect()->back()->with('success', 'Data karyawan berhasil diperbarui.');
    })->name('hr.update');

    // Fixed Assets
    Route::get('/assets', function () {
        $assets = \App\Models\FixedAsset::orderBy('name', 'asc')->paginate(10);
        return view('assets.index', compact('assets'));
    })->name('assets.index');

    Route::post('/assets', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'asset_code' => 'required|string|unique:fixed_assets,asset_code',
            'category' => 'required|string|max:255',
            'acquisition_cost' => 'required|numeric|min:0',
            'salvage_value' => 'required|numeric|min:0',
            'useful_life_months' => 'required|integer|min:1',
            'depreciation_method' => 'required|in:straight_line,double_declining',
            'acquisition_date' => 'required|date',
        ]);

        $branchId = session('scoped_branch_id') ?? auth()->user()->branch_id ?? \App\Models\Branch::first()->id;
        $coa = \App\Models\ChartOfAccount::where('type', 'asset')->first();

        \App\Models\FixedAsset::create([
            'branch_id' => $branchId,
            'account_id' => $coa?->id ?? 1,
            'asset_code' => strtoupper($request->asset_code),
            'name' => $request->name,
            'category' => $request->category,
            'acquisition_date' => $request->acquisition_date,
            'acquisition_cost' => $request->acquisition_cost,
            'salvage_value' => $request->salvage_value,
            'useful_life_months' => $request->useful_life_months,
            'depreciation_method' => $request->depreciation_method,
            'accumulated_depreciation' => 0,
            'book_value' => $request->acquisition_cost,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Aset tetap baru berhasil didaftarkan.');
    })->name('assets.store');

    Route::delete('/assets/{id}', function ($id) {
        $asset = \App\Models\FixedAsset::findOrFail($id);
        $asset->delete();
        return redirect()->back()->with('success', 'Aset tetap berhasil dihapus.');
    })->name('assets.destroy');

    // Finance & COA
    Route::get('/finance', function () {
        $coas = \App\Models\ChartOfAccount::orderBy('code', 'asc')->paginate(20);
        $allCoas = \App\Models\ChartOfAccount::orderBy('code', 'asc')->get();
        return view('finance.index', compact('coas', 'allCoas'));
    })->name('finance.index');

    Route::post('/finance', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'code' => 'required|string|unique:chart_of_accounts,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
        ]);

        $parent = $request->parent_id ? \App\Models\ChartOfAccount::find($request->parent_id) : null;
        $level = $parent ? $parent->level + 1 : 1;

        \App\Models\ChartOfAccount::create([
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type,
            'normal_balance' => $request->normal_balance,
            'parent_id' => $request->parent_id,
            'level' => $level,
            'is_active' => true,
            'is_system' => false,
        ]);

        return redirect()->back()->with('success', 'Akun COA baru berhasil dibuat.');
    })->name('finance.store');

    Route::delete('/finance/{id}', function ($id) {
        $coa = \App\Models\ChartOfAccount::findOrFail($id);
        
        if ($coa->is_system) {
            return redirect()->back()->with('error', 'Akun bawaan sistem tidak dapat dihapus.');
        }
        
        if ($coa->children()->count() > 0) {
            return redirect()->back()->with('error', 'Akun yang memiliki sub-akun tidak dapat dihapus.');
        }

        $coa->delete();
        return redirect()->back()->with('success', 'Akun COA berhasil dihapus.');
    })->name('finance.destroy');

    // Audit Logs
    Route::get('/audit-logs', function () {
        $logs = \App\Models\AuditLog::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('audit_logs.index', compact('logs'));
    })->name('audit-logs.index');

    // Procurement - PR
    Route::get('/procurement/purchase-requests', [\App\Http\Controllers\Procurement\PurchaseRequestController::class, 'index'])->name('procurement.purchase-requests.index');
    Route::post('/procurement/purchase-requests', [\App\Http\Controllers\Procurement\PurchaseRequestController::class, 'store'])->name('procurement.purchase-requests.store');
    Route::post('/procurement/purchase-requests/{id}/approve', [\App\Http\Controllers\Procurement\PurchaseRequestController::class, 'approve'])->name('procurement.purchase-requests.approve');
    Route::post('/procurement/purchase-requests/{id}/reject', [\App\Http\Controllers\Procurement\PurchaseRequestController::class, 'reject'])->name('procurement.purchase-requests.reject');
    Route::delete('/procurement/purchase-requests/{id}', [\App\Http\Controllers\Procurement\PurchaseRequestController::class, 'destroy'])->name('procurement.purchase-requests.destroy');

    // Procurement - PO
    Route::get('/procurement/purchase-orders', [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'index'])->name('procurement.purchase-orders.index');
    Route::post('/procurement/purchase-orders', [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'store'])->name('procurement.purchase-orders.store');
    Route::post('/procurement/purchase-orders/{id}/send', [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'send'])->name('procurement.purchase-orders.send');
    Route::post('/procurement/purchase-orders/{id}/confirm', [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'confirm'])->name('procurement.purchase-orders.confirm');
    Route::delete('/procurement/purchase-orders/{id}', [\App\Http\Controllers\Procurement\PurchaseOrderController::class, 'destroy'])->name('procurement.purchase-orders.destroy');

    // Procurement - GRN
    Route::get('/procurement/grns', [\App\Http\Controllers\Procurement\GoodsReceivedNoteController::class, 'index'])->name('procurement.grns.index');
    Route::post('/procurement/grns', [\App\Http\Controllers\Procurement\GoodsReceivedNoteController::class, 'store'])->name('procurement.grns.store');
    Route::post('/procurement/grns/{id}/confirm', [\App\Http\Controllers\Procurement\GoodsReceivedNoteController::class, 'confirm'])->name('procurement.grns.confirm');
    Route::delete('/procurement/grns/{id}', [\App\Http\Controllers\Procurement\GoodsReceivedNoteController::class, 'destroy'])->name('procurement.grns.destroy');

    // Finance - Ledger / Journals
    Route::get('/finance/journals', [\App\Http\Controllers\Finance\JournalController::class, 'index'])->name('finance.journals.index');
    Route::post('/finance/journals', [\App\Http\Controllers\Finance\JournalController::class, 'store'])->name('finance.journals.store');
    Route::post('/finance/journals/{id}/reverse', [\App\Http\Controllers\Finance\JournalController::class, 'reverse'])->name('finance.journals.reverse');

    // Finance - Accounting Periods
    Route::get('/finance/periods', [\App\Http\Controllers\Finance\AccountingPeriodController::class, 'index'])->name('finance.periods.index');
    Route::post('/finance/periods/{id}/close', [\App\Http\Controllers\Finance\AccountingPeriodController::class, 'close'])->name('finance.periods.close');

    // Finance - Reports
    Route::get('/finance/reports', [\App\Http\Controllers\Finance\FinancialReportController::class, 'index'])->name('finance.reports.index');

    // Super User - Switch Scoped Branch
    Route::post('/switch-branch', function (\Illuminate\Http\Request $request) {
        if (!auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin'])) {
            abort(403, 'Hanya Owner dan Administrator yang dapat beralih cabang.');
        }
        
        $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
        ]);
        
        if ($request->branch_id) {
            session(['scoped_branch_id' => $request->branch_id]);
        } else {
            session()->forget('scoped_branch_id');
        }
        
        return redirect()->back()->with('success', 'Cabang aktif berhasil diubah.');
    })->name('switch-branch');

    // Refund Module
    Route::get('/refunds', [\App\Http\Controllers\RefundController::class, 'index'])->name('refunds.index');
    Route::post('/refunds', [\App\Http\Controllers\RefundController::class, 'store'])->name('refunds.store');
    Route::post('/refunds/{id}/approve', [\App\Http\Controllers\RefundController::class, 'approve'])->name('refunds.approve');
    Route::post('/refunds/{id}/reject', [\App\Http\Controllers\RefundController::class, 'reject'])->name('refunds.reject');
});

require __DIR__.'/auth.php';
