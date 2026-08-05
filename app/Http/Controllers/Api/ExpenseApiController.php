<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\OperationalExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseApiController extends Controller
{
    public function index(Request $request)
    {
        $expenses = OperationalExpense::with(['account', 'branch'])
            ->orderBy('expense_date', 'desc')
            ->paginate($request->query('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $expenses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:chart_of_accounts,id',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,qr_qris',
            'description' => 'required|string|max:500',
        ]);

        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id ?? 1;

        return DB::transaction(function () use ($validated, $branchId) {
            $expense = OperationalExpense::create([
                'branch_id' => $branchId,
                'account_id' => $validated['account_id'],
                'created_by' => auth()->id() ?? 1,
                'amount' => $validated['amount'],
                'expense_date' => $validated['expense_date'],
                'payment_method' => $validated['payment_method'],
                'description' => $validated['description'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pengeluaran operasional berhasil dicatat!',
                'data' => $expense->load(['account', 'branch']),
            ], 201);
        });
    }

    public function show(OperationalExpense $expense)
    {
        $expense->load(['account', 'branch', 'creator']);

        return response()->json([
            'status' => 'success',
            'data' => $expense,
        ]);
    }
}
