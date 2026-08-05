<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserApiController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with(['branch', 'roles'])
            ->orderBy('name', 'asc')
            ->paginate($request->query('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'branch_id' => 'required|exists:branches,id',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'branch_id' => $validated['branch_id'],
        ]);

        $user->assignRole($validated['role']);

        return response()->json([
            'status' => 'success',
            'message' => 'Akun staf baru berhasil ditambahkan!',
            'data' => $user->load(['branch', 'roles']),
        ], 201);
    }

    public function show(User $user)
    {
        $user->load(['branch', 'roles', 'permissions']);

        return response()->json([
            'status' => 'success',
            'data' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'branch_id' => 'required|exists:branches,id',
            'role' => 'nullable|string|exists:roles,name',
            'password' => 'nullable|string|min:8',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'branch_id' => $validated['branch_id'],
            'password' => ! empty($validated['password']) ? Hash::make($validated['password']) : $user->password,
        ]);

        if (! empty($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Akun staf berhasil diperbarui!',
            'data' => $user->load(['branch', 'roles']),
        ]);
    }

    public function roles()
    {
        $roles = Role::with('permissions')->get();

        return response()->json([
            'status' => 'success',
            'data' => $roles,
        ]);
    }
}
