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
use App\Models\OperationalExpense;
use App\Models\Order;
use App\Models\Payroll;
use App\Models\SupplierPayment;
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
     * Post journal entry for Payroll with real-time double-entry accounting sync.
     */
    public function postPayrollJournal(Payroll $payroll): Journal
    {
        // Prevent duplicate posting for the same payroll
        $existingJournal = Journal::withoutGlobalScopes()
            ->where('source_type', Payroll::class)
            ->where('source_id', $payroll->id)
            ->first();

        if ($existingJournal) {
            return $existingJournal;
        }

        $items = $payroll->items()->get();

        $totalEarnings = (float) $items->sum(function ($item) {
            return (float) ($item->total_earnings > 0 ? $item->total_earnings : $item->calculateTotalEarnings());
        });

        $totalDeductions = (float) $items->sum(function ($item) {
            return (float) ($item->total_deductions > 0 ? $item->total_deductions : $item->calculateTotalDeductions());
        });

        $totalNetSalary = (float) $items->sum('net_salary');

        if ($totalEarnings <= 0 && $totalNetSalary <= 0) {
            $totalEarnings = 0;
            $totalNetSalary = 0;
        }

        $entries = [];

        // 1. Dr: Beban Gaji & Upah (5-3101) - Total Gaji Kotor
        $expenseAccount = ChartOfAccount::where('code', '5-3101')->first()
            ?? ChartOfAccount::firstOrCreate(['code' => '5-3101'], ['name' => 'Beban Gaji & Upah', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3]);

        $entries[] = [
            'account_id' => $expenseAccount->id,
            'debit' => $totalEarnings > 0 ? $totalEarnings : $totalNetSalary,
            'credit' => 0,
            'description' => "Beban Gaji & Upah Karyawan Periode {$payroll->month}/{$payroll->year}",
        ];

        // 2. Cr: Kas Kecil / Bank (1-1101) - Total Gaji Bersih (Take Home Pay)
        $cashAccount = ChartOfAccount::where('code', '1-1101')->first()
            ?? ChartOfAccount::firstOrCreate(['code' => '1-1101'], ['name' => 'Kas Kecil', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3]);

        $entries[] = [
            'account_id' => $cashAccount->id,
            'debit' => 0,
            'credit' => $totalNetSalary,
            'description' => "Pembayaran Gaji Bersih (THP) Karyawan Periode {$payroll->month}/{$payroll->year}",
        ];

        // 3. Cr: Hutang Gaji & Potongan Karyawan (2-1201) - Total Potongan (BPJS/Kasbon) jika ada
        if ($totalDeductions > 0) {
            $deductionAccount = ChartOfAccount::where('code', '2-1201')->first()
                ?? ChartOfAccount::firstOrCreate(['code' => '2-1201'], ['name' => 'Hutang Gaji & Potongan Karyawan', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3]);

            $entries[] = [
                'account_id' => $deductionAccount->id,
                'debit' => 0,
                'credit' => $totalDeductions,
                'description' => "Potongan BPJS / Kasbon / Denda Periode {$payroll->month}/{$payroll->year}",
            ];
        }

        $date = $payroll->processed_at ? $payroll->processed_at->toDateString() : now()->toDateString();

        return $this->autoPostJournal($payroll, $payroll->id, $entries, $date);
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

    /**
     * Post journal entry for an Operational Expense (Petty Cash / Daily Expenses).
     *
     * Dr. Beban Operasional (expense account selected by user)
     * Cr. Kas Kecil (1-1101) or Bank (1-1102)
     */
    public function postOperationalExpenseJournal(OperationalExpense $expense): Journal
    {
        $entries = [];

        // Dr: Beban Operasional (akun beban yang dipilih user, e.g. 5-2101 Listrik)
        $entries[] = [
            'account_id' => $expense->account_id,
            'debit' => $expense->amount,
            'credit' => 0,
            'description' => $expense->description,
        ];

        // Cr: Kas Kecil (cash) or Bank (transfer)
        if ($expense->payment_method === 'transfer') {
            $creditAccount = ChartOfAccount::where('code', '1-1102')->first()
                ?? ChartOfAccount::firstOrCreate(['code' => '1-1102'], ['name' => 'Bank', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3]);
        } else {
            $creditAccount = ChartOfAccount::where('code', '1-1101')->first()
                ?? ChartOfAccount::firstOrCreate(['code' => '1-1101'], ['name' => 'Kas Kecil', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3]);
        }

        $entries[] = [
            'account_id' => $creditAccount->id,
            'debit' => 0,
            'credit' => $expense->amount,
            'description' => "Pengeluaran kas: {$expense->description}",
        ];

        return $this->autoPostJournal($expense, $expense->id, $entries, $expense->expense_date?->toDateString());
    }

    /**
     * Post journal entry for Supplier Payment / AP Settlement.
     *
     * Dr. Hutang Usaha (2-1101)
     * Cr. Kas Kecil (1-1101) or Bank (1-1102)
     */
    public function postSupplierPaymentJournal(SupplierPayment $payment): Journal
    {
        $entries = [];

        // Dr: Hutang Usaha (2-1101)
        $payableAccount = ChartOfAccount::where('code', '2-1101')->first()
            ?? ChartOfAccount::firstOrCreate(['code' => '2-1101'], ['name' => 'Hutang Usaha', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3]);

        $entries[] = [
            'account_id' => $payableAccount->id,
            'debit' => $payment->amount,
            'credit' => 0,
            'description' => "Pelunasan hutang ke supplier {$payment->supplier?->name}" . ($payment->grn_id ? " (GRN #{$payment->goodsReceivedNote?->grn_number})" : ''),
        ];

        // Cr: Bank (transfer) or Kas Kecil (cash)
        if ($payment->payment_method === 'transfer') {
            $creditAccount = ChartOfAccount::where('code', '1-1102')->first()
                ?? ChartOfAccount::firstOrCreate(['code' => '1-1102'], ['name' => 'Bank', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3]);
        } else {
            $creditAccount = ChartOfAccount::where('code', '1-1101')->first()
                ?? ChartOfAccount::firstOrCreate(['code' => '1-1101'], ['name' => 'Kas Kecil', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3]);
        }

        $entries[] = [
            'account_id' => $creditAccount->id,
            'debit' => 0,
            'credit' => $payment->amount,
            'description' => "Pembayaran ke supplier {$payment->supplier?->name} via {$payment->payment_method}",
        ];

        return $this->autoPostJournal($payment, $payment->id, $entries, $payment->payment_date?->toDateString());
    }

    /**
     * Post journal entries for Cashier Shift Closing & Discrepancy Reconciliation.
     *
     * Handles:
     * 1. Petty cash expenses recorded during the shift (Dr. Beban Operasional / Cr. Kas Kecil)
     * 2. Cash Discrepancy (Selisih Kas):
     *    - Deficit (Fisik < Sistem): Dr. Beban Selisih Kas (5-9900) / Cr. Kas Kasir (1-1101)
     *    - Surplus (Fisik > Sistem): Dr. Kas Kasir (1-1101) / Cr. Pendapatan Selisih Kas (4-9900)
     */
    public function postShiftClosingJournal(\App\Models\CashierShift $shift): ?Journal
    {
        $diff = (float) $shift->cash_difference;
        $pettyCash = (float) $shift->petty_cash_total;

        if (abs($diff) < 0.01 && $pettyCash <= 0) {
            return null;
        }

        $entries = [];

        // 1. Record Petty Cash Expenses if any
        if ($pettyCash > 0) {
            $expenseAccount = ChartOfAccount::where('code', '5-2900')->first()
                ?? ChartOfAccount::where('type', 'expense')->first()
                ?? ChartOfAccount::firstOrCreate(['code' => '5-2900'], ['name' => 'Beban Operasional Kasir', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3]);

            $cashAccount = ChartOfAccount::where('code', '1-1101')->first()
                ?? ChartOfAccount::firstOrCreate(['code' => '1-1101'], ['name' => 'Kas Kecil', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3]);

            $entries[] = [
                'account_id' => $expenseAccount->id,
                'debit' => $pettyCash,
                'credit' => 0,
                'description' => "Pengeluaran Kas Kecil Shift #{$shift->id}",
            ];

            $entries[] = [
                'account_id' => $cashAccount->id,
                'debit' => 0,
                'credit' => $pettyCash,
                'description' => "Kredit Kas Kecil Pengeluaran Shift #{$shift->id}",
            ];
        }

        // 2. Record Cash Discrepancy (Surplus / Deficit)
        if (abs($diff) >= 0.01) {
            $cashAccount = ChartOfAccount::where('code', '1-1101')->first()
                ?? ChartOfAccount::firstOrCreate(['code' => '1-1101'], ['name' => 'Kas Kecil', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3]);

            if ($diff < 0) {
                // Deficit (Kas Kurang) -> Dr. Beban Selisih Kas (5-9900) / Cr. Kas Kasir (1-1101)
                $deficitAmount = abs($diff);
                $deficitAccount = ChartOfAccount::where('code', '5-9900')->first()
                    ?? ChartOfAccount::firstOrCreate(['code' => '5-9900'], ['name' => 'Beban Selisih Kas', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3]);

                $entries[] = [
                    'account_id' => $deficitAccount->id,
                    'debit' => $deficitAmount,
                    'credit' => 0,
                    'description' => "Defisit Selisih Kas Fisik Shift #{$shift->id}",
                ];
                $entries[] = [
                    'account_id' => $cashAccount->id,
                    'debit' => 0,
                    'credit' => $deficitAmount,
                    'description' => "Penyesuaian Defisit Kas Shift #{$shift->id}",
                ];
            } else {
                // Surplus (Kas Lebih) -> Dr. Kas Kasir (1-1101) / Cr. Pendapatan Selisih Kas (4-9900)
                $surplusAmount = $diff;
                $surplusAccount = ChartOfAccount::where('code', '4-9900')->first()
                    ?? ChartOfAccount::firstOrCreate(['code' => '4-9900'], ['name' => 'Pendapatan Selisih Kas', 'type' => 'revenue', 'normal_balance' => 'credit', 'level' => 3]);

                $entries[] = [
                    'account_id' => $cashAccount->id,
                    'debit' => $surplusAmount,
                    'credit' => 0,
                    'description' => "Penyesuaian Surplus Kas Shift #{$shift->id}",
                ];
                $entries[] = [
                    'account_id' => $surplusAccount->id,
                    'debit' => 0,
                    'credit' => $surplusAmount,
                    'description' => "Surplus Selisih Kas Fisik Shift #{$shift->id}",
                ];
            }
        }

        return $this->autoPostJournal($shift, $shift->id, $entries, $shift->closed_at?->toDateString());
    }
}
