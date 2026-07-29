<?php

namespace App\Services\Finance;

use App\Models\ChartOfAccount;
use App\Models\JournalLine;
use Illuminate\Support\Collection;

class FinancialReportService
{
    /**
     * Sum debit/credit per account for a given period in a single grouped
     * query, instead of one query per chart-of-account entry.
     *
     * @return Collection<int, array{account_id: int, debit: float, credit: float}>
     */
    protected function periodBalancesByAccount(?int $branchId, int $year, int $month): Collection
    {
        return JournalLine::query()
            ->selectRaw('journal_lines.account_id, SUM(journal_lines.debit) as debit, SUM(journal_lines.credit) as credit')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->where('journals.status', 'posted')
            ->whereYear('journals.date', $year)
            ->whereMonth('journals.date', $month)
            ->when($branchId, fn ($q) => $q->where('journals.branch_id', $branchId))
            ->groupBy('journal_lines.account_id')
            ->get();
    }

    /**
     * Get trial balance for a branch.
     */
    public function getTrialBalance(?int $branchId, int $year, int $month): array
    {
        $coas = ChartOfAccount::orderBy('code', 'asc')->get()->keyBy('id');
        $balances = $this->periodBalancesByAccount($branchId, $year, $month);

        $trialBalance = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($balances as $row) {
            $coa = $coas->get($row->account_id);
            if (! $coa) {
                continue;
            }

            $debitSum = (float) $row->debit;
            $creditSum = (float) $row->credit;
            $balance = $coa->normal_balance === 'debit'
                ? $debitSum - $creditSum
                : $creditSum - $debitSum;

            $trialBalance[] = [
                'account' => $coa,
                'debit' => $debitSum,
                'credit' => $creditSum,
                'balance' => $balance,
            ];

            $totalDebit += $debitSum;
            $totalCredit += $creditSum;
        }

        return [
            'lines' => $trialBalance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }

    /**
     * Get income statement for a branch.
     */
    public function getIncomeStatement(?int $branchId, int $year, int $month): array
    {
        $coas = ChartOfAccount::whereIn('type', ['revenue', 'expense'])
            ->orderBy('code', 'asc')
            ->get()
            ->keyBy('id');
        $balances = $this->periodBalancesByAccount($branchId, $year, $month);

        $revenues = [];
        $expenses = [];
        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($balances as $row) {
            $coa = $coas->get($row->account_id);
            if (! $coa) {
                continue;
            }

            $debitSum = (float) $row->debit;
            $creditSum = (float) $row->credit;

            if ($coa->type === 'revenue') {
                $balance = $creditSum - $debitSum;
                if ($balance != 0) {
                    $revenues[] = ['account' => $coa, 'balance' => $balance];
                    $totalRevenue += $balance;
                }
            } else { // expense
                $balance = $debitSum - $creditSum;
                if ($balance != 0) {
                    $expenses[] = ['account' => $coa, 'balance' => $balance];
                    $totalExpense += $balance;
                }
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
     * Get balance sheet for a branch.
     */
    public function getBalanceSheet(?int $branchId, int $year, int $month): array
    {
        $coas = ChartOfAccount::whereIn('type', ['asset', 'liability', 'equity'])
            ->orderBy('code', 'asc')
            ->get()
            ->keyBy('id');
        $balances = $this->periodBalancesByAccount($branchId, $year, $month);

        // Get net income dynamically for current period
        $incomeStatement = $this->getIncomeStatement($branchId, $year, $month);
        $currentNetIncome = $incomeStatement['net_income'];

        $assets = [];
        $liabilities = [];
        $equity = [];
        $totalAsset = 0;
        $totalLiability = 0;
        $totalEquity = 0;

        foreach ($balances as $row) {
            $coa = $coas->get($row->account_id);
            if (! $coa) {
                continue;
            }

            $debitSum = (float) $row->debit;
            $creditSum = (float) $row->credit;

            if ($coa->type === 'asset') {
                $balance = $debitSum - $creditSum;
                if ($balance != 0) {
                    $assets[] = ['account' => $coa, 'balance' => $balance];
                    $totalAsset += $balance;
                }
            } elseif ($coa->type === 'liability') {
                $balance = $creditSum - $debitSum;
                if ($balance != 0) {
                    $liabilities[] = ['account' => $coa, 'balance' => $balance];
                    $totalLiability += $balance;
                }
            } else { // equity
                $balance = $creditSum - $debitSum;
                // Add current net income to "Laba/Rugi Tahun Berjalan" dynamically
                if ($coa->code === '3-1301') {
                    $balance += $currentNetIncome;
                }
                if ($balance != 0) {
                    $equity[] = ['account' => $coa, 'balance' => $balance];
                    $totalEquity += $balance;
                }
            }
        }

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_asset' => $totalAsset,
            'total_liability' => $totalLiability,
            'total_equity' => $totalEquity + $currentNetIncome,
            'current_net_income' => $currentNetIncome,
        ];
    }
}
