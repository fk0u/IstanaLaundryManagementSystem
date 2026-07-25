<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\JournalLine;
use App\Models\ChartOfAccount;
use App\Models\AccountingPeriod;
use App\Models\Branch;
use App\Services\Finance\JournalService;
use App\Exceptions\JournalNotBalancedException;
use App\Exceptions\AccountingPeriodClosedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JournalController extends Controller
{
    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function index(Request $request)
    {
        $journals = Journal::with(['createdByUser', 'journalLines.account'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code', 'asc')->get();

        return view('finance.journals.index', compact('journals', 'accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'description' => 'required|string|max:255',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $totalDebit = 0;
                $totalCredit = 0;

                foreach ($request->lines as $line) {
                    $totalDebit += (float)$line['debit'];
                    $totalCredit += (float)$line['credit'];
                }

                if (abs($totalDebit - $totalCredit) > 0.01) {
                    throw new JournalNotBalancedException("Debit ({$totalDebit}) harus sama dengan Kredit ({$totalCredit}). Selisih: " . abs($totalDebit - $totalCredit));
                }

                $branchId = auth()->user()->branch_id ?? 1;
                $carbonDate = Carbon::parse($request->date);
                $year = $carbonDate->year;
                $month = $carbonDate->month;

                $period = AccountingPeriod::where('branch_id', $branchId)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->first();

                if (!$period) {
                    $period = AccountingPeriod::create([
                        'branch_id' => $branchId,
                        'year' => $year,
                        'month' => $month,
                        'status' => 'open',
                    ]);
                }

                if ($period->status === 'closed') {
                    throw new AccountingPeriodClosedException("Periode akuntansi sudah ditutup. Tidak bisa menambah jurnal.");
                }

                $branch = Branch::find($branchId);
                $branchCode = $branch ? $branch->code : 'HQ';
                $yearMonthStr = $carbonDate->format('Ym');

                // Generate Manual Journal Reference
                $count = Journal::withoutGlobalScopes()
                    ->where('branch_id', $branchId)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->count();
                $seqStr = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
                $reference = "JRN-{$branchCode}-{$yearMonthStr}-{$seqStr}";

                $journal = Journal::create([
                    'branch_id' => $branchId,
                    'accounting_period_id' => $period->id,
                    'reference' => $reference,
                    'type' => 'manual',
                    'description' => $request->description,
                    'date' => $request->date,
                    'status' => 'posted',
                    'created_by' => auth()->id() ?? 1,
                    'posted_at' => now()
                ]);

                foreach ($request->lines as $line) {
                    JournalLine::create([
                        'journal_id' => $journal->id,
                        'account_id' => $line['account_id'],
                        'debit' => $line['debit'],
                        'credit' => $line['credit'],
                        'description' => $line['description'] ?? $request->description
                    ]);
                }
            });

            return redirect()->back()->with('success', 'Jurnal manual berhasil disimpan dan diposting.');
        } catch (JournalNotBalancedException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (AccountingPeriodClosedException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reverse($id)
    {
        $journal = Journal::findOrFail($id);

        try {
            $this->journalService->reverseJournal($journal, auth()->user());
            return redirect()->back()->with('success', 'Jurnal berhasil dibatalkan (reversed).');
        } catch (AccountingPeriodClosedException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
