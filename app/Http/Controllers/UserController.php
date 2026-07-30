<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of system users.
     */
    public function index(Request $request)
    {
        $search = trim($request->query('q', ''));
        $roleFilter = $request->query('role');
        $branchFilter = $request->query('branch_id');

        $users = User::with(['branch', 'roles', 'employee'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                });
            })
            ->when($branchFilter, function ($q) use ($branchFilter) {
                $q->where('branch_id', $branchFilter);
            })
            ->when($roleFilter, function ($q) use ($roleFilter) {
                $q->role($roleFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $branches = Branch::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('users.index', compact('users', 'branches', 'roles', 'search', 'roleFilter', 'branchFilter'));
    }

    /**
     * Store a newly created user (Admin internal creation) and sync to HR Employees.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'branch_id' => 'required|exists:branches,id',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'branch_id' => $validated['branch_id'],
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        $user->assignRole($validated['role']);

        // Auto-sync matching HR Employee record
        $positionName = match ($validated['role']) {
            'Cashier' => 'Kasir Utama',
            'Workshop_Staff' => 'Operator Workshop',
            'Workshop_Admin' => 'Admin Workshop',
            'Branch_Admin' => 'Admin Cabang',
            'Finance' => 'Staf Keuangan',
            'CS_Marketing' => 'Staf CS & Marketing',
            default => 'Staf Operational',
        };

        \App\Models\Employee::withoutGlobalScopes()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'nik' => 'NIK-STF-'.str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'name' => $user->name,
                'position' => $positionName,
                'branch_id' => $user->branch_id,
                'base_salary' => 3000000.00,
                'is_active' => $user->is_active,
                'joined_at' => now()->toDateString(),
            ]
        );

        return redirect()->back()->with('success', "Pengguna staf {$user->name} berhasil ditambahkan dan tersinkronisasi ke HR!");
    }

    /**
     * Update existing user information, role, and sync to HR Employee.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'branch_id' => 'required|exists:branches,id',
            'role' => 'required|exists:roles,name',
            'is_active' => 'required|boolean',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'branch_id' => $validated['branch_id'],
            'is_active' => $validated['is_active'],
        ]);

        // Sync Spatie role
        $user->syncRoles([$validated['role']]);

        // Sync HR Employee record
        $positionName = match ($validated['role']) {
            'Cashier' => 'Kasir Utama',
            'Workshop_Staff' => 'Operator Workshop',
            'Workshop_Admin' => 'Admin Workshop',
            'Branch_Admin' => 'Admin Cabang',
            'Finance' => 'Staf Keuangan',
            'CS_Marketing' => 'Staf CS & Marketing',
            default => 'Staf Operational',
        };

        if ($user->employee) {
            $user->employee->update([
                'name' => $validated['name'],
                'branch_id' => $validated['branch_id'],
                'position' => $positionName,
                'is_active' => $validated['is_active'],
            ]);
        } else {
            \App\Models\Employee::withoutGlobalScopes()->create([
                'user_id' => $user->id,
                'nik' => 'NIK-STF-'.str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'name' => $user->name,
                'position' => $positionName,
                'branch_id' => $user->branch_id,
                'base_salary' => 3000000.00,
                'is_active' => $user->is_active,
                'joined_at' => now()->toDateString(),
            ]);
        }

        return redirect()->back()->with('success', "Data pengguna {$user->name} berhasil diperbarui dan tersinkronisasi ke HR.");
    }

    /**
     * Reset password for user.
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', "Password untuk pengguna {$user->name} berhasil di-reset!");
    }

    /**
     * Remove user from database.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
