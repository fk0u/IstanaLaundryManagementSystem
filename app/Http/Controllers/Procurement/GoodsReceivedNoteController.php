<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceivedNote;
use App\Models\GRNItem;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsReceivedNoteController extends Controller
{
    public function index()
    {
        $grns = GoodsReceivedNote::with(['purchaseOrder', 'receivedBy', 'items.item'])
            ->orderBy('id', 'desc')
            ->paginate(15);

        // List POs with supplier and items that are available for receiving
        $activePos = PurchaseOrder::with(['supplier', 'items.item'])
            ->whereIn('status', ['draft', 'sent', 'confirmed', 'partial'])
            ->orderBy('po_number', 'asc')
            ->get();

        return view('procurement.grn.index', compact('grns', 'activePos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'po_id' => 'required|exists:purchase_orders,id',
            'received_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.po_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $po = PurchaseOrder::findOrFail($request->po_id);
            $branchId = $po->branch_id;
            $branchCode = $po->branch?->code ?? 'HQ';
            $yearMonth = now()->format('Ym');

            // Generate GRN number
            $count = GoodsReceivedNote::whereHas('purchaseOrder', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count();
            $seqStr = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            $grnNumber = "GRN-{$branchCode}-{$yearMonth}-{$seqStr}";

            $grn = GoodsReceivedNote::create([
                'po_id' => $request->po_id,
                'grn_number' => $grnNumber,
                'received_by' => auth()->id(),
                'status' => 'draft',
                'received_date' => $request->received_date,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                // Skip if quantity is 0 (partial receiving could ignore some items)
                if ((float) $item['quantity'] <= 0) {
                    continue;
                }

                GRNItem::create([
                    'grn_id' => $grn->id,
                    'item_id' => $item['item_id'],
                    'po_item_id' => $item['po_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'batch_number' => 'BATCH-'.$grnNumber.'-'.$item['item_id'],
                ]);
            }
        });

        return redirect()->back()->with('success', 'Goods Received Note (GRN) berhasil dibuat sebagai draft.');
    }

    public function confirm($id)
    {
        $grn = GoodsReceivedNote::findOrFail($id);
        if ($grn->status !== 'draft') {
            return redirect()->back()->with('error', 'Hanya GRN draft yang dapat dikonfirmasi.');
        }

        // Updating the status to confirmed triggers GRNObserver::updated event
        $grn->update(['status' => 'confirmed']);

        return redirect()->back()->with('success', 'GRN berhasil dikonfirmasi. Stok persediaan telah diperbarui dan jurnal otomatis diposting.');
    }

    public function destroy($id)
    {
        $grn = GoodsReceivedNote::findOrFail($id);
        if ($grn->status !== 'draft') {
            return redirect()->back()->with('error', 'Hanya GRN draft yang dapat dihapus.');
        }

        $grn->delete();

        return redirect()->back()->with('success', 'GRN berhasil dihapus.');
    }
}
