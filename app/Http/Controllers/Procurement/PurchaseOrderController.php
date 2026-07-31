<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Branch;
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

        // List approved PRs with their items and requester that don't have a PO yet
        $approvedPrs = PurchaseRequest::with(['requestedBy', 'items.item'])
            ->where('status', 'approved')
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
            $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id;
            if (! $branchId || ! Branch::where('id', $branchId)->exists()) {
                $branchId = Branch::first()?->id;
            }
            $branchCode = Branch::find($branchId)?->code ?? 'HQ';
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

            // If PR is linked, update its status to ordered
            if ($request->pr_id) {
                PurchaseRequest::find($request->pr_id)?->update(['status' => 'ordered']);
            }
        });

        return redirect()->back()->with('success', 'Purchase Order (PO) berhasil dibuat sebagai draft.');
    }

    public function show($id)
    {
        $po = PurchaseOrder::with(['supplier', 'pr.requestedBy', 'branch', 'items.item'])->findOrFail($id);

        $phone = preg_replace('/[^0-9]/', '', $po->supplier?->phone ?? '');
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $itemsText = "";
        foreach ($po->items as $idx => $item) {
            $n = $idx + 1;
            $name = $item->item?->name ?? 'Barang #' . $item->item_id;
            $unit = $item->item?->unit ?? 'unit';
            $qty = number_format($item->quantity, 0, ',', '.');
            $cost = 'Rp ' . number_format($item->unit_cost, 0, ',', '.');
            $sub = 'Rp ' . number_format($item->subtotal, 0, ',', '.');
            $itemsText .= "{$n}. {$name} x {$qty} {$unit} ({$cost}) = {$sub}\n";
        }

        $orderDateStr = $po->order_date ? $po->order_date->format('d/m/Y') : date('d/m/Y');
        $expectedDateStr = $po->expected_date ? $po->expected_date->format('d/m/Y') : '-';
        $subtotalStr = 'Rp ' . number_format($po->subtotal, 0, ',', '.');
        $taxStr = 'Rp ' . number_format($po->tax_amount, 0, ',', '.');
        $totalStr = 'Rp ' . number_format($po->total, 0, ',', '.');

        $message = "*PURCHASE ORDER (PO)* - Istana Laundry\n"
            . "-----------------------------------\n"
            . "*No PO:* {$po->po_number}\n"
            . "*Supplier:* {$po->supplier?->name}\n"
            . "*Tanggal Order:* {$orderDateStr}\n"
            . "*Estimasi Diterima:* {$expectedDateStr}\n"
            . "*Cabang Tujuan:* {$po->branch?->name}\n\n"
            . "*DETAIL BARANG PESANAN:*\n"
            . $itemsText . "\n"
            . "-----------------------------------\n"
            . "*Subtotal:* {$subtotalStr}\n"
            . "*PPN (11%):* {$taxStr}\n"
            . "*GRAND TOTAL PO:* {$totalStr}\n\n"
            . "Mohon dapat dikonfirmasi & diproses pengirimannya. Terima kasih!";

        $waUrl = $phone ? ("https://wa.me/{$phone}?text=" . urlencode($message)) : null;

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'po' => [
                    'id' => $po->id,
                    'po_number' => $po->po_number,
                    'status' => $po->status,
                    'order_date' => $orderDateStr,
                    'expected_date' => $expectedDateStr,
                    'subtotal' => $po->subtotal,
                    'tax_amount' => $po->tax_amount,
                    'total' => $po->total,
                    'supplier_name' => $po->supplier?->name ?? 'Supplier',
                    'supplier_phone' => $po->supplier?->phone ?? '-',
                    'supplier_email' => $po->supplier?->email ?? '-',
                    'supplier_address' => $po->supplier?->address ?? '-',
                    'branch_name' => $po->branch?->name ?? 'Cabang',
                    'pr_number' => $po->pr?->pr_number ?? null,
                ],
                'items' => $po->items->map(function ($i) {
                    return [
                        'id' => $i->id,
                        'name' => $i->item?->name ?? ('Item #' . $i->item_id),
                        'unit' => $i->item?->unit ?? 'unit',
                        'quantity' => (float) $i->quantity,
                        'unit_cost' => (float) $i->unit_cost,
                        'subtotal' => (float) $i->subtotal,
                        'received_qty' => (float) $i->received_qty,
                    ];
                }),
                'wa_url' => $waUrl,
                'message' => $message,
            ]);
        }

        return view('procurement.purchase_orders.show', compact('po', 'waUrl', 'message'));
    }

    public function whatsapp($id)
    {
        $po = PurchaseOrder::with(['supplier', 'branch', 'items.item'])->findOrFail($id);

        if ($po->status === 'draft') {
            $po->update(['status' => 'sent']);
        }

        $phone = preg_replace('/[^0-9]/', '', $po->supplier?->phone ?? '');
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $itemsText = "";
        foreach ($po->items as $idx => $item) {
            $n = $idx + 1;
            $name = $item->item?->name ?? 'Barang #' . $item->item_id;
            $unit = $item->item?->unit ?? 'unit';
            $qty = number_format($item->quantity, 0, ',', '.');
            $cost = 'Rp ' . number_format($item->unit_cost, 0, ',', '.');
            $sub = 'Rp ' . number_format($item->subtotal, 0, ',', '.');
            $itemsText .= "{$n}. {$name} x {$qty} {$unit} ({$cost}) = {$sub}\n";
        }

        $orderDateStr = $po->order_date ? $po->order_date->format('d/m/Y') : date('d/m/Y');
        $expectedDateStr = $po->expected_date ? $po->expected_date->format('d/m/Y') : '-';
        $subtotalStr = 'Rp ' . number_format($po->subtotal, 0, ',', '.');
        $taxStr = 'Rp ' . number_format($po->tax_amount, 0, ',', '.');
        $totalStr = 'Rp ' . number_format($po->total, 0, ',', '.');

        $message = "*PURCHASE ORDER (PO)* - Istana Laundry\n"
            . "-----------------------------------\n"
            . "*No PO:* {$po->po_number}\n"
            . "*Supplier:* {$po->supplier?->name}\n"
            . "*Tanggal Order:* {$orderDateStr}\n"
            . "*Estimasi Diterima:* {$expectedDateStr}\n"
            . "*Cabang Tujuan:* {$po->branch?->name}\n\n"
            . "*DETAIL BARANG PESANAN:*\n"
            . $itemsText . "\n"
            . "-----------------------------------\n"
            . "*Subtotal:* {$subtotalStr}\n"
            . "*PPN (11%):* {$taxStr}\n"
            . "*GRAND TOTAL PO:* {$totalStr}\n\n"
            . "Mohon dapat dikonfirmasi & diproses pengirimannya. Terima kasih!";

        if (! $phone) {
            return redirect()->back()->with('error', 'Supplier ini belum memiliki nomor telepon / WhatsApp yang valid.');
        }

        $waUrl = "https://wa.me/{$phone}?text=" . urlencode($message);

        return redirect()->away($waUrl);
    }

    public function print($id)
    {
        $po = PurchaseOrder::with(['supplier', 'pr.requestedBy', 'branch', 'items.item'])->findOrFail($id);

        return view('procurement.purchase_orders.print', compact('po'));
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
