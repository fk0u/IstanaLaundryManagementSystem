<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftApiController extends Controller
{
    public function index(Request $request)
    {
        $shifts = CashierShift::with(['cashier', 'branch'])
            ->orderBy('start_time', 'desc')
            ->paginate($request->query('per_page', 10));

        return response()->json([
            'status' => 'success',
            'data' => $shifts,
        ]);
    }

    public function openShift(Request $request)
    {
        $validated = $request->validate([
            'start_cash' => 'required|numeric|min:0',
        ]);

        $cashierId = auth()->id() ?? 1;
        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id ?? 1;

        $existing = CashierShift::where('cashier_id', $cashierId)->whereNull('end_time')->first();
        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda masih memiliki shift yang sedang aktif.',
                'data' => $existing,
            ], 422);
        }

        $shift = CashierShift::create([
            'cashier_id' => $cashierId,
            'branch_id' => $branchId,
            'start_cash' => $validated['start_cash'],
            'start_time' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Shift kasir baru berhasil dibuka!',
            'data' => $shift,
        ], 201);
    }

    public function closeShift(Request $request)
    {
        $validated = $request->validate([
            'actual_end_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $cashierId = auth()->id() ?? 1;
        $shift = CashierShift::where('cashier_id', $cashierId)->whereNull('end_time')->first();

        if (! $shift) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ditemukan shift aktif yang perlu ditutup.',
            ], 404);
        }

        return DB::transaction(function () use ($validated, $shift) {
            $totalSalesCash = (float) DB::table('order_payments')
                ->join('orders', 'order_payments.order_id', '=', 'orders.id')
                ->where('orders.cashier_shift_id', $shift->id)
                ->where('order_payments.payment_method', 'cash')
                ->sum('order_payments.amount');

            $expectedCash = (float) $shift->start_cash + $totalSalesCash;
            $actualCash = (float) $validated['actual_end_cash'];
            $difference = $actualCash - $expectedCash;

            $shift->update([
                'end_time' => now(),
                'end_cash' => $expectedCash,
                'actual_end_cash' => $actualCash,
                'difference' => $difference,
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Shift kasir berhasil ditutup!',
                'data' => $shift,
            ]);
        });
    }
}
