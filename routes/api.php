<?php

use App\Http\Controllers\Api\AssetApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\ExpenseApiController;
use App\Http\Controllers\Api\FinanceApiController;
use App\Http\Controllers\Api\HrApiController;
use App\Http\Controllers\Api\InventoryApiController;
use App\Http\Controllers\Api\MasterApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\OrderTrackingController;
use App\Http\Controllers\Api\PerformanceApiController;
use App\Http\Controllers\Api\PosTabletController;
use App\Http\Controllers\Api\ProcurementApiController;
use App\Http\Controllers\Api\ProductionController;
use App\Http\Controllers\Api\PublicApiController;
use App\Http\Controllers\Api\ShiftApiController;
use App\Http\Controllers\Api\SupplierPaymentApiController;
use App\Http\Controllers\Api\UserApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public API Endpoints
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('api.login');
Route::get('/track/{orderNumber}', [OrderTrackingController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('api.track');

// Public API v1 for Company Profile & Online Orders with GPS
Route::prefix('v1')->group(function () {
    Route::get('/branches', [PublicApiController::class, 'branches'])->name('api.v1.branches');
    Route::get('/services', [PublicApiController::class, 'services'])->name('api.v1.services');
    Route::get('/track/{orderNumber?}', [PublicApiController::class, 'track'])->name('api.v1.track');
    Route::post('/track', [PublicApiController::class, 'track'])->name('api.v1.track.post');
    Route::post('/orders/online', [PublicApiController::class, 'storeOnlineOrder'])
        ->middleware('throttle:10,1')
        ->name('api.v1.orders.online');
});

// Authenticated 100% Feature-Complete RESTful API Engine (Sanctum Token + Branch Scope)
Route::middleware(['auth:sanctum', 'branch.scope'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');

    // POS Tablet API
    Route::get('/pos/services', [PosTabletController::class, 'services'])->name('api.pos.services');
    Route::get('/pos/customers', [PosTabletController::class, 'customers'])->name('api.pos.customers');
    Route::post('/pos/orders', [PosTabletController::class, 'storeOrder'])->name('api.pos.store-order');

    // Production Workshop Status API
    Route::get('/production', [ProductionController::class, 'index'])->name('api.production.index');
    Route::get('/production/{order}', [ProductionController::class, 'show'])->name('api.production.show');
    Route::patch('/production/{order}/status', [ProductionController::class, 'updateStatus'])->name('api.production.update-status');

    // RESTful API v1 Modules
    Route::prefix('v1')->group(function () {
        // 1. Dashboard & KPI Analytics
        Route::get('/dashboard/stats', [DashboardApiController::class, 'stats'])->name('api.v1.dashboard.stats');
        Route::get('/dashboard/charts', [DashboardApiController::class, 'charts'])->name('api.v1.dashboard.charts');

        // 2. Orders & Transactions
        Route::get('/orders', [OrderApiController::class, 'index'])->name('api.v1.orders.index');
        Route::get('/orders/{order}', [OrderApiController::class, 'show'])->name('api.v1.orders.show');
        Route::post('/orders/{order}/payments', [OrderApiController::class, 'storePayment'])->name('api.v1.orders.payments.store');
        Route::post('/orders/{order}/refund', [OrderApiController::class, 'refund'])->name('api.v1.orders.refund');
        Route::get('/orders/{order}/receipt-data', [OrderApiController::class, 'receiptData'])->name('api.v1.orders.receipt-data');

        // 3. CRM Customers & Loyalty
        Route::get('/customers', [CustomerApiController::class, 'index'])->name('api.v1.customers.index');
        Route::post('/customers', [CustomerApiController::class, 'store'])->name('api.v1.customers.store');
        Route::get('/customers/{customer}', [CustomerApiController::class, 'show'])->name('api.v1.customers.show');
        Route::put('/customers/{customer}', [CustomerApiController::class, 'update'])->name('api.v1.customers.update');
        Route::delete('/customers/{customer}', [CustomerApiController::class, 'destroy'])->name('api.v1.customers.destroy');
        Route::post('/customers/{customer}/adjust-points', [CustomerApiController::class, 'adjustPoints'])->name('api.v1.customers.adjust-points');

        // 4. Inventory & Stock
        Route::get('/inventory', [InventoryApiController::class, 'index'])->name('api.v1.inventory.index');
        Route::post('/inventory', [InventoryApiController::class, 'store'])->name('api.v1.inventory.store');
        Route::get('/inventory/{inventoryItem}', [InventoryApiController::class, 'show'])->name('api.v1.inventory.show');
        Route::put('/inventory/{inventoryItem}', [InventoryApiController::class, 'update'])->name('api.v1.inventory.update');
        Route::put('/inventory/{inventoryItem}/adjust', [InventoryApiController::class, 'adjust'])->name('api.v1.inventory.adjust');
        Route::delete('/inventory/{inventoryItem}', [InventoryApiController::class, 'destroy'])->name('api.v1.inventory.destroy');

        // 5. HR & Payroll
        Route::get('/hr/employees', [HrApiController::class, 'employees'])->name('api.v1.hr.employees.index');
        Route::post('/hr/employees', [HrApiController::class, 'storeEmployee'])->name('api.v1.hr.employees.store');
        Route::put('/hr/employees/{employee}', [HrApiController::class, 'updateEmployee'])->name('api.v1.hr.employees.update');
        Route::get('/hr/payrolls', [HrApiController::class, 'payrolls'])->name('api.v1.hr.payrolls.index');
        Route::post('/hr/payrolls', [HrApiController::class, 'storePayroll'])->name('api.v1.hr.payrolls.store');

        // 6. Fixed Assets & Depreciation
        Route::get('/assets', [AssetApiController::class, 'index'])->name('api.v1.assets.index');
        Route::post('/assets', [AssetApiController::class, 'store'])->name('api.v1.assets.store');
        Route::get('/assets/{fixedAsset}', [AssetApiController::class, 'show'])->name('api.v1.assets.show');
        Route::put('/assets/{fixedAsset}', [AssetApiController::class, 'update'])->name('api.v1.assets.update');
        Route::post('/assets/depreciate', [AssetApiController::class, 'depreciate'])->name('api.v1.assets.depreciate');

        // 7. Finance, COA, Expenses, & Accounting
        Route::get('/finance/coa', [FinanceApiController::class, 'coa'])->name('api.v1.finance.coa.index');
        Route::post('/finance/coa', [FinanceApiController::class, 'storeCoa'])->name('api.v1.finance.coa.store');
        Route::get('/finance/journals', [FinanceApiController::class, 'journals'])->name('api.v1.finance.journals.index');
        Route::post('/finance/journals', [FinanceApiController::class, 'storeJournal'])->name('api.v1.finance.journals.store');
        Route::get('/finance/expenses', [ExpenseApiController::class, 'index'])->name('api.v1.finance.expenses.index');
        Route::post('/finance/expenses', [ExpenseApiController::class, 'store'])->name('api.v1.finance.expenses.store');
        Route::get('/finance/expenses/{expense}', [ExpenseApiController::class, 'show'])->name('api.v1.finance.expenses.show');
        Route::get('/finance/supplier-payments', [SupplierPaymentApiController::class, 'index'])->name('api.v1.finance.supplier-payments.index');
        Route::post('/finance/supplier-payments', [SupplierPaymentApiController::class, 'store'])->name('api.v1.finance.supplier-payments.store');
        Route::get('/finance/reports/income-statement', [FinanceApiController::class, 'incomeStatement'])->name('api.v1.finance.reports.income-statement');
        Route::get('/finance/reports/balance-sheet', [FinanceApiController::class, 'balanceSheet'])->name('api.v1.finance.reports.balance-sheet');
        Route::get('/finance/reports/trial-balance', [FinanceApiController::class, 'trialBalance'])->name('api.v1.finance.reports.trial-balance');
        Route::get('/finance/accounting-periods', [FinanceApiController::class, 'accountingPeriods'])->name('api.v1.finance.accounting-periods.index');
        Route::post('/finance/accounting-periods/{period}/close', [FinanceApiController::class, 'closeAccountingPeriod'])->name('api.v1.finance.accounting-periods.close');

        // 8. Procurement & Suppliers
        Route::get('/procurement/suppliers', [ProcurementApiController::class, 'suppliers'])->name('api.v1.procurement.suppliers.index');
        Route::post('/procurement/suppliers', [ProcurementApiController::class, 'storeSupplier'])->name('api.v1.procurement.suppliers.store');
        Route::get('/procurement/purchase-requests', [ProcurementApiController::class, 'purchaseRequests'])->name('api.v1.procurement.purchase-requests.index');
        Route::get('/procurement/purchase-requests/{purchaseRequest}', [ProcurementApiController::class, 'showPr'])->name('api.v1.procurement.purchase-requests.show');
        Route::post('/procurement/purchase-requests', [ProcurementApiController::class, 'storePurchaseRequest'])->name('api.v1.procurement.purchase-requests.store');
        Route::put('/procurement/purchase-requests/{purchaseRequest}/approve', [ProcurementApiController::class, 'approvePr'])->name('api.v1.procurement.purchase-requests.approve');
        Route::get('/procurement/purchase-orders', [ProcurementApiController::class, 'purchaseOrders'])->name('api.v1.procurement.purchase-orders.index');
        Route::get('/procurement/purchase-orders/{purchaseOrder}', [ProcurementApiController::class, 'showPo'])->name('api.v1.procurement.purchase-orders.show');
        Route::post('/procurement/purchase-orders', [ProcurementApiController::class, 'storePurchaseOrder'])->name('api.v1.procurement.purchase-orders.store');
        Route::get('/procurement/grns', [ProcurementApiController::class, 'grns'])->name('api.v1.procurement.grns.index');
        Route::post('/procurement/grns', [ProcurementApiController::class, 'storeGrn'])->name('api.v1.procurement.grns.store');

        // 9. Cashier Shifts & Settlement
        Route::get('/shifts', [ShiftApiController::class, 'index'])->name('api.v1.shifts.index');
        Route::post('/shifts/open', [ShiftApiController::class, 'openShift'])->name('api.v1.shifts.open');
        Route::post('/shifts/close', [ShiftApiController::class, 'closeShift'])->name('api.v1.shifts.close');

        // 10. Master Data (Services & Branches)
        Route::get('/master/services/{service}', [MasterApiController::class, 'showService'])->name('api.v1.master.services.show');
        Route::post('/master/services', [MasterApiController::class, 'storeService'])->name('api.v1.master.services.store');
        Route::put('/master/services/{service}', [MasterApiController::class, 'updateService'])->name('api.v1.master.services.update');
        Route::delete('/master/services/{service}', [MasterApiController::class, 'destroyService'])->name('api.v1.master.services.destroy');
        Route::get('/master/branches/{branch}', [MasterApiController::class, 'showBranch'])->name('api.v1.master.branches.show');
        Route::post('/master/branches', [MasterApiController::class, 'storeBranch'])->name('api.v1.master.branches.store');
        Route::put('/master/branches/{branch}', [MasterApiController::class, 'updateBranch'])->name('api.v1.master.branches.update');
        Route::delete('/master/branches/{branch}', [MasterApiController::class, 'destroyBranch'])->name('api.v1.master.branches.destroy');

        // 11. User Management & RBAC
        Route::get('/users', [UserApiController::class, 'index'])->name('api.v1.users.index');
        Route::post('/users', [UserApiController::class, 'store'])->name('api.v1.users.store');
        Route::get('/users/{user}', [UserApiController::class, 'show'])->name('api.v1.users.show');
        Route::put('/users/{user}', [UserApiController::class, 'update'])->name('api.v1.users.update');
        Route::get('/roles', [UserApiController::class, 'roles'])->name('api.v1.roles.index');

        // 12. Performance KPIs & Audit Logs
        Route::get('/performance/cashiers', [PerformanceApiController::class, 'cashiers'])->name('api.v1.performance.cashiers');
        Route::get('/performance/branches', [PerformanceApiController::class, 'branches'])->name('api.v1.performance.branches');
        Route::get('/audit-logs', [PerformanceApiController::class, 'auditLogs'])->name('api.v1.audit-logs');
    });
});
