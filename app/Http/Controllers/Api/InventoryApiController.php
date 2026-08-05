<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryApiController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->query('low_stock') === 'true') {
            $query->whereColumn('current_stock', '<=', 'min_stock');
        }

        $items = $query->orderBy('name', 'asc')->paginate($request->query('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:inventory_items,sku',
            'category' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'min_stock' => 'required|numeric|min:0',
            'current_stock' => 'required|numeric|min:0',
        ]);

        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id ?? 1;

        $item = InventoryItem::create([
            'branch_id' => $branchId,
            'name' => $validated['name'],
            'sku' => strtoupper($validated['sku']),
            'category' => $validated['category'],
            'unit' => $validated['unit'],
            'min_stock' => $validated['min_stock'],
            'current_stock' => $validated['current_stock'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Item inventori baru berhasil ditambahkan!',
            'data' => $item,
        ], 201);
    }

    public function show(InventoryItem $inventoryItem)
    {
        return response()->json([
            'status' => 'success',
            'data' => $inventoryItem,
        ]);
    }

    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'min_stock' => 'required|numeric|min:0',
        ]);

        $inventoryItem->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Data item inventori berhasil diperbarui!',
            'data' => $inventoryItem,
        ]);
    }

    public function adjust(Request $request, InventoryItem $inventoryItem)
    {
        $validated = $request->validate([
            'current_stock' => 'required|numeric|min:0',
        ]);

        $inventoryItem->update(['current_stock' => $validated['current_stock']]);

        return response()->json([
            'status' => 'success',
            'message' => 'Stok inventori berhasil dikoreksi!',
            'data' => $inventoryItem,
        ]);
    }

    public function destroy(InventoryItem $inventoryItem)
    {
        $inventoryItem->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Item inventori berhasil dihapus.',
        ]);
    }
}
