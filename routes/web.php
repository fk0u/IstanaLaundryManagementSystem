<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Finance\AccountingPeriodController;
use App\Http\Controllers\Finance\FinancialReportController;
use App\Http\Controllers\Finance\JournalController;
use App\Http\Controllers\HR\HRController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\Procurement\GoodsReceivedNoteController;
use App\Http\Controllers\Procurement\PurchaseOrderController;
use App\Http\Controllers\Procurement\PurchaseRequestController;
use App\Http\Controllers\Procurement\SupplierController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\ServiceController;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\FixedAsset;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/track', function (Request $request) {
    $orderNumber = $request->input('order_number');

    if (! $orderNumber) {
        return redirect('/')->with('error', 'Masukkan nomor nota terlebih dahulu.');
    }

    // Validate order number format (basic alphanumeric check)
    if (! preg_match('/^[A-Z0-9-]+$/', $orderNumber)) {
        return redirect('/')->with('error', 'Format nomor nota tidak valid.');
    }

    $order = Order::with(['customer', 'branch', 'items.service', 'productionStatusLogs.updater'])
        ->where('order_number', $orderNumber)
        ->first();

    return view('track', compact('order', 'orderNumber'));
})->middleware('throttle:30,1')->name('track');

Route::get('/guide', function () {
    return view('guide');
})->middleware(['auth'])->name('guide');

Route::get('/dashboard', [DashboardController::class, 'index'])
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
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::post('/pos', [POSController::class, 'store'])->name('pos.store');
    Route::post('/pos/customers', [POSController::class, 'storeCustomer'])->name('pos.customers.store');

    // Invoices & Billing
    Route::get('/invoices/{order}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{order}/receipt', [InvoiceController::class, 'receipt'])->name('invoices.receipt');
    Route::get('/invoices/{order}/whatsapp', [InvoiceController::class, 'sendWhatsApp'])->name('invoices.whatsapp');
    Route::get('/invoices/{order}/ready-whatsapp', [InvoiceController::class, 'sendReadyWhatsApp'])->name('invoices.ready-whatsapp');

    // Orders — list seluruh transaksi
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

    // Production Tracking
    Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
    Route::post('/production/update/{id}', [ProductionController::class, 'updateStatus'])->name('production.update');

    // Performance Monitoring & Export
    Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index');
    Route::get('/performance/export', [PerformanceController::class, 'exportCsv'])->name('performance.export');
    Route::get('/performance/export/pdf', [PerformanceController::class, 'exportPdf'])->name('performance.export.pdf');
    Route::get('/performance/export/xlsx', [PerformanceController::class, 'exportExcel'])->name('performance.export.xlsx');

    // CRM & Customers — Global & Export
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/export', [CustomerController::class, 'exportCsv'])->name('customers.export');
    Route::get('/customers/export/pdf', [CustomerController::class, 'exportPdf'])->name('customers.export.pdf');
    Route::get('/customers/export/xlsx', [CustomerController::class, 'exportExcel'])->name('customers.export.xlsx');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Promotions
    Route::middleware('role:Branch_Admin|Owner|Super_Admin|Developer')->group(function () {
        Route::get('/promotions', function () {
            $promotions = Promotion::orderBy('created_at', 'desc')->paginate(10);

            return view('promotions.index', compact('promotions'));
        })->name('promotions.index');

        Route::post('/promotions', function (Request $request) {
            $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|unique:promotions,code',
                'type' => 'required|in:percent,nominal',
                'value' => 'required|numeric|min:0',
                'min_transaction' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            Promotion::create([
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
            $promo = Promotion::findOrFail($id);
            $promo->delete();

            return redirect()->back()->with('success', 'Kupon promosi berhasil dihapus.');
        })->name('promotions.destroy');
    });

    // ===== MASTER DATA: SERVICES =====
    Route::middleware('role:Developer|Owner|Super_Admin')->prefix('services')->name('services.')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('index');
        Route::post('/', [ServiceController::class, 'store'])->name('store');
        Route::match(['put', 'patch'], '/{service}', [ServiceController::class, 'update'])->name('update');
        Route::patch('/{service}/toggle-active', [ServiceController::class, 'toggleActive'])->name('toggle-active');
    });

    // Inventory
    Route::get('/inventory', function () {
        $inventoryItems = InventoryItem::orderBy('name', 'asc')->paginate(10);

        return view('inventory.index', compact('inventoryItems'));
    })->name('inventory.index');

    Route::middleware('role:Branch_Admin|Owner|Super_Admin|Developer')->group(function () {
        Route::post('/inventory', function (Request $request) {
            $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'required|string|unique:inventory_items,sku',
                'category' => 'required|string|max:255',
                'unit' => 'required|string|max:50',
                'min_stock' => 'required|numeric|min:0',
                'current_stock' => 'required|numeric|min:0',
            ]);

            $branchId = session('scoped_branch_id') ?? auth()->user()->branch_id ?? Branch::first()->id;

            InventoryItem::create([
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

        Route::put('/inventory/{id}/adjust', function (Request $request, $id) {
            $request->validate([
                'current_stock' => 'required|numeric|min:0',
            ]);

            $item = InventoryItem::findOrFail($id);
            $item->update(['current_stock' => $request->current_stock]);

            return redirect()->back()->with('success', 'Stok item inventori berhasil dikoreksi.');
        })->name('inventory.adjust');
    });

    // HR & Payroll
    Route::middleware('role:Finance|Owner|Super_Admin|Developer')->group(function () {
        Route::get('/hr', [HRController::class, 'index'])->name('hr.index');
        Route::post('/hr/employees', [HRController::class, 'storeEmployee'])->name('hr.employees.store');
        Route::put('/hr/employees/{employee}', [HRController::class, 'updateEmployee'])->name('hr.employees.update');
        Route::post('/hr/payrolls', [HRController::class, 'storePayroll'])->name('hr.payrolls.store');
        Route::post('/hr/payrolls/{payroll}/finalize', [HRController::class, 'finalizePayroll'])->name('hr.payrolls.finalize');
        Route::get('/hr/payrolls/{payroll}', [HRController::class, 'showPayroll'])->name('hr.payrolls.show');
        Route::get('/hr/payslip/{item}', [HRController::class, 'showPayslip'])->name('hr.payslip');
        Route::post('/hr/employees/{employee}/create-account', [HRController::class, 'createAccountForEmployee'])->name('hr.employees.create-account');
        Route::post('/hr/employees/{employee}/link-account', [HRController::class, 'linkAccountForEmployee'])->name('hr.employees.link-account');
        Route::post('/hr/employees/{employee}/reset-password', [HRController::class, 'resetEmployeePassword'])->name('hr.employees.reset-password');
        Route::post('/hr/attendances', [HRController::class, 'storeAttendance'])->name('hr.attendances.store');
        Route::put('/hr/payroll-item/{item}', [HRController::class, 'updatePayrollItem'])->name('hr.payroll-item.update');
        Route::delete('/hr/payroll/{payroll}', [HRController::class, 'destroyPayroll'])->name('hr.payroll.destroy');

        Route::post('/hr', function (Request $request) {
            $request->validate([
                'name' => 'required|string|max:255',
                'nik' => 'required|string|unique:employees,nik',
                'position' => 'required|string|max:255',
                'base_salary' => 'required|numeric|min:0',
                'joined_at' => 'required|date',
            ]);

            $branchId = session('scoped_branch_id') ?? auth()->user()->branch_id ?? Branch::first()->id;

            Employee::create([
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

        Route::put('/hr/{id}', function (Request $request, $id) {
            $request->validate([
                'name' => 'required|string|max:255',
                'position' => 'required|string|max:255',
                'base_salary' => 'required|numeric|min:0',
                'is_active' => 'required|boolean',
            ]);

            $employee = Employee::findOrFail($id);
            $employee->update($request->only('name', 'position', 'base_salary', 'is_active'));

            return redirect()->back()->with('success', 'Data karyawan berhasil diperbarui.');
        })->name('hr.update');
    });

    // Fixed Assets
    Route::middleware('role:Finance|Owner|Super_Admin|Developer')->group(function () {
        Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
        Route::get('/assets/export', [AssetController::class, 'exportCsv'])->name('assets.export');
        Route::get('/assets/export/pdf', [AssetController::class, 'exportPdf'])->name('assets.export.pdf');
        Route::get('/assets/export/xlsx', [AssetController::class, 'exportExcel'])->name('assets.export.xlsx');
        Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
        Route::post('/assets/{asset}/maintenance', [AssetController::class, 'updateMaintenance'])->name('assets.maintenance.update');
        Route::get('/assets/{asset}', [AssetController::class, 'show'])->name('assets.show');

        Route::post('/assets', function (Request $request) {
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

            $branchId = session('scoped_branch_id') ?? auth()->user()->branch_id ?? Branch::first()->id;
            $coa = ChartOfAccount::where('type', 'asset')->first();

            FixedAsset::create([
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
            $asset = FixedAsset::findOrFail($id);
            $asset->delete();

            return redirect()->back()->with('success', 'Aset tetap berhasil dihapus.');
        })->name('assets.destroy');
    });

    // Finance & COA
    Route::middleware('role:Finance|Owner|Super_Admin|Developer')->group(function () {
        Route::get('/finance', function () {
            $coas = ChartOfAccount::orderBy('code', 'asc')->paginate(20);
            $allCoas = ChartOfAccount::orderBy('code', 'asc')->get();

            return view('finance.index', compact('coas', 'allCoas'));
        })->name('finance.index');

        Route::post('/finance', function (Request $request) {
            $request->validate([
                'code' => 'required|string|unique:chart_of_accounts,code',
                'name' => 'required|string|max:255',
                'type' => 'required|in:asset,liability,equity,revenue,expense',
                'normal_balance' => 'required|in:debit,credit',
                'parent_id' => 'nullable|exists:chart_of_accounts,id',
            ]);

            $parent = $request->parent_id ? ChartOfAccount::find($request->parent_id) : null;
            $level = $parent ? $parent->level + 1 : 1;

            ChartOfAccount::create([
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
            $coa = ChartOfAccount::findOrFail($id);
            if ($coa->journalLines()->exists()) {
                return redirect()->back()->with('error', 'Akun COA tidak dapat dihapus karena sudah memiliki mutasi jurnal.');
            }
            $coa->delete();

            return redirect()->back()->with('success', 'Akun COA berhasil dihapus.');
        })->name('finance.destroy');

        // Closing Checklist Periode Akuntansi
        Route::get('/finance/closing-checklist', function () {
            $currentMonth = now()->format('Y-m');
            $openPeriods = \App\Models\AccountingPeriod::where('is_closed', false)->get();
            $unpaidOrdersCount = \App\Models\Order::where('payment_status', '!=', 'paid')->whereNotIn('production_status', ['BATAL'])->count();
            $unpaidOrdersAmount = \App\Models\Order::where('payment_status', '!=', 'paid')->whereNotIn('production_status', ['BATAL'])->sum(\Illuminate\Support\Facades\DB::raw('total - paid_amount'));

            return view('finance.closing_checklist', compact('openPeriods', 'unpaidOrdersCount', 'unpaidOrdersAmount', 'currentMonth'));
        })->name('finance.closing-checklist');

        // Export Laporan CSV/Excel
        Route::get('/finance/reports/export', function (Request $request) {
            $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());
            $branchId = session('scoped_branch_id') ?? auth()->user()->branch_id;

            $reportService = app(\App\Services\Finance\FinancialReportService::class);
            $incomeStatement = $reportService->generateIncomeStatement($startDate, $endDate, $branchId);

            $filename = "Laporan_Keuangan_{$startDate}_sd_{$endDate}.csv";
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($incomeStatement, $startDate, $endDate) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['LAPORAN LABA RUGI - ISTANA LAUNDRY ERP']);
                fputcsv($file, ["Periode: {$startDate} s/d {$endDate}"]);
                fputcsv($file, []);
                fputcsv($file, ['Komponen Akun', 'Kode', 'Nilai (Rp)']);

                fputcsv($file, ['PENDAPATAN OPERASIONAL', '', '']);
                foreach ($incomeStatement['revenues']['accounts'] as $acc) {
                    fputcsv($file, [$acc['name'], $acc['code'], number_format($acc['balance'], 2, '.', '')]);
                }
                fputcsv($file, ['TOTAL PENDAPATAN', '', number_format($incomeStatement['revenues']['total'], 2, '.', '')]);
                fputcsv($file, []);

                fputcsv($file, ['BEBAN OPERASIONAL', '', '']);
                foreach ($incomeStatement['expenses']['accounts'] as $acc) {
                    fputcsv($file, [$acc['name'], $acc['code'], number_format($acc['balance'], 2, '.', '')]);
                }
                fputcsv($file, ['TOTAL BEBAN', '', number_format($incomeStatement['expenses']['total'], 2, '.', '')]);
                fputcsv($file, []);

                fputcsv($file, ['LABA (RUGI) BERSIH', '', number_format($incomeStatement['net_income'], 2, '.', '')]);
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        })->name('finance.reports.export');
    });

    // User Management (Admin Only)
    Route::middleware('role:Super_Admin|Owner|Developer')->group(function () {
        Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/reset-password', [\App\Http\Controllers\UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');

        // Branch & Scope Management
        Route::get('/branches', [\App\Http\Controllers\BranchController::class, 'index'])->name('branches.index');
        Route::post('/branches', [\App\Http\Controllers\BranchController::class, 'store'])->name('branches.store');
        Route::put('/branches/{branch}', [\App\Http\Controllers\BranchController::class, 'update'])->name('branches.update');
        Route::post('/branches/{branch}/toggle-active', [\App\Http\Controllers\BranchController::class, 'toggleActive'])->name('branches.toggle-active');
        Route::delete('/branches/{branch}', [\App\Http\Controllers\BranchController::class, 'destroy'])->name('branches.destroy');
    });

    // Audit Logs
    Route::middleware('role:Super_Admin|Developer')->get('/audit-logs', function () {
        $logs = AuditLog::with('user')->orderBy('created_at', 'desc')->paginate(20);

        return view('audit_logs.index', compact('logs'));
    })->name('audit-logs.index');

    // Procurement - Supplier
    Route::middleware('role:Branch_Admin|Owner|Super_Admin|Developer')->group(function () {
        Route::get('/procurement/suppliers', [SupplierController::class, 'index'])->name('procurement.suppliers.index');
        Route::post('/procurement/suppliers', [SupplierController::class, 'store'])->name('procurement.suppliers.store');
        Route::put('/procurement/suppliers/{id}', [SupplierController::class, 'update'])->name('procurement.suppliers.update');
        Route::delete('/procurement/suppliers/{id}', [SupplierController::class, 'destroy'])->name('procurement.suppliers.destroy');

        // Procurement - PR
        Route::get('/procurement/purchase-requests', [PurchaseRequestController::class, 'index'])->name('procurement.purchase-requests.index');
        Route::post('/procurement/purchase-requests', [PurchaseRequestController::class, 'store'])->name('procurement.purchase-requests.store');
        Route::post('/procurement/purchase-requests/{id}/approve', [PurchaseRequestController::class, 'approve'])->name('procurement.purchase-requests.approve');
        Route::post('/procurement/purchase-requests/{id}/reject', [PurchaseRequestController::class, 'reject'])->name('procurement.purchase-requests.reject');
        Route::delete('/procurement/purchase-requests/{id}', [PurchaseRequestController::class, 'destroy'])->name('procurement.purchase-requests.destroy');

        // Procurement - PO
        Route::get('/procurement/purchase-orders', [PurchaseOrderController::class, 'index'])->name('procurement.purchase-orders.index');
        Route::post('/procurement/purchase-orders', [PurchaseOrderController::class, 'store'])->name('procurement.purchase-orders.store');
        Route::post('/procurement/purchase-orders/{id}/send', [PurchaseOrderController::class, 'send'])->name('procurement.purchase-orders.send');
        Route::post('/procurement/purchase-orders/{id}/confirm', [PurchaseOrderController::class, 'confirm'])->name('procurement.purchase-orders.confirm');
        Route::delete('/procurement/purchase-orders/{id}', [PurchaseOrderController::class, 'destroy'])->name('procurement.purchase-orders.destroy');

        // Procurement - GRN
        Route::get('/procurement/grns', [GoodsReceivedNoteController::class, 'index'])->name('procurement.grns.index');
        Route::post('/procurement/grns', [GoodsReceivedNoteController::class, 'store'])->name('procurement.grns.store');
        Route::post('/procurement/grns/{id}/confirm', [GoodsReceivedNoteController::class, 'confirm'])->name('procurement.grns.confirm');
        Route::delete('/procurement/grns/{id}', [GoodsReceivedNoteController::class, 'destroy'])->name('procurement.grns.destroy');
    });

    // Finance - Ledger / Journals
    Route::middleware('role:Finance|Owner|Super_Admin|Developer')->group(function () {
        Route::get('/finance/journals', [JournalController::class, 'index'])->name('finance.journals.index');
        Route::post('/finance/journals', [JournalController::class, 'store'])->name('finance.journals.store');
        Route::post('/finance/journals/{id}/reverse', [JournalController::class, 'reverse'])->name('finance.journals.reverse');

        // Finance - Accounting Periods
        Route::get('/finance/periods', [AccountingPeriodController::class, 'index'])->name('finance.periods.index');
        Route::post('/finance/periods/{id}/close', [AccountingPeriodController::class, 'close'])->name('finance.periods.close');

        // Finance - Reports & Exports
        Route::get('/finance/reports', [FinancialReportController::class, 'index'])->name('finance.reports.index');
        Route::get('/finance/reports/account-ledger', [FinancialReportController::class, 'accountLedger'])->name('finance.reports.account-ledger');
        Route::get('/finance/reports/excel', [FinancialReportController::class, 'exportExcel'])->name('finance.reports.excel');
        Route::get('/finance/reports/pdf', [FinancialReportController::class, 'exportPdf'])->name('finance.reports.pdf');
        Route::get('/finance/reports/powerbi-pdf', [FinancialReportController::class, 'exportPowerBiPdf'])->name('finance.reports.powerbi-pdf');
    });

    // Super User - Switch Scoped Branch
    Route::post('/switch-branch', function (Request $request) {
        if (! auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin'])) {
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
    Route::middleware('role:Cashier|Branch_Admin|Finance|Owner|Super_Admin|Developer')->group(function () {
        Route::get('/refunds', [RefundController::class, 'index'])->name('refunds.index');
        Route::post('/refunds', [RefundController::class, 'store'])->name('refunds.store');
        Route::post('/refunds/{id}/approve', [RefundController::class, 'approve'])->name('refunds.approve');
        Route::post('/refunds/{id}/reject', [RefundController::class, 'reject'])->name('refunds.reject');
    });
});

require __DIR__.'/auth.php';
