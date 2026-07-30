<?php

namespace App\Services\Finance;

use App\Exceptions\AccountingPeriodClosedException;
use App\Exceptions\JournalNotBalancedException;
use App\Models\AccountingPeriod;
use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\DepreciationSchedule;
use App\Models\GoodsReceivedNote;
use App\Models\Journal;
use App\Models\JournalLine;
use App\Models\Order;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JournalService
{
    /**
     * Create a balanced journal entry from business transactions automatically.
     *
     * @param  mixed  $sourceModel
     * @param  array  $entries  Array of ['account_id' => x, 'debit' => x, 'credit' => x, 'description' => x]
     *
     * @throws JournalNotBalancedException
     * @throws AccountingPeriodClosedException
     */
    public function autoPostJournal($sourceModel, int $sourceId, array $entries, ?string $date = null): Journal
    {
        return DB::transaction(function () use ($sourceModel, $sourceId, $entries, $date) {
            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($entries as $entry) {
                $totalDebit += (float) $entry['debit'];
                $totalCredit += (float) $entry['credit'];
            }

            if (abs($totalDebit - $totalCredit) > 0.01) {
                Log::error('Journal not balanced', [
                    'source_type' => get_class($sourceModel),
                    'source_id' => $sourceId,
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                ]);
                throw new JournalNotBalancedException("Jurnal tidak seimbang. Total Debit: {$totalDebit}, Total Kredit: {$totalCredit}");
            }

            $branchId = $sourceModel->branch_id ?? session('scoped_branch_id') ?? auth()->user()?->branch_id;
            if (! $branchId || ! Branch::where('id', $branchId)->exists()) {
                $branchId = Branch::first()?->id;
            }

            $carbonDate = $date ? Carbon::parse($date) : now();
            $year = $carbonDate->year;
            $month = $carbonDate->month;

            $period = AccountingPeriod::where('branch_id', $branchId)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if (! $period) {
                $period = AccountingPeriod::create([
                    'branch_id' => $branchId,
                    'year' => $year,
                    'month' => $month,
                    'status' => 'open',
                ]);
            }

            if ($period->status === 'closed') {
                Log::error('Attempted to post journal to closed period', [
                    'source_type' => get_class($sourceModel),
                    'source_id' => $sourceId,
                    'period_id' => $period->id,
                    'year' => $year,
                    'month' => $month,
                ]);
                throw new AccountingPeriodClosedException("Tidak dapat memposting jurnal ke periode akuntansi yang sudah ditutup ({$year}-{$month}).");
            }

            $branch = Branch::find($branchId);
            $branchCode = $branch ? $branch->code : 'HQ';
            $yearMonthStr = $carbonDate->format('Ym');

            // Idempotency check: check if journal already exists for this source
            $existingJournal = Journal::withoutGlobalScopes()
                ->where('branch_id', $branchId)
                ->where('source_type', get_class($sourceModel))
                ->where('source_id', $sourceId)
                ->where('status', '!=', 'reversed')
                ->first();

            if ($existingJournal) {
                Log::warning('Journal already exists for source (idempotency)', [
                    'source_type' => get_class($sourceModel),
                    'source_id' => $sourceId,
                    'existing_reference' => $existingJournal->reference,
                ]);
                throw new \Exception("Jurnal sudah ada untuk {$sourceModel} #{$sourceId} (Reference: {$existingJournal->reference}). Gunakan fungsi reverse jika perlu membatalkan.");
            }

            // Count existing journals for sequence with lock to prevent race condition
            $lastSeq = Journal::withoutGlobalScopes()
                ->where('branch_id', $branchId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->lockForUpdate()
                ->count();
            $seqStr = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
            $reference = "JRN-{$branchCode}-{$yearMonthStr}-{$seqStr}";

            $journal = Journal::create([
                'branch_id' => $branchId,
                'accounting_period_id' => $period->id,
                'reference' => $reference,
                'source_type' => get_class($sourceModel),
                'source_id' => $sourceId,
                'type' => 'auto',
                'description' => 'Jurnal otomatis untuk '.class_basename($sourceModel)." #{$sourceId}",
                'date' => $carbonDate->toDateString(),
                'status' => 'posted',
                'created_by' => auth()->id() ?? User::where('branch_id', $branchId)->first()?->id ?? 1,
                'posted_at' => now(),
            ]);

            foreach ($entries as $entry) {
                JournalLine::create([
                    'journal_id' => $journal->id,
                    'account_id' => $entry['account_id'],
                    'debit' => $entry['debit'],
                    'credit' => $entry['credit'],
                    'description' => $entry['description'],
                ]);
            }

            return $journal;
        });
    }

    /**
     * Post journal entry for an Order payment.
     */
    public function postOrderJournal(Order $order): Journal
    {
        $entries = [];

        // Determine Debit Account (Cash / Bank or Accounts Receivable)
        if (in_array($order->payment_method, ['cash', 'transfer'])) {
            $debitAccount = ChartOfAccount::where('code', '1-1101')->first()
                ?? ChartOfAccount::firstOrCreate(['code' => '1-1101'], ['name' => 'Kas Kecil', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3]);
        } else {
            $debitAccount = ChartOfAccount::where('code', '1-1201')->first()
                ?? ChartOfAccount::firstOrCreate(['code' => '1-1201'], ['name' => 'Piutang Usaha', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3]);
        }

        $entries[] = [
            'account_id' => $debitAccount->id,
            'debit' => $order->total,
            'credit' => 0,
            'description' => "Penerimaan pembayaran order #{$order->order_number}",
        ];

        // Determine Credit Account (Revenue)
        $revenueAccount = ChartOfAccount::where('code', '4-1001')->first()
            ?? ChartOfAccount::firstOrCreate(['code' => '4-1001'], ['name' => 'Pendapatan Jasa Laundry', 'type' => 'revenue', 'normal_balance' => 'credit', 'level' => 3]);

        $entries[] = [
            'account_id' => $revenueAccount->id,
            'debit' => 0,
            'credit' => $order->subtotal,
            'description' => "Pendapatan jasa laundry order #{$order->order_number}",
        ];

        // If there's a discount (promo coupon or points redemption)
        $totalDiscount = (float) $order->subtotal + (float) $order->tax_amount - (float) $order->total;
        if ($totalDiscount > 0.001) {
            $discountAccount = ChartOfAccount::where('code', '5-4105')->first()
                ?? ChartOfAccount::firstOrCreate(['code' => '5-4105'], ['name' => 'Beban Marketing & Promosi', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3]);

            $entries[] = [
                'account_id' => $discountAccount->id,
                'debit' => $totalDiscount,
                'credit' => 0,
                'description' => "Beban diskon promo & poin order #{$order->order_number}",
            ];
        }

        // If there's tax
        if ($order->tax_amount > 0) {
            $taxAccount = ChartOfAccount::where('code', '2-2101')->first()
                ?? ChartOfAccount::firstOrCreate(['code' => '2-2101'], ['name' => 'Hutang PPN', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3]);

            $entries[] = [
                'account_id' => $taxAccount->id,
                'debit' => 0,
                'credit' => $order->tax_amount,
                'description' => "Pajak PPN keluaran order #{$order->order_number}",
            ];
        }

        return $this->autoPostJournal($order, $order->id, $entries, $order->created_at?->toDateString());
    }

    /**
     * Post journal entry for a GRN confirmation.
     */
    public function postGRNJournal(GoodsReceivedNote $grn): Journal
    {
        $totalCost = 0;
        foreach ($grn->items as $item) {
            $totalCost += (float) $item->quantity * (float) $item->unit_cost;
        }

        $entries = [];

        // Dr: Persediaan Bahan Habis Pakai
        $inventoryAccount = ChartOfAccount::where('code', '1-1301')->first()
            ?? ChartOfAccount::firstOrCreate(['code' => '1-1301'], ['name' => 'Persediaan Bahan Habis Pakai', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3]);

        $entries[] = [
            'account_id' => $inventoryAccount->id,
            'debit' => $totalCost,
            'credit' => 0,
            'description' => "Persediaan masuk dari penerimaan barang GRN #{$grn->grn_number}",
        ];

        // Cr: Hutang Usaha
        $payableAccount = ChartOfAccount::where('code', '2-1101')->first()
            ?? ChartOfAccount::firstOrCreate(['code' => '2-1101'], ['name' => 'Hutang Usaha', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3]);

        $entries[] = [
            'account_id' => $payableAccount->id,
            'debit' => 0,
            'credit' => $totalCost,
            'description' => "Hutang atas penerimaan barang GRN #{$grn->grn_number}",
        ];

        return $this->autoPostJournal($grn, $grn->id, $entries, $grn->received_date?->toDateString() ?? now()->toDateString());
    }

    /**
     * Post journal entry for Payroll.
     */
    public function postPayrollJournal(Payroll $payroll): Journal
    {
        $totalNetSalary = $payroll->payrollItems()->sum('net_salary');

        $entries = [];

        // Dr: Beban Gaji
        $expenseAccount = ChartOfAccount::where('code', '5-3101')->first()
            ?? ChartOfAccount::firstOrCreate(['code' => '5-3101'], ['name' => 'Beban Gaji & Upah', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3]);

        $entries[] = [
            'account_id' => $expenseAccount->id,
            'debit' => $totalNetSalary,
            'credit' => 0,
            'description' => "Beban gaji karyawan periode {$payroll->month}/{$payroll->year}",
        ];

        // Cr: Kas Kecil
        $cashAccount = ChartOfAccount::where('code', '1-1101')->first()
            ?? ChartOfAccount::firstOrCreate(['code' => '1-1101'], ['name' => 'Kas Kecil', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3]);

        $entries[] = [
            'account_id' => $cashAccount->id,
            'debit' => 0,
            'credit' => $totalNetSalary,
            'description' => "Pembayaran gaji karyawan periode {$payroll->month}/{$payroll->year}",
        ];

        return $this->autoPostJournal($payroll, $payroll->id, $entries, now()->toDateString());
    }

    /**
     * Post journal entry for Depreciation.
     */
    public function postDepreciationJournal(DepreciationSchedule $schedule): Journal
    {
        $asset = $schedule->fixedAsset;
        $amount = (float) $schedule->depreciation_amount;

        $entries = [];

        // Dr: Beban Penyusutan Aset
        $depExpenseAccount = ChartOfAccount::where('code', '5-4101')->first()
            ?? ChartOfAccount::firstOrCreate(['code' => '5-4101'], ['name' => 'Beban Penyusutan Aset', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3]);

        $entries[] = [
            'account_id' => $depExpenseAccount->id,
            'debit' => $amount,
            'credit' => 0,
            'description' => "Beban penyusutan aset {$asset->name} untuk periode {$schedule->period_date?->format('Y-m')}",
        ];

        // Cr: Akumulasi Penyusutan berdasarkan Kategori Aset
        $accCode = '1-2901'; // Default: Mesin Cuci
        if ($asset->category === 'peralatan') {
            $accCode = '1-2901';
        } elseif ($asset->category === 'kendaraan') {
            $accCode = '1-2903';
        } elseif ($asset->category === 'furniture') {
            $accCode = '1-2904';
        }

        $accumulatedAccount = ChartOfAccount::where('code', $accCode)->first()
            ?? ChartOfAccount::firstOrCreate(['code' => $accCode], ['name' => "Akum. Penyusutan {$asset->category}", 'type' => 'asset', 'normal_balance' => 'credit', 'level' => 3]);
        $entries[] = [
            'account_id' => $accumulatedAccount->id,
            'debit' => 0,
            'credit' => $amount,
            'description' => "Akum. penyusutan aset {$asset->name} periode {$schedule->period_date?->format('Y-m')}",
        ];

        // Perform the post
        $journal = $this->autoPostJournal($schedule, $schedule->id, $entries, $schedule->period_date?->toDateString());

        // Update schedule
        $schedule->update([
            'is_posted' => true,
            'journal_id' => $journal->id,
        ]);

        return $journal;
    }

    /**
     * Reverse a posted journal.
     *
     * @throws AccountingPeriodClosedException
     */
    public function reverseJournal(Journal $journal, User $user): Journal
    {
        return DB::transaction(function () use ($journal, $user) {
            $period = $journal->accountingPeriod;
            if ($period && $period->status === 'closed') {
                throw new AccountingPeriodClosedException('Tidak dapat membatalkan jurnal pada periode akuntansi yang sudah ditutup.');
            }

            $reversalReference = 'REV-'.$journal->reference;

            $reversalJournal = Journal::create([
                'branch_id' => $journal->branch_id,
                'accounting_period_id' => $journal->accounting_period_id,
                'reference' => $reversalReference,
                'source_type' => $journal->source_type,
                'source_id' => $journal->source_id,
                'type' => 'reversal',
                'description' => "Pembatalan / Reversal untuk jurnal {$journal->reference}",
                'date' => now()->toDateString(),
                'status' => 'posted',
                'created_by' => $user->id,
                'posted_at' => now(),
            ]);

            foreach ($journal->journalLines as $line) {
                JournalLine::create([
                    'journal_id' => $reversalJournal->id,
                    'account_id' => $line->account_id,
                    'debit' => $line->credit, // Swapped
                    'credit' => $line->debit, // Swapped
                    'description' => 'Pembatalan: '.$line->description,
                ]);
            }

            $journal->update([
                'status' => 'reversed',
                'reversed_by' => $reversalJournal->id,
            ]);

            return $reversalJournal;
        });
    }
}
