<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalLine;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    /**
     * Get Trial Balance (Neraca Saldo)
     */
    public function getTrialBalance(?int $branchId, ?int $year, ?int $month): array
    {
        $query = JournalLine::query()
            ->join('journals', 'journal_lines.journal_id', '=', 'journals.id')
            ->join('chart_of_accounts', 'journal_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('journals.status', 'posted');

        if ($branchId) {
            $query->where('journals.branch_id', $branchId);
        }
        if ($year) {
            $query->whereYear('journals.date', $year);
        }
        if ($month) {
            $query->whereMonth('journals.date', $month);
        }

        $balances = $query->select(
            'chart_of_accounts.id',
            'chart_of_accounts.code',
            'chart_of_accounts.name',
            'chart_of_accounts.type',
            'chart_of_accounts.normal_balance',
            DB::raw('SUM(journal_lines.debit) as total_debit'),
            DB::raw('SUM(journal_lines.credit) as total_credit')
        )
        ->groupBy(
            'chart_of_accounts.id',
            'chart_of_accounts.code',
            'chart_of_accounts.name',
            'chart_of_accounts.type',
            'chart_of_accounts.normal_balance'
        )
        ->orderBy('chart_of_accounts.code')
        ->get();

        $totalDebit = 0;
        $totalCredit = 0;

        $rows = $balances->map(function ($row) use (&$totalDebit, &$totalCredit) {
            $debit = (float) $row->total_debit;
            $credit = (float) $row->total_credit;
            
            $netDebit = 0;
            $netCredit = 0;

            if ($row->normal_balance === 'debit') {
                $net = $debit - $credit;
                if ($net >= 0) {
                    $netDebit = $net;
                } else {
                    $netCredit = abs($net);
                }
            } else {
                $net = $credit - $debit;
                if ($net >= 0) {
                    $netCredit = $net;
                } else {
                    $netDebit = abs($net);
                }
            }

            $totalDebit += $netDebit;
            $totalCredit += $netCredit;

            return [
                'code' => $row->code,
                'name' => $row->name,
                'type' => $row->type,
                'debit' => $netDebit,
                'credit' => $netCredit,
            ];
        });

        return [
            'rows' => $rows,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }

    /**
     * Get Income Statement (Laporan Laba Rugi)
     */
    public function getIncomeStatement(?int $branchId, ?int $year, ?int $month): array
    {
        $tb = $this->getTrialBalance($branchId, $year, $month);
        
        $revenues = [];
        $expenses = [];
        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($tb['rows'] as $row) {
            if ($row['type'] === 'revenue') {
                $amount = $row['credit'] - $row['debit'];
                $revenues[] = [
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'amount' => $amount,
                ];
                $totalRevenue += $amount;
            } elseif ($row['type'] === 'expense') {
                $amount = $row['debit'] - $row['credit'];
                $expenses[] = [
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'amount' => $amount,
                ];
                $totalExpense += $amount;
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
     * Get Balance Sheet (Laporan Neraca)
     */
    public function getBalanceSheet(?int $branchId, ?int $year, ?int $month): array
    {
        $tb = $this->getTrialBalance($branchId, $year, $month);
        $incomeStatement = $this->getIncomeStatement($branchId, $year, $month);

        $assets = [];
        $liabilities = [];
        $equities = [];
        $totalAssets = 0;
        $totalLiabilities = 0;
        $totalEquities = 0;

        foreach ($tb['rows'] as $row) {
            if ($row['type'] === 'asset') {
                $amount = $row['debit'] - $row['credit'];
                $assets[] = ['code' => $row['code'], 'name' => $row['name'], 'amount' => $amount];
                $totalAssets += $amount;
            } elseif ($row['type'] === 'liability') {
                $amount = $row['credit'] - $row['debit'];
                $liabilities[] = ['code' => $row['code'], 'name' => $row['name'], 'amount' => $amount];
                $totalLiabilities += $amount;
            } elseif ($row['type'] === 'equity') {
                $amount = $row['credit'] - $row['debit'];
                $equities[] = ['code' => $row['code'], 'name' => $row['name'], 'amount' => $amount];
                $totalEquities += $amount;
            }
        }

        // Add Current Period Net Income to Equity
        $equities[] = [
            'code' => 'NET_INC',
            'name' => 'Laba Bersih Periode Berjalan',
            'amount' => $incomeStatement['net_income'],
        ];
        $totalEquities += $incomeStatement['net_income'];

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equities' => $equities,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equities' => $totalEquities,
            'total_liabilities_equity' => $totalLiabilities + $totalEquities,
            'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquities)) < 0.01,
        ];
    }
}
