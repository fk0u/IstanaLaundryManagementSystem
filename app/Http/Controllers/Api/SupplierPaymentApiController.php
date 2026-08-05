<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierPaymentApiController extends Controller
{
    public function index(Request $request)
    {
        $payments = SupplierPayment::with(['supplier', 'purchaseOrder', 'branch'])
            ->orderBy('payment_date', 'desc')
            ->paginate($request->query('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $payments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'po_id' => 'nullable|exists:purchase_orders,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,bank_transfer',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id ?? 1;

        return DB::transaction(function () use ($validated, $branchId) {
            $payment = SupplierPayment::create([
                'branch_id' => $branchId,
                'supplier_id' => $validated['supplier_id'],
                'po_id' => $validated['po_id'] ?? null,
                'created_by' => auth()->id() ?? 1,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran utang supplier berhasil dicatat!',
                'data' => $payment->load(['supplier', 'purchaseOrder']),
            ], 201);
        });
    }
}
