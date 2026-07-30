<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * Display a listing of branches and their scope statistics.
     */
    public function index(Request $request)
    {
        $search = trim($request->query('q', ''));

        $branches = Branch::withCount(['users', 'employees', 'orders'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%")
                    ->orWhere('address', 'LIKE', "%{$search}%");
            })
            ->orderBy('is_active', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        return view('branches.index', compact('branches', 'search'));
    }

    /**
     * Store a newly created branch.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:branches,code',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
        ]);

        $branch = Branch::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', "Cabang baru {$branch->name} ({$branch->code}) berhasil ditambahkan!");
    }

    /**
     * Update branch details.
     */
    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('branches')->ignore($branch->id)],
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'is_active' => 'required|boolean',
        ]);

        $branch->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->back()->with('success', "Informasi cabang {$branch->name} berhasil diperbarui.");
    }

    /**
     * Toggle active status of branch.
     */
    public function toggleActive(Branch $branch)
    {
        $branch->update(['is_active' => ! $branch->is_active]);
        $statusLabel = $branch->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()->with('success', "Cabang {$branch->name} berhasil {$statusLabel}.");
    }

    /**
     * Remove branch.
     */
    public function destroy(Branch $branch)
    {
        if ($branch->orders()->exists() || $branch->users()->exists()) {
            return redirect()->back()->with('error', "Cabang {$branch->name} tidak dapat dihapus karena masih memiliki riwayat transaksi atau pengguna terikat.");
        }

        $branch->delete();

        return redirect()->back()->with('success', "Cabang {$branch->name} berhasil dihapus.");
    }
}
