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

        $users = User::with(['branch', 'roles'])
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
     * Store a newly created user (Admin internal creation).
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

        return redirect()->back()->with('success', "Pengguna staf {$user->name} berhasil ditambahkan!");
    }

    /**
     * Update existing user information and role.
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

        return redirect()->back()->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
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
