<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CashierShift;
use App\Models\Journal;
use App\Models\OrderPayment;
use App\Models\User;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * Display cashier shift audit & settlement index.
     */
    public function index(Request $request)
    {
        $branchId = session('scoped_branch_id') ?? auth()->user()->branch_id;

        $query = CashierShift::with(['branch', 'cashier', 'pettyCashRecords']);

        // Scope by branch if set
        if ($branchId) {
            $query->where('branch_id', $branchId);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('cashier_id')) {
            $query->where('cashier_id', $request->cashier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->status));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('opened_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('opened_at', '<=', $request->date_to);
        }

        $shifts = $query->orderByDesc('opened_at')->paginate(15);

        // Attach computed totals for each shift in the paginated set
        $shifts->getCollection()->transform(function ($shift) {
            $shiftOrderPayments = OrderPayment::whereHas('order', function ($q) use ($shift) {
                $q->where('cashier_shift_id', $shift->id);
            })->get();

            $cashSales = (float) $shiftOrderPayments->where('payment_method', 'CASH')->sum('amount');
            $qrisSales = (float) $shiftOrderPayments->where('payment_method', 'QRIS')->sum('amount');
            $transferSales = (float) $shiftOrderPayments->where('payment_method', 'TRANSFER')->sum('amount');
            $debitSales = (float) $shiftOrderPayments->where('payment_method', 'DEBIT')->sum('amount');

            $directCashOrders = \App\Models\Order::where('cashier_shift_id', $shift->id)
                ->whereDoesntHave('payments')
                ->whereIn('payment_method', ['cash', 'CASH'])
                ->sum('paid_amount');
            $cashSales += (float) $directCashOrders;

            $shift->computed_cash_sales = $cashSales;
            $shift->computed_non_cash_sales = $qrisSales + $transferSales + $debitSales;
            $shift->computed_total_omset = $cashSales + $shift->computed_non_cash_sales;

            return $shift;
        });

        // Summary statistics
        $kpiQuery = CashierShift::query();
        if ($branchId) {
            $kpiQuery->where('branch_id', $branchId);
        }

        $totalClosedShifts = (clone $kpiQuery)->where('status', 'CLOSED')->count();
        $totalPettyCashSpent = (clone $kpiQuery)->sum('petty_cash_total');
        $totalCashDifference = (clone $kpiQuery)->where('status', 'CLOSED')->sum('cash_difference');

        $branches = Branch::orderBy('name')->get();
        $cashiers = User::orderBy('name')->get();

        return view('shifts.index', compact(
            'shifts',
            'branches',
            'cashiers',
            'totalClosedShifts',
            'totalPettyCashSpent',
            'totalCashDifference'
        ));
    }

    /**
     * Display detailed cashier shift audit & settlement report.
     */
    public function show(CashierShift $shift)
    {
        $shift->load([
            'branch',
            'cashier',
            'pettyCashRecords.user',
            'orders.customer',
            'orders.payments',
        ]);

        $payments = OrderPayment::whereHas('order', function ($q) use ($shift) {
            $q->where('cashier_shift_id', $shift->id);
        })->get();

        $cashSales = (float) $payments->where('payment_method', 'CASH')->sum('amount');
        $qrisSales = (float) $payments->where('payment_method', 'QRIS')->sum('amount');
        $transferSales = (float) $payments->where('payment_method', 'TRANSFER')->sum('amount');
        $debitSales = (float) $payments->where('payment_method', 'DEBIT')->sum('amount');
        $dpSales = (float) $payments->where('payment_method', 'DP')->sum('amount');

        $directCashOrders = \App\Models\Order::where('cashier_shift_id', $shift->id)
            ->whereDoesntHave('payments')
            ->whereIn('payment_method', ['cash', 'CASH'])
            ->sum('paid_amount');
        $cashSales += (float) $directCashOrders;

        // Fetch corresponding financial journal entry
        $journal = Journal::withoutGlobalScopes()
            ->where('source_type', CashierShift::class)
            ->where('source_id', $shift->id)
            ->with('journalLines.account')
            ->first();

        return view('shifts.show', compact(
            'shift',
            'cashSales',
            'qrisSales',
            'transferSales',
            'debitSales',
            'dpSales',
            'journal'
        ));
    }
}
