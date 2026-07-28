<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withCount('purchaseOrders')->orderBy('name', 'asc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->paginate(15)->withQueryString();

        return view('procurement.suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'npwp' => 'nullable|string|max:25',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Supplier::create($validated);

        return redirect()->back()->with('success', 'Supplier baru berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name,'.$supplier->id,
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'npwp' => 'nullable|string|max:25',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);

        $supplier->update($validated);

        return redirect()->back()->with('success', 'Data supplier berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);

        if ($supplier->purchaseOrders()->exists()) {
            return redirect()->back()->with('error', 'Supplier tidak dapat dihapus karena masih memiliki Purchase Order terkait. Nonaktifkan saja.');
        }

        $supplier->delete();

        return redirect()->back()->with('success', 'Supplier berhasil dihapus.');
    }
}
