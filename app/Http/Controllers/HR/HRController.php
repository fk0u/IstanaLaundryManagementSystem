<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HRController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id');
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $branches = Branch::orderBy('name')->get();

        $employees = Employee::with('branch')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->paginate(15);

        // withoutBranchScope prevents global scope from hiding payrolls
        // when a non-scoped user (Owner/Developer) accesses /hr without session branch scope.
        $payrolls = Payroll::withoutBranchScope()
            ->with(['branch', 'createdByUser', 'items.employee'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
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

                    // Calculate transport allowance automatically from attendance (Rp 15.000 / present day)
                    $presentCount = $attendanceDays ?: 26;
                    $transportAllowance = $presentCount * 15000;

                    // BPJS split calculations (Indonesian Standard: BPJS Kesehatan 1%, BPJS Ketenagakerjaan JHT 2%)
                    $bpjsKesehatan = round($emp->base_salary * 0.01);
                    $bpjsKetenagakerjaan = round($emp->base_salary * 0.02);

                    // Auto-calculate workload performance bonus from actual completed workshop order items
                    $totalWorkloadKg = \App\Models\OrderItem::whereHas('order', function ($q) use ($emp, $request) {
                        $q->where('branch_id', $emp->branch_id)
                          ->whereYear('created_at', $request->year)
                          ->whereMonth('created_at', $request->month)
                          ->whereNotIn('production_status', ['BATAL']);
                    })->whereHas('service', function ($s) {
                        $s->where('unit', 'Kg');
                    })->sum('quantity');

                    $totalWorkloadPcs = \App\Models\OrderItem::whereHas('order', function ($q) use ($emp, $request) {
                        $q->where('branch_id', $emp->branch_id)
                          ->whereYear('created_at', $request->year)
                          ->whereMonth('created_at', $request->month)
                          ->whereNotIn('production_status', ['BATAL']);
                    })->whereHas('service', function ($s) {
                        $s->where('unit', 'Pcs');
                    })->sum('quantity');

                    // Incentive rates: Rp 200/Kg & Rp 500/Pcs for workshop staff
                    $bonusKg = ($emp->position === 'Operator Workshop') ? round($totalWorkloadKg * 200) : 0;
                    $bonusPcs = ($emp->position === 'Operator Workshop') ? round($totalWorkloadPcs * 500) : 0;

                    // Create payroll item with comprehensive components
                    $payrollItem = PayrollItem::create([
                        'payroll_id' => $payroll->id,
                        'employee_id' => $emp->id,
                        'base_salary' => $emp->base_salary,
                        'allowance' => 0,
                        'deduction' => 0,
                        'attendance_days' => $presentCount,
                        'work_days' => $workDays,
                        // Earnings components
                        'bonus_kg' => $bonusKg,
                        'bonus_pcs' => $bonusPcs,
                        'transport_allowance' => $transportAllowance, // Auto Rp15k/day
                        'overtime_pay' => 0,
                        'attendance_bonus' => $attendanceBonus,
                        'special_bonus' => 0,
                        // Deductions components
                        'tardiness_deduction' => $tardinessDeduction,
                        'loan_deduction' => 0,
                        'damage_deduction' => 0,
                        'bpjs_deduction' => 0,
                        'bpjs_kesehatan_deduction' => $bpjsKesehatan,
                        'bpjs_ketenagakerjaan_deduction' => $bpjsKetenagakerjaan,
                        'net_salary' => $emp->base_salary,
                    ]);

                    // Auto-calculate totals
                    $payrollItem->saveCalculations();
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal generate payroll: '.$e->getMessage());
        }

        return redirect()->route('hr.index')->with('success', 'Payroll periode berhasil digenerate!');
    }

    public function showPayslip(PayrollItem $item)
    {
        $item->load(['employee', 'payroll.branch']);

        return view('hr.payslip', compact('item'));
    }

    /**
     * Resolve a Payroll bypassing BranchScoped global scope.
     * Required because route model binding uses the global scope,
     * causing 404 when the session branch scope does not match the payroll.
     */
    protected function resolvePayroll(int $id): Payroll
    {
        return Payroll::withoutBranchScope()->findOrFail($id);
    }

    public function showPayroll(int $payroll)
    {
        $payroll = $this->resolvePayroll($payroll);
        $payroll->load(['branch', 'createdByUser', 'items.employee']);

        return view('hr.payroll-detail', compact('payroll'));
    }

    public function updatePayrollItem(Request $request, PayrollItem $item)
    {
        $request->validate([
            'allowance' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'bonus_kg' => 'nullable|numeric|min:0',
            'bonus_pcs' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'attendance_bonus' => 'nullable|numeric|min:0',
            'special_bonus' => 'nullable|numeric|min:0',
            'tardiness_deduction' => 'nullable|numeric|min:0',
            'loan_deduction' => 'nullable|numeric|min:0',
            'damage_deduction' => 'nullable|numeric|min:0',
            'bpjs_deduction' => 'nullable|numeric|min:0',
            'bpjs_kesehatan_deduction' => 'nullable|numeric|min:0',
            'bpjs_ketenagakerjaan_deduction' => 'nullable|numeric|min:0',
        ]);

        $oldValues = $item->only([
            'allowance',
            'deduction',
            'bonus_kg',
            'bonus_pcs',
            'transport_allowance',
            'overtime_pay',
            'attendance_bonus',
            'tardiness_deduction',
            'loan_deduction',
            'damage_deduction',
            'bpjs_deduction',
            'total_earnings',
            'total_deductions',
            'net_salary',
        ]);

        $item->update([
            'allowance' => $request->allowance ?? 0,
            'deduction' => $request->deduction ?? 0,
            'bonus_kg' => $request->bonus_kg ?? 0,
            'bonus_pcs' => $request->bonus_pcs ?? 0,
            'transport_allowance' => $request->transport_allowance ?? 0,
            'overtime_pay' => $request->overtime_pay ?? 0,
            'attendance_bonus' => $request->attendance_bonus ?? 0,
            'special_bonus' => $request->special_bonus ?? 0,
            'tardiness_deduction' => $request->tardiness_deduction ?? 0,
            'loan_deduction' => $request->loan_deduction ?? 0,
            'damage_deduction' => $request->damage_deduction ?? 0,
            'bpjs_deduction' => $request->bpjs_deduction ?? 0,
            'bpjs_kesehatan_deduction' => $request->bpjs_kesehatan_deduction ?? 0,
            'bpjs_ketenagakerjaan_deduction' => $request->bpjs_ketenagakerjaan_deduction ?? 0,
        ]);

        // Recalculate totals
        $item->saveCalculations();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'update',
            'model_type' => PayrollItem::class,
            'model_id' => $item->id,
            'old_values' => $oldValues,
            'new_values' => $item->only([
                'allowance',
                'deduction',
                'bonus_kg',
                'bonus_pcs',
                'transport_allowance',
                'overtime_pay',
                'attendance_bonus',
                'tardiness_deduction',
                'loan_deduction',
                'damage_deduction',
                'bpjs_deduction',
                'total_earnings',
                'total_deductions',
                'net_salary',
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Komponen payroll berhasil diperbarui!');
    }

    public function destroyPayroll(int $payroll)
    {
        try {
            // Bypass BranchScoped global scope to ensure record is always found
            $payroll = $this->resolvePayroll($payroll);
            $payroll->load(['branch', 'items.employee']);
            $snapshot = [
                'payroll' => [
                    'id' => $payroll->id,
                    'branch_id' => $payroll->branch_id,
                    'branch_name' => $payroll->branch?->name,
                    'month' => $payroll->month,
                    'year' => $payroll->year,
                    'status' => $payroll->status,
                    'processed_at' => optional($payroll->processed_at)->toDateTimeString(),
                    'items_count' => $payroll->items->count(),
                    'total_net_salary' => $payroll->items->sum('net_salary'),
                ],
                'items' => $payroll->items->map(fn (PayrollItem $item) => [
                    'id' => $item->id,
                    'employee_id' => $item->employee_id,
                    'employee_name' => $item->employee?->name,
                    'net_salary' => $item->net_salary,
                ])->values()->all(),
            ];

            $payrollId = $payroll->id;

            // Delete all related payroll items first (cascade delete)
            $payroll->items()->delete();

            // Delete the payroll record
            $payroll->delete();

            // AuditLog write — wrapped separately so failure won't break the delete flow
            try {
                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'delete',
                    'model_type' => Payroll::class,
                    'model_id' => $payrollId,
                    'old_values' => $snapshot,
                    'new_values' => null,
                    'ip_address' => request()->ip(),
                    'user_agent' => (string) request()->userAgent(),
                ]);
            } catch (\Exception $auditEx) {
                // Audit log failure is non-fatal — log it but don't block the user
                Log::warning('AuditLog write failed after payroll deletion: '.$auditEx->getMessage());
            }

            return redirect()->route('hr.index')->with('success', 'Riwayat payroll berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('hr.index')->with('error', 'Gagal menghapus payroll: '.$e->getMessage());
        }
    }
}
