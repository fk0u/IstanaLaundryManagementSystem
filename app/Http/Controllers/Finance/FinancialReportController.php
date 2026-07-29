<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\FinancialReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialReportController extends Controller
{
    protected FinancialReportService $reportService;

    public function __construct(FinancialReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id');
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $year = (int) ($request->query('year') ?? date('Y'));
        $month = $request->query('month') ? (int) $request->query('month') : null;
        $tab = $request->query('tab', 'income');

        $branches = Branch::orderBy('name')->get();

        $trialBalance = $this->reportService->getTrialBalance($branchId, $year, $month);
        $incomeStatement = $this->reportService->getIncomeStatement($branchId, $year, $month);
        $balanceSheet = $this->reportService->getBalanceSheet($branchId, $year, $month);

        return view('finance.reports', compact(
            'trialBalance',
            'incomeStatement',
            'balanceSheet',
            'branches',
            'branchId',
            'year',
            'month',
            'tab'
        ));
    }

    /**
     * Export report data to Excel (.csv / .xls format)
     */
    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id');
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $year = (int) ($request->query('year') ?? date('Y'));
        $month = $request->query('month') ? (int) $request->query('month') : null;
        $tab = $request->query('tab', 'income');

        $branchName = $branchId ? (Branch::find($branchId)?->name ?? 'Cabang Specific') : 'Konsolidasi Seluruh Cabang';
        $periodLabel = $month ? date('F Y', mktime(0, 0, 0, $month, 10, $year)) : "Tahun {$year}";

        $filename = "Financial_Report_{$tab}_{$year}".($month ? "_{$month}" : '').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $reportService = $this->reportService;

        $callback = function () use ($reportService, $branchId, $year, $month, $tab, $branchName, $periodLabel) {
            $file = fopen('php://output', 'w');
            // BOM for UTF-8 Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            if ($tab === 'income') {
                $income = $reportService->getIncomeStatement($branchId, $year, $month);
                fputcsv($file, ['ISTANA LAUNDRY ERP — LAPORAN LABA RUGI (INCOME STATEMENT)']);
                fputcsv($file, ["Cabang: {$branchName}", "Periode: {$periodLabel}"]);
                fputcsv($file, []);
                fputcsv($file, ['KODE AKUN', 'NAMA AKUN', 'KATEGORI', 'NILAI (RP)']);
                
                fputcsv($file, ['--- PENDAPATAN OPERASIONAL ---']);
                foreach ($income['revenues'] as $rev) {
                    fputcsv($file, [$rev['code'], $rev['name'], 'Pendapatan', number_format($rev['amount'], 2, '.', '')]);
                }
                fputcsv($file, ['', 'TOTAL PENDAPATAN', '', number_format($income['total_revenue'], 2, '.', '')]);
                fputcsv($file, []);

                fputcsv($file, ['--- BEBAN OPERASIONAL ---']);
                foreach ($income['expenses'] as $exp) {
                    fputcsv($file, [$exp['code'], $exp['name'], 'Beban', number_format($exp['amount'], 2, '.', '')]);
                }
                fputcsv($file, ['', 'TOTAL BEBAN', '', number_format($income['total_expense'], 2, '.', '')]);
                fputcsv($file, []);
                fputcsv($file, ['', 'LABA (RUGI) BERSIH', '', number_format($income['net_income'], 2, '.', '')]);

            } elseif ($tab === 'balance') {
                $balance = $reportService->getBalanceSheet($branchId, $year, $month);
                fputcsv($file, ['ISTANA LAUNDRY ERP — LAPORAN NERACA (BALANCE SHEET)']);
                fputcsv($file, ["Cabang: {$branchName}", "Periode: {$periodLabel}"]);
                fputcsv($file, []);

                fputcsv($file, ['KODE AKUN', 'NAMA AKUN', 'KELOMPOK', 'NILAI (RP)']);
                fputcsv($file, ['--- AKTIVA (ASSETS) ---']);
                foreach ($balance['assets'] as $ast) {
                    fputcsv($file, [$ast['code'], $ast['name'], 'Aktiva', number_format($ast['amount'], 2, '.', '')]);
                }
                fputcsv($file, ['', 'TOTAL AKTIVA', '', number_format($balance['total_assets'], 2, '.', '')]);
                fputcsv($file, []);

                fputcsv($file, ['--- PASIVA (PASIVA & MODAL) ---']);
                foreach ($balance['liabilities'] as $liab) {
                    fputcsv($file, [$liab['code'], $liab['name'], 'Kewajiban', number_format($liab['amount'], 2, '.', '')]);
                }
                foreach ($balance['equities'] as $eq) {
                    fputcsv($file, [$eq['code'], $eq['name'], 'Ekuitas', number_format($eq['amount'], 2, '.', '')]);
                }
                fputcsv($file, ['', 'TOTAL PASIVA', '', number_format($balance['total_liabilities_equity'], 2, '.', '')]);

            } else {
                $trial = $reportService->getTrialBalance($branchId, $year, $month);
                fputcsv($file, ['ISTANA LAUNDRY ERP — NERACA SALDO (TRIAL BALANCE)']);
                fputcsv($file, ["Cabang: {$branchName}", "Periode: {$periodLabel}"]);
                fputcsv($file, []);
                fputcsv($file, ['KODE AKUN', 'NAMA AKUN', 'KATEGORI', 'DEBIT (RP)', 'KREDIT (RP)']);
                foreach ($trial['rows'] as $row) {
                    fputcsv($file, [$row['code'], $row['name'], $row['type'], number_format($row['debit'], 2, '.', ''), number_format($row['credit'], 2, '.', '')]);
                }
                fputcsv($file, ['', 'TOTAL', '', number_format($trial['total_debit'], 2, '.', ''), number_format($trial['total_credit'], 2, '.', '')]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display PowerBI Executive PDF Dashboard Report View.
     */
    public function exportPowerBiPdf(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id');
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $year = (int) ($request->query('year') ?? date('Y'));
        $month = $request->query('month') ? (int) $request->query('month') : null;

        $selectedBranch = $branchId ? Branch::find($branchId) : null;
        $branches = Branch::orderBy('name')->get();

        $trialBalance = $this->reportService->getTrialBalance($branchId, $year, $month);
        $incomeStatement = $this->reportService->getIncomeStatement($branchId, $year, $month);
        $balanceSheet = $this->reportService->getBalanceSheet($branchId, $year, $month);

        return view('finance.powerbi_pdf', compact(
            'trialBalance',
            'incomeStatement',
            'balanceSheet',
            'branches',
            'selectedBranch',
            'branchId',
            'year',
            'month'
        ));
    }
}
