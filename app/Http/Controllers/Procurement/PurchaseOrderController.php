<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'pr', 'items.item'])
            ->orderBy('id', 'desc')
            ->paginate(15);

        // Standard helpers for creating a PO
        $suppliers = Supplier::where('is_active', true)->orderBy('name', 'asc')->get();

        // List approved PRs that don't have a PO yet
        $approvedPrs = PurchaseRequest::where('status', 'approved')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('purchase_orders')
                    ->whereColumn('purchase_orders.pr_id', 'purchase_requests.id');
            })
            ->orderBy('pr_number', 'asc')
            ->get();

        $inventoryItems = InventoryItem::orderBy('name', 'asc')->get();

        return view('procurement.purchase_orders.index', compact('purchaseOrders', 'suppliers', 'approvedPrs', 'inventoryItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'pr_id' => 'nullable|exists:purchase_requests,id',
            'expected_date' => 'required|date|after_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $branchId = auth()->user()->branch_id ?? 1;
            $branchCode = auth()->user()->branch?->code ?? 'HQ';
            $yearMonth = now()->format('Ym');

            // Generate PO number
            $count = PurchaseOrder::withoutGlobalScopes()
                ->where('branch_id', $branchId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count();
            $seqStr = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            $poNumber = "PO-{$branchCode}-{$yearMonth}-{$seqStr}";

            // Calculate totals
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += (float) $item['quantity'] * (float) $item['unit_cost'];
            }
            $taxAmount = $subtotal * 0.11; // 11% PPN standard tax
            $total = $subtotal + $taxAmount;

            $po = PurchaseOrder::create([
                'pr_id' => $request->pr_id,
                'branch_id' => $branchId,
                'po_number' => $poNumber,
                'supplier_id' => $request->supplier_id,
                'status' => 'draft',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'order_date' => now()->toDateString(),
                'expected_date' => $request->expected_date,
            ]);

            foreach ($request->items as $item) {
                $itemSubtotal = (float) $item['quantity'] * (float) $item['unit_cost'];
                PurchaseOrderItem::create([
                    'po_id' => $po->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $itemSubtotal,
                    'received_qty' => 0,
                ]);
            }

            // If PR is linked, update its status
            if ($request->pr_id) {
                PurchaseRequest::find($request->pr_id)->update(['status' => 'approved']); // Ensure marked
            }
        });

        return redirect()->back()->with('success', 'Purchase Order (PO) berhasil dibuat sebagai draft.');
    }

    public function send($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        if ($po->status !== 'draft') {
            return redirect()->back()->with('error', 'Hanya PO draft yang dapat dikirim.');
        }

        $po->update(['status' => 'sent']);

        return redirect()->back()->with('success', 'Status PO berhasil diubah menjadi dikirim (Sent).');
    }

    public function confirm($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        if (! in_array($po->status, ['draft', 'sent'])) {
            return redirect()->back()->with('error', 'Hanya PO draft atau dikirim yang dapat dikonfirmasi.');
        }

        $po->update(['status' => 'confirmed']);

        return redirect()->back()->with('success', 'Status PO berhasil dikonfirmasi oleh supplier.');
    }

    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        if ($po->status !== 'draft') {
            return redirect()->back()->with('error', 'Hanya PO draft yang dapat dihapus.');
        }

        $po->delete();

        return redirect()->back()->with('success', 'PO berhasil dihapus.');
    }
}
