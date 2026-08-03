<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductionStatusLog;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerformanceController extends Controller
{
    /**
     * Helper: build base Order query (respects branch scope or global).
     */
    private function baseOrderQuery(?int $branchId)
    {
        return $branchId ? Order::where('branch_id', $branchId) : Order::withoutGlobalScopes();
    }

    /**
     * Helper: build base Refund query (respects branch scope or global).
     */
    private function baseRefundQuery(?int $branchId)
    {
        return $branchId ? Refund::withoutGlobalScopes()->where('branch_id', $branchId)
                         : Refund::withoutGlobalScopes();
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id') ?: null;
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        // Date filter: default current month
        $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->query('date_to', now()->toDateString());

        $branches = Branch::orderBy('name')->get();

        /* =============================================
           1. CASHIER LEADERBOARD
           ============================================= */
        $cashierRoles = ['Cashier', 'Branch_Admin', 'Owner', 'Super_Admin', 'Developer'];

        $cashiers = User::role($cashierRoles)
            ->withCount(['orders as total_orders' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                $branchId ? $q->where('branch_id', $branchId) : $q->withoutGlobalScopes();
                $q->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }])
            ->withSum(['orders as total_revenue' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                $branchId ? $q->where('branch_id', $branchId) : $q->withoutGlobalScopes();
                $q->where('payment_status', 'paid')
                  ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }], 'total')
            ->withSum(['orders as total_pending_revenue' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                $branchId ? $q->where('branch_id', $branchId) : $q->withoutGlobalScopes();
                $q->whereIn('payment_status', ['pending', 'partial'])
                  ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }], 'total')
            ->withCount(['orders as total_discount_orders' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                $branchId ? $q->where('branch_id', $branchId) : $q->withoutGlobalScopes();
                $q->where('discount_amount', '>', 0)
                  ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }])
            ->get()
            ->filter(fn ($u) => $u->total_orders > 0)
            ->sortByDesc('total_revenue')
            ->values();

        /* =============================================
           2. DAILY TRANSACTION BREAKDOWN PER CASHIER
           ============================================= */
        $dailyOrdersQuery = $this->baseOrderQuery($branchId);
        $cashierDailyBreakdown = $dailyOrdersQuery
            ->select(
                DB::raw('DATE(created_at) as date'),
                'cashier_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(CASE WHEN payment_status = "paid" THEN total ELSE 0 END) as paid_revenue'),
                DB::raw('SUM(CASE WHEN payment_status IN ("pending","partial") THEN total ELSE 0 END) as pending_revenue'),
                DB::raw('SUM(discount_amount) as total_discount'),
            )
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->groupBy(DB::raw('DATE(created_at)'), 'cashier_id')
            ->with('cashier:id,name')
            ->orderByDesc('date')
            ->get();

        /* =============================================
           3. REVENUE BY DAY (Time-Series for Line Chart)
           ============================================= */
        $revenueByDay = $this->baseOrderQuery($branchId)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN payment_status = "paid" THEN total ELSE 0 END) as paid_revenue'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total) as gross_revenue'),
            )
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        /* =============================================
           4. HOURLY HEATMAP
           ============================================= */
        $hourlyHeatmap = $this->baseOrderQuery($branchId)
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total) as total_revenue'),
            )
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        // Fill missing hours with zeros for chart completeness
        $hourlyData = [];
        for ($h = 0; $h <= 23; $h++) {
            $hourlyData[$h] = [
                'hour' => $h,
                'total_orders' => $hourlyHeatmap->get($h)?->total_orders ?? 0,
                'total_revenue' => $hourlyHeatmap->get($h)?->total_revenue ?? 0,
            ];
        }

        /* =============================================
           5. PRODUCTION PIPELINE (per-stage count)
           ============================================= */
        $stages = ['TERIMA', 'PILAH', 'CUCI', 'KERING', 'LIPAT', 'CEK', 'SIAP', 'DIAMBIL'];

        $pipelineRaw = $this->baseOrderQuery($branchId)
            ->select('production_status', DB::raw('COUNT(*) as cnt'))
            ->whereIn('production_status', $stages)
            ->groupBy('production_status')
            ->get()
            ->keyBy('production_status');

        $productionPipeline = collect($stages)->map(fn ($s) => [
            'stage' => $s,
            'count' => $pipelineRaw->get($s)?->cnt ?? 0,
        ]);

        // Active orders (not yet DIAMBIL)
        $totalActiveOrders = $this->baseOrderQuery($branchId)
            ->whereNotIn('production_status', ['DIAMBIL'])
            ->count();

        // Overdue orders
        $overdueOrders = $this->baseOrderQuery($branchId)
            ->whereNotIn('production_status', ['DIAMBIL'])
            ->where('estimated_done_at', '<', now())
            ->count();

        $overdueRate = $totalActiveOrders > 0 ? round(($overdueOrders / $totalActiveOrders) * 100, 1) : 0;

        /* =============================================
           6. CHANNEL BREAKDOWN (outlet vs pickup_delivery)
           ============================================= */
        $channelRaw = $this->baseOrderQuery($branchId)
            ->select(
                'order_type',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(CASE WHEN payment_status = "paid" THEN total ELSE 0 END) as paid_revenue'),
            )
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->groupBy('order_type')
            ->get()
            ->keyBy('order_type');

        $channelBreakdown = [
            'outlet' => [
                'count'   => $channelRaw->get('outlet')?->total_orders ?? 0,
                'revenue' => $channelRaw->get('outlet')?->paid_revenue ?? 0,
            ],
            'pickup_delivery' => [
                'count'   => $channelRaw->get('pickup_delivery')?->total_orders ?? 0,
                'revenue' => $channelRaw->get('pickup_delivery')?->paid_revenue ?? 0,
            ],
        ];

        /* =============================================
           7. TOP SERVICES
           ============================================= */
        $baseItemQuery = $branchId
            ? OrderItem::whereHas('order', fn ($q) => $q->where('branch_id', $branchId)
                ->whereBetween(DB::raw('DATE(orders.created_at)'), [$dateFrom, $dateTo]))
            : OrderItem::whereHas('order', fn ($q) => $q->withoutGlobalScopes()
                ->whereBetween(DB::raw('DATE(orders.created_at)'), [$dateFrom, $dateTo]));

        $topServices = $baseItemQuery
            ->select(
                'service_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(subtotal) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_id) as total_orders'),
            )
            ->with('service:id,name,type')
            ->groupBy('service_id')
            ->orderByDesc('total_revenue')
            ->take(10)
            ->get();

        /* =============================================
           8. REFUND STATISTICS
           ============================================= */
        $refundQuery = $this->baseRefundQuery($branchId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);

        $totalRefunds   = (clone $refundQuery)->count();
        $totalRefundAmt = (clone $refundQuery)->where('status', 'approved')->sum('amount');

        $periodPaidOrders = $this->baseOrderQuery($branchId)
            ->where('payment_status', 'paid')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->count();

        $refundRate = $periodPaidOrders > 0 ? round(($totalRefunds / $periodPaidOrders) * 100, 2) : 0;

        $refundStats = [
            'total_requests'  => $totalRefunds,
            'approved_amount' => $totalRefundAmt,
            'refund_rate'     => $refundRate,
        ];

        /* =============================================
           9. CUSTOMER RETENTION (New vs Returning)
           ============================================= */
        $periodCustomerIds = $this->baseOrderQuery($branchId)
            ->whereNotNull('customer_id')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->pluck('customer_id')
            ->unique();

        $returningCustomers = 0;
        $newCustomers       = 0;

        if ($periodCustomerIds->isNotEmpty()) {
            // Customers who had orders BEFORE dateFrom
            $returningIds = $this->baseOrderQuery($branchId)
                ->whereIn('customer_id', $periodCustomerIds)
                ->where(DB::raw('DATE(created_at)'), '<', $dateFrom)
                ->pluck('customer_id')
                ->unique();

            $returningCustomers = $returningIds->count();
            $newCustomers       = $periodCustomerIds->count() - $returningCustomers;
        }

        $totalCustomers = $periodCustomerIds->count();
        $retentionRate  = $totalCustomers > 0 ? round(($returningCustomers / $totalCustomers) * 100, 1) : 0;

        $customerRetention = [
            'new_customers'       => $newCustomers,
            'returning_customers' => $returningCustomers,
            'total_customers'     => $totalCustomers,
            'retention_rate'      => $retentionRate,
        ];

        /* =============================================
           10. PAYMENT METHOD BREAKDOWN
           ============================================= */
        $paymentBreakdown = $this->baseOrderQuery($branchId)
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(CASE WHEN payment_status = "paid" THEN total ELSE 0 END) as paid_revenue'),
            )
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->groupBy('payment_method')
            ->orderByDesc('total_orders')
            ->get();

        /* =============================================
           11. WORKSHOP STAFF PRODUCTIVITY
           ============================================= */
        $staffProductivity = ProductionStatusLog::query()
            ->join('users', 'production_status_logs.updated_by', '=', 'users.id')
            ->join('orders', 'production_status_logs.order_id', '=', 'orders.id')
            ->when($branchId, fn ($q) => $q->where('orders.branch_id', $branchId))
            ->whereBetween(DB::raw('DATE(production_status_logs.created_at)'), [$dateFrom, $dateTo])
            ->select(
                'users.id as user_id',
                'users.name as staff_name',
                DB::raw('COUNT(production_status_logs.id) as total_actions'),
                DB::raw("SUM(CASE WHEN production_status_logs.status = 'SIAP' THEN 1 ELSE 0 END) as completed_orders"),
                DB::raw("SUM(CASE WHEN production_status_logs.status = 'DIAMBIL' THEN 1 ELSE 0 END) as picked_up_orders"),
                DB::raw("SUM(CASE WHEN production_status_logs.status = 'CUCI' THEN 1 ELSE 0 END) as cuci_count"),
                DB::raw("SUM(CASE WHEN production_status_logs.status = 'KERING' THEN 1 ELSE 0 END) as kering_count"),
                DB::raw("SUM(CASE WHEN production_status_logs.status = 'LIPAT' THEN 1 ELSE 0 END) as lipat_count"),
                DB::raw("COUNT(DISTINCT production_status_logs.order_id) as unique_orders"),
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_actions')
            ->take(20)
            ->get();

        /* =============================================
           12. PERIOD SUMMARY STATS
           ============================================= */
        $periodRevenue = $this->baseOrderQuery($branchId)
            ->where('payment_status', 'paid')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->sum('total');

        $periodOrders = $this->baseOrderQuery($branchId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->count();

        $periodAvgOrder   = $periodOrders > 0 ? $periodRevenue / $periodOrders : 0;
        $periodTotalDiscount = $this->baseOrderQuery($branchId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])
            ->sum('discount_amount');

        return view('performance.index', compact(
            // Meta
            'branches', 'branchId', 'dateFrom', 'dateTo', 'isGlobalUser',
            // Section 1: Cashier
            'cashiers', 'cashierDailyBreakdown',
            // Section 2: Revenue Trends
            'revenueByDay', 'hourlyData',
            // Section 3: Pipeline
            'productionPipeline', 'totalActiveOrders', 'overdueOrders', 'overdueRate',
            // Section 4: Analytics
            'channelBreakdown', 'topServices', 'refundStats', 'customerRetention', 'paymentBreakdown',
            // Section 5: Workshop
            'staffProductivity',
            // Section 6: Summary KPIs
            'periodRevenue', 'periodOrders', 'periodAvgOrder', 'periodTotalDiscount',
        ));
    }

    public function exportCsv(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id') ?: null;
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->query('date_to', now()->toDateString());
        $branchName = $branchId ? (Branch::find($branchId)?->name ?? 'Cabang Unknown') : 'Seluruh Cabang';

        $fileName = 'laporan-kinerja-' . now()->format('Ymd-His') . '.csv';

        // Gather data (re-use same logic)
        $cashiers = User::role(['Cashier', 'Branch_Admin', 'Owner', 'Super_Admin', 'Developer'])
            ->withCount(['orders as total_orders' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                $branchId ? $q->where('branch_id', $branchId) : $q->withoutGlobalScopes();
                $q->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }])
            ->withSum(['orders as total_revenue' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                $branchId ? $q->where('branch_id', $branchId) : $q->withoutGlobalScopes();
                $q->where('payment_status', 'paid')
                  ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }], 'total')
            ->withSum(['orders as total_pending_revenue' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                $branchId ? $q->where('branch_id', $branchId) : $q->withoutGlobalScopes();
                $q->whereIn('payment_status', ['pending', 'partial'])
                  ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }], 'total')
            ->get()
            ->filter(fn ($u) => $u->total_orders > 0)
            ->sortByDesc('total_revenue');

        $staffProductivity = ProductionStatusLog::query()
            ->join('users', 'production_status_logs.updated_by', '=', 'users.id')
            ->join('orders', 'production_status_logs.order_id', '=', 'orders.id')
            ->when($branchId, fn ($q) => $q->where('orders.branch_id', $branchId))
            ->whereBetween(DB::raw('DATE(production_status_logs.created_at)'), [$dateFrom, $dateTo])
            ->select(
                'users.name as staff_name',
                DB::raw('COUNT(production_status_logs.id) as total_actions'),
                DB::raw("SUM(CASE WHEN production_status_logs.status = 'SIAP' THEN 1 ELSE 0 END) as completed_orders"),
                DB::raw("SUM(CASE WHEN production_status_logs.status = 'DIAMBIL' THEN 1 ELSE 0 END) as picked_up_orders"),
                DB::raw("COUNT(DISTINCT production_status_logs.order_id) as unique_orders"),
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_actions')
            ->get();

        $baseQ = $this->baseOrderQuery($branchId);
        $periodRevenue = (clone $baseQ)->where('payment_status', 'paid')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])->sum('total');
        $periodOrders = (clone $baseQ)->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])->count();
        $periodAvgOrder = $periodOrders > 0 ? $periodRevenue / $periodOrders : 0;

        $topServices = OrderItem::whereHas('order', function ($q) use ($branchId, $dateFrom, $dateTo) {
            $branchId ? $q->where('branch_id', $branchId) : $q->withoutGlobalScopes();
            $q->whereBetween(DB::raw('DATE(orders.created_at)'), [$dateFrom, $dateTo]);
        })
            ->select('service_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_revenue'), DB::raw('COUNT(DISTINCT order_id) as total_orders'))
            ->with('service:id,name')
            ->groupBy('service_id')
            ->orderByDesc('total_revenue')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($branchName, $dateFrom, $dateTo, $cashiers, $staffProductivity, $topServices, $periodRevenue, $periodOrders, $periodAvgOrder) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['ISTANA LAUNDRY ERP - LAPORAN KINERJA & PRODUKTIVITAS']);
            fputcsv($file, ["Cabang: {$branchName}", "Periode: {$dateFrom} s/d {$dateTo}"]);
            fputcsv($file, []);

            fputcsv($file, ['RINGKASAN KPI', 'NILAI']);
            fputcsv($file, ['Total Pendapatan Lunas (Rp)', number_format($periodRevenue, 2, '.', '')]);
            fputcsv($file, ['Total Nota Terbuat', $periodOrders]);
            fputcsv($file, ['Rata-rata Nilai Nota (Rp)', number_format($periodAvgOrder, 2, '.', '')]);
            fputcsv($file, []);

            fputcsv($file, ['--- LEADERBOARD OMSET KASIR ---']);
            fputcsv($file, ['RANK', 'NAMA KASIR', 'TOTAL NOTA', 'OMSET LUNAS (RP)', 'OMSET PENDING (RP)']);
            foreach ($cashiers as $i => $c) {
                fputcsv($file, [$i + 1, $c->name, $c->total_orders, number_format($c->total_revenue ?? 0, 2, '.', ''), number_format($c->total_pending_revenue ?? 0, 2, '.', '')]);
            }
            fputcsv($file, []);

            fputcsv($file, ['--- TOP 10 LAYANAN TERLARIS ---']);
            fputcsv($file, ['NAMA LAYANAN', 'JUMLAH TRANSAKSI', 'TOTAL QTY', 'TOTAL PENDAPATAN (RP)']);
            foreach ($topServices as $svc) {
                fputcsv($file, [$svc->service?->name ?? 'N/A', $svc->total_orders, number_format($svc->total_quantity, 2, '.', ''), number_format($svc->total_revenue, 2, '.', '')]);
            }
            fputcsv($file, []);

            fputcsv($file, ['--- PRODUKTIVITAS STAF WORKSHOP ---']);
            fputcsv($file, ['NAMA STAF', 'TOTAL AKSI', 'ORDER SIAP', 'ORDER DIAMBIL', 'ORDER UNIK']);
            foreach ($staffProductivity as $st) {
                fputcsv($file, [$st->staff_name, $st->total_actions, $st->completed_orders, $st->picked_up_orders, $st->unique_orders]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id') ?: null;
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $dateFrom   = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo     = $request->query('date_to', now()->toDateString());
        $branchName = $branchId ? (Branch::find($branchId)?->name ?? 'Cabang Unknown') : 'Seluruh Cabang';

        $cashiers = User::role(['Cashier', 'Branch_Admin', 'Owner', 'Super_Admin', 'Developer'])
            ->withCount(['orders as total_orders' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                $branchId ? $q->where('branch_id', $branchId) : $q->withoutGlobalScopes();
                $q->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }])
            ->withSum(['orders as total_revenue' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                $branchId ? $q->where('branch_id', $branchId) : $q->withoutGlobalScopes();
                $q->where('payment_status', 'paid')->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }], 'total')
            ->withSum(['orders as total_pending_revenue' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                $branchId ? $q->where('branch_id', $branchId) : $q->withoutGlobalScopes();
                $q->whereIn('payment_status', ['pending', 'partial'])->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }], 'total')
            ->get()->filter(fn ($u) => $u->total_orders > 0)->sortByDesc('total_revenue');

        $staffProductivity = ProductionStatusLog::query()
            ->join('users', 'production_status_logs.updated_by', '=', 'users.id')
            ->join('orders', 'production_status_logs.order_id', '=', 'orders.id')
            ->when($branchId, fn ($q) => $q->where('orders.branch_id', $branchId))
            ->whereBetween(DB::raw('DATE(production_status_logs.created_at)'), [$dateFrom, $dateTo])
            ->select('users.name as staff_name', DB::raw('COUNT(production_status_logs.id) as total_actions'), DB::raw("SUM(CASE WHEN production_status_logs.status = 'SIAP' THEN 1 ELSE 0 END) as completed_orders"), DB::raw("COUNT(DISTINCT production_status_logs.order_id) as unique_orders"))
            ->groupBy('users.id', 'users.name')->orderByDesc('total_actions')->get();

        $periodRevenue = $this->baseOrderQuery($branchId)->where('payment_status', 'paid')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])->sum('total');

        $topServices = OrderItem::whereHas('order', function ($q) use ($branchId, $dateFrom, $dateTo) {
            $branchId ? $q->where('branch_id', $branchId) : $q->withoutGlobalScopes();
            $q->whereBetween(DB::raw('DATE(orders.created_at)'), [$dateFrom, $dateTo]);
        })->select('service_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_revenue'), DB::raw('COUNT(DISTINCT order_id) as total_orders'))
            ->with('service:id,name')->groupBy('service_id')->orderByDesc('total_revenue')->take(10)->get();

        $cashierDailyBreakdown = $this->baseOrderQuery($branchId)
            ->select(DB::raw('DATE(created_at) as date'), 'cashier_id', DB::raw('COUNT(*) as total_orders'), DB::raw('SUM(CASE WHEN payment_status = "paid" THEN total ELSE 0 END) as paid_revenue'), DB::raw('SUM(discount_amount) as total_discount'))
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])->groupBy(DB::raw('DATE(created_at)'), 'cashier_id')->with('cashier:id,name')->orderByDesc('date')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.performance_pdf', compact(
            'branchName', 'dateFrom', 'dateTo', 'cashiers', 'cashierDailyBreakdown',
            'staffProductivity', 'periodRevenue', 'topServices'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('laporan-kinerja-' . now()->format('Ymd-His') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id') ?: null;
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $dateFrom   = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo     = $request->query('date_to', now()->toDateString());
        $branchName = $branchId ? (Branch::find($branchId)?->name ?? 'Cabang Unknown') : 'Seluruh Cabang';

        $cashiers = User::role(['Cashier', 'Branch_Admin', 'Owner', 'Super_Admin', 'Developer'])
            ->withCount(['orders as total_orders' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                $branchId ? $q->where('branch_id', $branchId) : $q->withoutGlobalScopes();
                $q->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }])
            ->withSum(['orders as total_revenue' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                $branchId ? $q->where('branch_id', $branchId) : $q->withoutGlobalScopes();
                $q->where('payment_status', 'paid')->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }], 'total')
            ->withSum(['orders as total_pending_revenue' => function ($q) use ($branchId, $dateFrom, $dateTo) {
                $branchId ? $q->where('branch_id', $branchId) : $q->withoutGlobalScopes();
                $q->whereIn('payment_status', ['pending', 'partial'])->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo]);
            }], 'total')
            ->get()->filter(fn ($u) => $u->total_orders > 0)->sortByDesc('total_revenue');

        $staffProductivity = ProductionStatusLog::query()
            ->join('users', 'production_status_logs.updated_by', '=', 'users.id')
            ->join('orders', 'production_status_logs.order_id', '=', 'orders.id')
            ->when($branchId, fn ($q) => $q->where('orders.branch_id', $branchId))
            ->whereBetween(DB::raw('DATE(production_status_logs.created_at)'), [$dateFrom, $dateTo])
            ->select('users.name as staff_name', DB::raw('COUNT(production_status_logs.id) as total_actions'), DB::raw("SUM(CASE WHEN production_status_logs.status = 'SIAP' THEN 1 ELSE 0 END) as completed_orders"), DB::raw("COUNT(DISTINCT production_status_logs.order_id) as unique_orders"))
            ->groupBy('users.id', 'users.name')->orderByDesc('total_actions')->get();

        $periodRevenue = $this->baseOrderQuery($branchId)->where('payment_status', 'paid')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])->sum('total');

        $topServices = OrderItem::whereHas('order', function ($q) use ($branchId, $dateFrom, $dateTo) {
            $branchId ? $q->where('branch_id', $branchId) : $q->withoutGlobalScopes();
            $q->whereBetween(DB::raw('DATE(orders.created_at)'), [$dateFrom, $dateTo]);
        })->select('service_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(subtotal) as total_revenue'), DB::raw('COUNT(DISTINCT order_id) as total_orders'))
            ->with('service:id,name')->groupBy('service_id')->orderByDesc('total_revenue')->take(10)->get();

        $cashierDailyBreakdown = $this->baseOrderQuery($branchId)
            ->select(DB::raw('DATE(created_at) as date'), 'cashier_id', DB::raw('COUNT(*) as total_orders'), DB::raw('SUM(CASE WHEN payment_status = "paid" THEN total ELSE 0 END) as paid_revenue'), DB::raw('SUM(discount_amount) as total_discount'))
            ->whereBetween(DB::raw('DATE(created_at)'), [$dateFrom, $dateTo])->groupBy(DB::raw('DATE(created_at)'), 'cashier_id')->with('cashier:id,name')->orderByDesc('date')->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\GenericViewExport('exports.performance_pdf', compact(
                'branchName', 'dateFrom', 'dateTo', 'cashiers', 'cashierDailyBreakdown',
                'staffProductivity', 'periodRevenue', 'topServices'
            ), 'Laporan Kinerja'),
            'laporan-kinerja-' . now()->format('Ymd-His') . '.xlsx'
        );
    }
}
