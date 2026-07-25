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

        $payrolls = Payroll::with(['branch', 'createdByUser', 'items.employee'])
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

        try {
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

                    // Calculate attendance bonus (100% attendance = bonus)
                    $attendanceBonus = 0;
                    if ($attendanceDays >= $workDays) {
                        $attendanceBonus = $emp->base_salary * 0.05; // 5% bonus for perfect attendance
                    }

                    // Calculate tardiness deduction (if any late records)
                    $tardinessDeduction = 0;
                    $lateDays = Attendance::where('employee_id', $emp->id)
                        ->whereYear('date', $request->year)
                        ->whereMonth('date', $request->month)
                        ->where('status', 'late')
                        ->count();
                    if ($lateDays > 0) {
                        $tardinessDeduction = $lateDays * 25000; // Rp25.000 per late day
                    }

                    // Create payroll item with comprehensive components
                    $payrollItem = PayrollItem::create([
                        'payroll_id' => $payroll->id,
                        'employee_id' => $emp->id,
                        'base_salary' => $emp->base_salary,
                        'allowance' => 0,
                        'deduction' => 0,
                        'attendance_days' => $attendanceDays ?: 26,
                        'work_days' => $workDays,
                        // Earnings components
                        'bonus_kg' => 0, // To be calculated based on actual workload
                        'bonus_pcs' => 0, // To be calculated based on special items
                        'transport_allowance' => 0, // Manual input or policy-based
                        'overtime_pay' => 0, // To be calculated based on overtime hours
                        'attendance_bonus' => $attendanceBonus,
                        // Deductions components
                        'tardiness_deduction' => $tardinessDeduction,
                        'loan_deduction' => 0, // Manual input from employee loans
                        'damage_deduction' => 0, // Manual input from damage claims
                        'bpjs_deduction' => 0, // Policy-based or manual input
                        'net_salary' => $netSalary, // Will be recalculated
                    ]);

                    // Auto-calculate totals
                    $payrollItem->saveCalculations();
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal generate payroll: ' . $e->getMessage());
        }

        return redirect()->route('hr.index')->with('success', 'Payroll periode berhasil digenerate!');
    }

    public function showPayslip(PayrollItem $item)
    {
        $item->load(['employee', 'payroll.branch']);
        return view('hr.payslip', compact('item'));
    }

    public function updatePayrollItem(Request $request, PayrollItem $item)
    {
        $request->validate([
            'bonus_kg' => 'nullable|numeric|min:0',
            'bonus_pcs' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'attendance_bonus' => 'nullable|numeric|min:0',
            'tardiness_deduction' => 'nullable|numeric|min:0',
            'loan_deduction' => 'nullable|numeric|min:0',
            'damage_deduction' => 'nullable|numeric|min:0',
            'bpjs_deduction' => 'nullable|numeric|min:0',
        ]);

        $item->update([
            'bonus_kg' => $request->bonus_kg ?? 0,
            'bonus_pcs' => $request->bonus_pcs ?? 0,
            'transport_allowance' => $request->transport_allowance ?? 0,
            'overtime_pay' => $request->overtime_pay ?? 0,
            'attendance_bonus' => $request->attendance_bonus ?? 0,
            'tardiness_deduction' => $request->tardiness_deduction ?? 0,
            'loan_deduction' => $request->loan_deduction ?? 0,
            'damage_deduction' => $request->damage_deduction ?? 0,
            'bpjs_deduction' => $request->bpjs_deduction ?? 0,
        ]);

        // Recalculate totals
        $item->saveCalculations();

        return redirect()->back()->with('success', 'Komponen payroll berhasil diperbarui!');
    }

    public function destroyPayroll(Payroll $payroll)
    {
        try {
            // Delete all related payroll items first (cascade delete)
            $payroll->items()->delete();
            
            // Delete the payroll record
            $payroll->delete();
            
            return redirect()->route('hr.index')->with('success', 'Riwayat payroll berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('hr.index')->with('error', 'Gagal menghapus payroll: ' . $e->getMessage());
        }
    }
}
