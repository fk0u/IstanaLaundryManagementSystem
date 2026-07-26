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
}
