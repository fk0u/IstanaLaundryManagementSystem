<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceivedNote;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcurementApiController extends Controller
{
    public function suppliers(Request $request)
    {
        $suppliers = Supplier::orderBy('name', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $suppliers,
        ]);
    }

    public function storeSupplier(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:25',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $supplier = Supplier::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Supplier baru berhasil ditambahkan!',
            'data' => $supplier,
        ], 201);
    }

    public function purchaseRequests(Request $request)
    {
        $prs = PurchaseRequest::with(['branch', 'requester', 'items.inventoryItem'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 10));

        return response()->json([
            'status' => 'success',
            'data' => $prs,
        ]);
    }

    public function showPr(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load(['branch', 'requester', 'items.inventoryItem']);

        return response()->json([
            'status' => 'success',
            'data' => $purchaseRequest,
        ]);
    }

    public function storePurchaseRequest(Request $request)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.1',
        ]);

        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id ?? 1;

        return DB::transaction(function () use ($validated, $branchId) {
            $prNumber = 'PR-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            $pr = PurchaseRequest::create([
                'branch_id' => $branchId,
                'pr_number' => $prNumber,
                'requested_by' => auth()->id() ?? 1,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? 'Pengajuan via API',
            ]);

            foreach ($validated['items'] as $item) {
                PurchaseRequestItem::create([
                    'pr_id' => $pr->id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Purchase Request berhasil diajukan!',
                'data' => $pr->load('items.inventoryItem'),
            ], 201);
        });
    }

    public function approvePr(Request $request, PurchaseRequest $purchaseRequest)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
        ]);

        $status = $validated['action'] === 'approve' ? 'approved' : 'rejected';

        $purchaseRequest->update([
            'status' => $status,
            'approved_by' => auth()->id() ?? 1,
            'approved_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status Purchase Request berhasil diperbarui menjadi ' . strtoupper($status) . '!',
            'data' => $purchaseRequest,
        ]);
    }

    public function purchaseOrders(Request $request)
    {
        $pos = PurchaseOrder::with(['branch', 'supplier', 'items.inventoryItem'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 10));

        return response()->json([
            'status' => 'success',
            'data' => $pos,
        ]);
    }

    public function showPo(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['branch', 'supplier', 'items.inventoryItem', 'grns']);

        return response()->json([
            'status' => 'success',
            'data' => $purchaseOrder,
        ]);
    }

    public function storePurchaseOrder(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'pr_id' => 'nullable|exists:purchase_requests,id',
            'notes' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id ?? 1;

        return DB::transaction(function () use ($validated, $branchId) {
            $poNumber = 'PO-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            $totalAmount = collect($validated['items'])->sum(fn ($i) => $i['quantity'] * $i['unit_price']);

            $po = PurchaseOrder::create([
                'branch_id' => $branchId,
                'supplier_id' => $validated['supplier_id'],
                'pr_id' => $validated['pr_id'] ?? null,
                'po_number' => $poNumber,
                'total_amount' => $totalAmount,
                'status' => 'ordered',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id() ?? 1,
            ]);

            foreach ($validated['items'] as $item) {
                PurchaseOrderItem::create([
                    'po_id' => $po->id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Purchase Order berhasil dibuat!',
                'data' => $po->load('items.inventoryItem'),
            ], 201);
        });
    }

    public function grns(Request $request)
    {
        $grns = GoodsReceivedNote::with(['purchaseOrder.supplier', 'receiver'])
            ->orderBy('received_at', 'desc')
            ->paginate($request->query('per_page', 10));

        return response()->json([
            'status' => 'success',
            'data' => $grns,
        ]);
    }

    public function storeGrn(Request $request)
    {
        $validated = $request->validate([
            'po_id' => 'required|exists:purchase_orders,id',
            'grn_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:255',
        ]);

        $po = PurchaseOrder::findOrFail($validated['po_id']);

        $grn = GoodsReceivedNote::create([
            'po_id' => $po->id,
            'grn_number' => $validated['grn_number'] ?? ('GRN-' . date('Ymd') . '-' . strtoupper(Str::random(4))),
            'received_by' => auth()->id() ?? 1,
            'received_at' => now(),
            'notes' => $validated['notes'] ?? 'Penerimaan via API',
        ]);

        $po->update(['status' => 'received']);

        return response()->json([
            'status' => 'success',
            'message' => 'Penerimaan Barang (GRN) berhasil dicatat dan status PO telah diperbarui!',
            'data' => $grn,
        ], 201);
    }
}
