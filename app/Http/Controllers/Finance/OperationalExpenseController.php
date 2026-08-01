<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\OperationalExpense;
use App\Services\Finance\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OperationalExpenseController extends Controller
{
    protected JournalService $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id');
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $branches = Branch::orderBy('name')->get();

        // Expense accounts (type = expense, codes starting with 5-2)
        $expenseAccounts = ChartOfAccount::where('type', 'expense')
            ->where('code', 'like', '5-2%')
            ->orderBy('code')
            ->get();

        // If no specific OpEx accounts exist yet, show all expense accounts
        if ($expenseAccounts->isEmpty()) {
            $expenseAccounts = ChartOfAccount::where('type', 'expense')
                ->orderBy('code')
                ->get();
        }

        $query = OperationalExpense::with(['account', 'branch', 'creator'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        // Date filter
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());

        $query->whereBetween('expense_date', [$startDate, $endDate]);

        $expenses = $query->orderByDesc('expense_date')->orderByDesc('id')->paginate(20);

        // Summary analytics
        $totalExpenses = (clone $query)->sum('amount');
        $expensesByCategory = OperationalExpense::with('account')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->get()
            ->map(fn ($row) => [
                'account' => $row->account,
                'total' => (float) $row->total,
            ]);

        return view('finance.operational-expenses.index', compact(
            'expenses',
            'branches',
            'branchId',
            'expenseAccounts',
            'startDate',
            'endDate',
            'totalExpenses',
            'expensesByCategory'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'expense_date' => 'required|date|before_or_equal:today',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'receipt_number' => 'nullable|string|max:100',
            'payment_method' => 'required|in:cash,transfer',
        ]);

        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id;
        if (! $branchId) {
            $branchId = Branch::first()?->id;
        }

        try {
            $expense = OperationalExpense::create([
                'branch_id' => $branchId,
                'expense_date' => $request->expense_date,
                'account_id' => $request->account_id,
                'amount' => $request->amount,
                'description' => $request->description,
                'receipt_number' => $request->receipt_number,
                'payment_method' => $request->payment_method,
                'status' => 'posted',
                'created_by' => Auth::id(),
            ]);

            $journal = $this->journalService->postOperationalExpenseJournal($expense);

            return redirect()->back()->with('success', "Beban operasional berhasil dicatat dan jurnal keuangan tersinkronisasi! (Ref: {$journal->reference})");
        } catch (\Exception $e) {
            Log::error('Failed to record operational expense: '.$e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Gagal mencatat beban operasional: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        $expense = OperationalExpense::findOrFail($id);

        // Check if there's a journal for this expense and reverse it
        $journal = Journal::withoutGlobalScopes()
            ->where('source_type', OperationalExpense::class)
            ->where('source_id', $expense->id)
            ->where('status', '!=', 'reversed')
            ->first();

        if ($journal) {
            try {
                $this->journalService->reverseJournal($journal, auth()->user());
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Gagal membatalkan jurnal terkait: '.$e->getMessage());
            }
        }

        $expense->delete();

        return redirect()->back()->with('success', 'Catatan beban operasional berhasil dihapus dan jurnal dibatalkan.');
    }
}
