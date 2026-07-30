<?php

namespace App\Services;

use App\Models\JournalLine;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    /**
     * Get Trial Balance (Neraca Saldo)
     */
    public function getTrialBalance(?int $branchId, ?int $year, ?int $month): array
    {
        $query = JournalLine::query()
            ->join('journals', 'journal_lines.journal_id', '=', 'journals.id')
            ->join('chart_of_accounts', 'journal_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('journals.status', 'posted');

        if ($branchId) {
            $query->where('journals.branch_id', $branchId);
        }
        if ($year) {
            $query->whereYear('journals.date', $year);
        }
        if ($month) {
            $query->whereMonth('journals.date', $month);
        }

        $balances = $query->select(
            'chart_of_accounts.id',
            'chart_of_accounts.code',
            'chart_of_accounts.name',
            'chart_of_accounts.type',
            'chart_of_accounts.normal_balance',
            DB::raw('SUM(journal_lines.debit) as total_debit'),
            DB::raw('SUM(journal_lines.credit) as total_credit')
        )
            ->groupBy(
                'chart_of_accounts.id',
                'chart_of_accounts.code',
                'chart_of_accounts.name',
                'chart_of_accounts.type',
                'chart_of_accounts.normal_balance'
            )
            ->orderBy('chart_of_accounts.code')
            ->get();

        $totalDebit = 0;
        $totalCredit = 0;

        $rows = $balances->map(function ($row) use (&$totalDebit, &$totalCredit) {
            $debit = (float) $row->total_debit;
            $credit = (float) $row->total_credit;

            $netDebit = 0;
            $netCredit = 0;

            if ($row->normal_balance === 'debit') {
                $net = $debit - $credit;
                if ($net >= 0) {
                    $netDebit = $net;
                } else {
                    $netCredit = abs($net);
                }
            } else {
                $net = $credit - $debit;
                if ($net >= 0) {
                    $netCredit = $net;
                } else {
                    $netDebit = abs($net);
                }
            }

            $totalDebit += $netDebit;
            $totalCredit += $netCredit;

            return [
                'code' => $row->code,
                'name' => $row->name,
                'type' => $row->type,
                'debit' => $netDebit,
                'credit' => $netCredit,
            ];
        });

        return [
            'rows' => $rows,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }

    /**
     * Get Income Statement (Laporan Laba Rugi)
     */
    public function getIncomeStatement(?int $branchId, ?int $year, ?int $month): array
    {
        $tb = $this->getTrialBalance($branchId, $year, $month);

        $revenues = [];
        $expenses = [];
        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($tb['rows'] as $row) {
            if ($row['type'] === 'revenue') {
                $amount = $row['credit'] - $row['debit'];
                $revenues[] = [
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'amount' => $amount,
                ];
                $totalRevenue += $amount;
            } elseif ($row['type'] === 'expense') {
                $amount = $row['debit'] - $row['credit'];
                $expenses[] = [
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'amount' => $amount,
                ];
                $totalExpense += $amount;
            }
        }

        $netIncome = $totalRevenue - $totalExpense;

        return [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'total_revenue' => $totalRevenue,
            'total_expense' => $totalExpense,
            'net_income' => $netIncome,
        ];
    }

    /**
     * Get Balance Sheet (Laporan Neraca)
     */
    public function getBalanceSheet(?int $branchId, ?int $year, ?int $month): array
    {
        $tb = $this->getTrialBalance($branchId, $year, $month);
        $incomeStatement = $this->getIncomeStatement($branchId, $year, $month);

        $assets = [];
        $liabilities = [];
        $equities = [];
        $totalAssets = 0;
        $totalLiabilities = 0;
        $totalEquities = 0;

        foreach ($tb['rows'] as $row) {
            if ($row['type'] === 'asset') {
                $amount = $row['debit'] - $row['credit'];
                $assets[] = ['code' => $row['code'], 'name' => $row['name'], 'amount' => $amount];
                $totalAssets += $amount;
            } elseif ($row['type'] === 'liability') {
                $amount = $row['credit'] - $row['debit'];
                $liabilities[] = ['code' => $row['code'], 'name' => $row['name'], 'amount' => $amount];
                $totalLiabilities += $amount;
            } elseif ($row['type'] === 'equity') {
                $amount = $row['credit'] - $row['debit'];
                $equities[] = ['code' => $row['code'], 'name' => $row['name'], 'amount' => $amount];
                $totalEquities += $amount;
            }
        }

        // Add Current Period Net Income to Equity
        $equities[] = [
            'code' => 'NET_INC',
            'name' => 'Laba Bersih Periode Berjalan',
            'amount' => $incomeStatement['net_income'],
        ];
        $totalEquities += $incomeStatement['net_income'];

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equities' => $equities,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equities' => $totalEquities,
            'total_liabilities_equity' => $totalLiabilities + $totalEquities,
            'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquities)) < 0.01,
        ];
    }

    /**
     * Get Executive KPI Analytics & Deep Branch Breakdown.
     * Includes most popular / least popular services, branch performance ranking,
     * payment method breakdown, and operational efficiency metrics.
     */
    public function getExecutiveKpiAnalytics(?int $branchId, ?int $year, ?int $month): array
    {
        $ordersQuery = DB::table('orders')
            ->whereNull('deleted_at');

        if ($branchId) {
            $ordersQuery->where('branch_id', $branchId);
        }
        if ($year) {
            $ordersQuery->whereYear('created_at', $year);
        }
        if ($month) {
            $ordersQuery->whereMonth('created_at', $month);
        }

        // 1. Branch Revenue & Order Breakdown
        $branchBreakdown = DB::table('branches')
            ->whereNull('deleted_at')
            ->when($branchId, fn ($q) => $q->where('id', $branchId))
            ->select('branches.id', 'branches.name', 'branches.code')
            ->get()
            ->map(function ($br) use ($year, $month) {
                $sub = DB::table('orders')
                    ->where('branch_id', $br->id)
                    ->whereNull('deleted_at')
                    ->when($year, fn ($q) => $q->whereYear('created_at', $year))
                    ->when($month, fn ($q) => $q->whereMonth('created_at', $month));

                $totalRev = (float) (clone $sub)->sum('total');
                $paidRev = (float) (clone $sub)->where('payment_status', 'paid')->sum('paid_amount');
                $unpaidRev = (float) (clone $sub)->whereIn('payment_status', ['pending', 'partial'])->sum(DB::raw('total - paid_amount'));
                $totalOrders = (int) (clone $sub)->count();
                $avgOrderValue = $totalOrders > 0 ? $totalRev / $totalOrders : 0;

                return [
                    'id' => $br->id,
                    'name' => $br->name,
                    'code' => $br->code,
                    'total_revenue' => $totalRev,
                    'paid_revenue' => $paidRev,
                    'unpaid_revenue' => $unpaidRev,
                    'total_orders' => $totalOrders,
                    'avg_order_value' => $avgOrderValue,
                ];
            })
            ->sortByDesc('total_revenue')
            ->values();

        // 2. Most Popular & Least Popular Services (Layanan Paling Laris & Sepi)
        $servicePerformance = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('services', 'order_items.service_id', '=', 'services.id')
            ->whereNull('orders.deleted_at')
            ->when($branchId, fn ($q) => $q->where('orders.branch_id', $branchId))
            ->when($year, fn ($q) => $q->whereYear('orders.created_at', $year))
            ->when($month, fn ($q) => $q->whereMonth('orders.created_at', $month))
            ->select(
                'services.name',
                'services.type',
                'services.unit',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('services.id', 'services.name', 'services.type', 'services.unit')
            ->orderByDesc('total_qty')
            ->get();

        $topServices = $servicePerformance->take(5);
        $leastServices = $servicePerformance->reverse()->take(5);

        // 3. Payment Method Breakdown (Cash vs Transfer vs Invoice)
        $paymentMethods = (clone $ordersQuery)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total_amount'))
            ->groupBy('payment_method')
            ->get();

        // 4. Executive KPI Summary
        $totalGrossRevenue = (float) (clone $ordersQuery)->sum('total');
        $totalPaidRevenue = (float) (clone $ordersQuery)->sum('paid_amount');
        $totalOutstandingPiutang = (float) (clone $ordersQuery)->whereIn('payment_status', ['pending', 'partial'])->sum(DB::raw('total - paid_amount'));
        $totalOrdersCount = (int) (clone $ordersQuery)->count();
        $averageBasketSize = $totalOrdersCount > 0 ? $totalGrossRevenue / $totalOrdersCount : 0;

        return [
            'total_gross_revenue' => $totalGrossRevenue,
            'total_paid_revenue' => $totalPaidRevenue,
            'total_outstanding_piutang' => $totalOutstandingPiutang,
            'total_orders_count' => $totalOrdersCount,
            'average_basket_size' => $averageBasketSize,
            'branch_breakdown' => $branchBreakdown,
            'top_services' => $topServices,
            'least_services' => $leastServices,
            'payment_methods' => $paymentMethods,
        ];
    }
}
