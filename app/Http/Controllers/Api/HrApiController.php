<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HrApiController extends Controller
{
    public function employees(Request $request)
    {
        $query = Employee::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('name', 'asc')->paginate($request->query('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $employees,
        ]);
    }

    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nik' => 'required|string|unique:employees,nik',
            'position' => 'required|string|max:255',
            'base_salary' => 'required|numeric|min:0',
            'joined_at' => 'required|date',
        ]);

        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id ?? 1;

        $employee = Employee::create([
            'branch_id' => $branchId,
            'name' => $validated['name'],
            'nik' => $validated['nik'],
            'position' => $validated['position'],
            'base_salary' => $validated['base_salary'],
            'joined_at' => $validated['joined_at'],
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data karyawan baru berhasil ditambahkan!',
            'data' => $employee,
        ], 201);
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'base_salary' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        $employee->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Data karyawan berhasil diperbarui!',
            'data' => $employee,
        ]);
    }

    public function payrolls(Request $request)
    {
        $payrolls = Payroll::with(['branch', 'items.employee'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 10));

        return response()->json([
            'status' => 'success',
            'data' => $payrolls,
        ]);
    }

    public function storePayroll(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|string', // e.g. "2026-08"
        ]);

        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id ?? 1;
        $employees = Employee::where('is_active', true)->where('branch_id', $branchId)->get();

        if ($employees->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada karyawan aktif pada cabang ini.',
            ], 422);
        }

        return DB::transaction(function () use ($validated, $branchId, $employees) {
            $totalAmount = $employees->sum('base_salary');

            $payroll = Payroll::create([
                'branch_id' => $branchId,
                'period' => $validated['period'],
                'total_amount' => $totalAmount,
                'status' => 'draft',
                'created_by' => auth()->id() ?? 1,
            ]);

            foreach ($employees as $emp) {
                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $emp->id,
                    'base_salary' => $emp->base_salary,
                    'allowance' => 0,
                    'deduction' => 0,
                    'net_salary' => $emp->base_salary,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan Penggajian berhasil di-generate!',
                'data' => $payroll->load('items.employee'),
            ], 201);
        });
    }
}
