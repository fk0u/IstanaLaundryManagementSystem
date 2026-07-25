<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\Finance\FinancialReportService;
use App\Models\Branch;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    protected $reportService;

    public function __construct(FinancialReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $branchId = $request->input('branch_id');

        // Check if user is restricted to a branch (using auth user's branch_id)
        if (auth()->user() && !auth()->user()->hasRole(['Developer', 'Owner', 'Super Admin'])) {
            $branchId = auth()->user()->branch_id;
        }

        $trialBalance = $this->reportService->getTrialBalance($branchId, $year, $month);
        $incomeStatement = $this->reportService->getIncomeStatement($branchId, $year, $month);
        $balanceSheet = $this->reportService->getBalanceSheet($branchId, $year, $month);

        $branches = Branch::orderBy('name', 'asc')->get();

        return view('finance.reports.index', compact(
            'trialBalance',
            'incomeStatement',
            'balanceSheet',
            'year',
            'month',
            'branchId',
            'branches'
        ));
    }
}
