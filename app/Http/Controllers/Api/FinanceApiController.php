<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\Finance\FinancialReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceApiController extends Controller
{
    protected FinancialReportService $reportService;

    public function __construct(FinancialReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function coa()
    {
        $coas = ChartOfAccount::orderBy('code', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $coas,
        ]);
    }

    public function storeCoa(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:chart_of_accounts,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
        ]);

        $parent = $validated['parent_id'] ? ChartOfAccount::find($validated['parent_id']) : null;
        $level = $parent ? $parent->level + 1 : 1;

        $account = ChartOfAccount::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'normal_balance' => $validated['normal_balance'],
            'parent_id' => $validated['parent_id'] ?? null,
            'level' => $level,
            'is_active' => true,
            'is_system' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Akun COA baru berhasil dibuat!',
            'data' => $account,
        ], 201);
    }

    public function journals(Request $request)
    {
        $journals = JournalEntry::with(['lines.account', 'branch'])
            ->orderBy('date', 'desc')
            ->paginate($request->query('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $journals,
        ]);
    }

    public function storeJournal(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
        ]);

        $totalDebit = collect($validated['lines'])->sum('debit');
        $totalCredit = collect($validated['lines'])->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return response()->json([
                'status' => 'error',
                'message' => 'Entri jurnal tidak seimbang! Total Debit (' . number_format($totalDebit, 2) . ') harus sama dengan Total Kredit (' . number_format($totalCredit, 2) . ').',
            ], 422);
        }

        return DB::transaction(function () use ($validated, $totalDebit) {
            $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id ?? 1;

            $entry = JournalEntry::create([
                'branch_id' => $branchId,
                'entry_number' => 'JRN-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'date' => $validated['date'],
                'description' => $validated['description'],
                'total_debit' => $totalDebit,
                'total_credit' => $totalDebit,
                'created_by' => auth()->id() ?? 1,
            ]);

            foreach ($validated['lines'] as $line) {
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Entri Jurnal Umum berhasil dicatat!',
                'data' => $entry->load('lines.account'),
            ], 201);
        });
    }

    public function incomeStatement(Request $request)
    {
        $year = (int) $request->query('year', date('Y'));
        $month = (int) $request->query('month', date('n'));
        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id;

        $statement = $this->reportService->getIncomeStatement($branchId, $year, $month);

        return response()->json([
            'status' => 'success',
            'data' => $statement,
        ]);
    }

    public function balanceSheet(Request $request)
    {
        $year = (int) $request->query('year', date('Y'));
        $month = (int) $request->query('month', date('n'));
        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id;

        $sheet = $this->reportService->getBalanceSheet($branchId, $year, $month);

        return response()->json([
            'status' => 'success',
            'data' => $sheet,
        ]);
    }

    public function trialBalance(Request $request)
    {
        $year = (int) $request->query('year', date('Y'));
        $month = (int) $request->query('month', date('n'));
        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id;

        $tb = $this->reportService->getTrialBalance($branchId, $year, $month);

        return response()->json([
            'status' => 'success',
            'data' => $tb,
        ]);
    }

    public function accountingPeriods()
    {
        $periods = AccountingPeriod::orderBy('year', 'desc')->orderBy('month', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $periods,
        ]);
    }

    public function closeAccountingPeriod(Request $request, AccountingPeriod $period)
    {
        if ($period->is_closed) {
            return response()->json([
                'status' => 'error',
                'message' => 'Periode akuntansi ini sudah dalam status Tutup Buku.',
            ], 422);
        }

        $period->update([
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by' => auth()->id() ?? 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Proses Tutup Buku periode ' . $period->year . '-' . sprintf('%02d', $period->month) . ' berhasil dilakukan!',
            'data' => $period,
        ]);
    }
}
