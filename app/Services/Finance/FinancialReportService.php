<?php

namespace App\Services\Finance;

use App\Models\ChartOfAccount;
use App\Models\JournalLine;

class FinancialReportService
{
    /**
     * Get trial balance for a branch.
     */
    public function getTrialBalance(?int $branchId, int $year, int $month): array
    {
        $coas = ChartOfAccount::orderBy('code', 'asc')->get();
        $trialBalance = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($coas as $coa) {
            // Sum debit and credit from posted journals in this period
            $linesQuery = JournalLine::whereHas('journal', function ($query) use ($branchId, $year, $month) {
                $query->where('status', 'posted')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month);
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
            })->where('account_id', $coa->id);

            $debitSum = (float) $linesQuery->sum('debit');
            $creditSum = (float) $linesQuery->sum('credit');

            $balance = 0;
            if ($coa->normal_balance === 'debit') {
                $balance = $debitSum - $creditSum;
            } else {
                $balance = $creditSum - $debitSum;
            }

            if ($debitSum > 0 || $creditSum > 0) {
                $trialBalance[] = [
                    'account' => $coa,
                    'debit' => $debitSum,
                    'credit' => $creditSum,
                    'balance' => $balance,
                ];

                $totalDebit += $debitSum;
                $totalCredit += $creditSum;
            }
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
        $coas = ChartOfAccount::whereIn('type', ['revenue', 'expense'])->orderBy('code', 'asc')->get();
        $revenues = [];
        $expenses = [];
        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($coas as $coa) {
            $linesQuery = JournalLine::whereHas('journal', function ($query) use ($branchId, $year, $month) {
                $query->where('status', 'posted')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month);
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
            })->where('account_id', $coa->id);

            $debitSum = (float) $linesQuery->sum('debit');
            $creditSum = (float) $linesQuery->sum('credit');

            if ($coa->type === 'revenue') {
                $balance = $creditSum - $debitSum;
                if ($balance != 0) {
                    $revenues[] = ['account' => $coa, 'balance' => $balance];
                    $totalRevenue += $balance;
                }
            } else {
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
        $coas = ChartOfAccount::whereIn('type', ['asset', 'liability', 'equity'])->orderBy('code', 'asc')->get();
        $assets = [];
        $liabilities = [];
        $equity = [];
        $totalAsset = 0;
        $totalLiability = 0;
        $totalEquity = 0;

        // Get net income dynamically for current period
        $incomeStatement = $this->getIncomeStatement($branchId, $year, $month);
        $currentNetIncome = $incomeStatement['net_income'];

        foreach ($coas as $coa) {
            $linesQuery = JournalLine::whereHas('journal', function ($query) use ($branchId, $year, $month) {
                $query->where('status', 'posted')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month);
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
            })->where('account_id', $coa->id);

            $debitSum = (float) $linesQuery->sum('debit');
            $creditSum = (float) $linesQuery->sum('credit');

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
