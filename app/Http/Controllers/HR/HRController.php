<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HRController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);
        
        $branchId = $request->query('branch_id');
        if (!$isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $branches = Branch::orderBy('name')->get();

        $employees = Employee::with('branch')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->paginate(15);

        $payrolls = Payroll::with(['branch', 'createdByUser'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        return view('hr.index', compact('employees', 'payrolls', 'branches', 'branchId'));
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'nik' => 'required|string|unique:employees,nik',
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'base_salary' => 'required|numeric|min:0',
            'branch_id' => 'required|exists:branches,id',
        ]);

        Employee::create([
            'nik' => $request->nik,
            'name' => $request->name,
            'position' => $request->position,
            'base_salary' => $request->base_salary,
            'branch_id' => $request->branch_id,
            'is_active' => true,
            'joined_at' => now(),
        ]);

        return redirect()->route('hr.index')->with('success', 'Karyawan baru berhasil ditambahkan!');
    }

    public function storePayroll(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2024',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $existing = Payroll::where('branch_id', $request->branch_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Payroll untuk periode tersebut sudah diproses!');
        }

        DB::transaction(function () use ($request) {
            $payroll = Payroll::create([
                'branch_id' => $request->branch_id,
                'month' => $request->month,
                'year' => $request->year,
                'status' => 'draft',
                'created_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            $employees = Employee::where('branch_id', $request->branch_id)->where('is_active', true)->get();

            foreach ($employees as $emp) {
                // Calculate attendances for that month
                $attendanceDays = Attendance::where('employee_id', $emp->id)
                    ->whereYear('date', $request->year)
                    ->whereMonth('date', $request->month)
                    ->where('status', 'present')
                    ->count();

                $workDays = 26; // Default work days per month
                $proRataRatio = $attendanceDays > 0 ? min(1, $attendanceDays / $workDays) : 1;
                $netSalary = round($emp->base_salary * $proRataRatio);

                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $emp->id,
                    'base_salary' => $emp->base_salary,
                    'allowance' => 0,
                    'deduction' => 0,
                    'attendance_days' => $attendanceDays ?: 26,
                    'work_days' => $workDays,
                    'net_salary' => $netSalary,
                ]);
            }
        });

        return redirect()->route('hr.index')->with('success', 'Payroll periode berhasil digenerate!');
    }

    public function showPayslip(PayrollItem $item)
    {
        $item->load(['employee', 'payroll.branch']);
        return view('hr.payslip', compact('item'));
    }
}
