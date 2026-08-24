<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    protected WhatsAppService $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

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
            ->orderBy('id', 'desc')
            ->get();

        $inventoryItems = InventoryItem::where('is_active', true)->orderBy('name', 'asc')->get();

        return view('procurement.purchase_orders.index', compact('purchaseOrders', 'suppliers', 'approvedPrs', 'inventoryItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'nullable|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'pr_id' => 'nullable|exists:purchase_requests,id',
            'purchase_request_id' => 'nullable|exists:purchase_requests,id',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $subtotal = 0;
        foreach ($request->items as $itemData) {
            $subtotal += $itemData['quantity'] * $itemData['unit_cost'];
        }

        $taxAmount = $subtotal * 0.11; // Standard PPN 11%
        $total = $subtotal + $taxAmount;

        $poNumber = 'PO-'.date('Ymd').'-'.str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $branchId = session('scoped_branch_id') ?? session('branch_id') ?? auth()->user()?->branch_id ?? 1;

        $po = PurchaseOrder::create([
            'po_number' => $poNumber,
            'pr_id' => $request->pr_id ?? $request->purchase_request_id,
            'supplier_id' => $request->supplier_id,
            'branch_id' => $branchId,
            'status' => 'draft',
            'order_date' => $request->order_date ?? now()->toDateString(),
            'expected_date' => $request->expected_date,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);

        foreach ($request->items as $itemData) {
            $itemSubtotal = $itemData['quantity'] * $itemData['unit_cost'];
            PurchaseOrderItem::create([
                'po_id' => $po->id,
                'item_id' => $itemData['item_id'],
                'quantity' => $itemData['quantity'],
                'unit_cost' => $itemData['unit_cost'],
                'subtotal' => $itemSubtotal,
            ]);
        }

        return redirect()->back()->with('success', 'Purchase Order (PO) berhasil dibuat sebagai draft.');
    }

    public function show($id)
    {
        $po = PurchaseOrder::with(['supplier', 'pr.requestedBy', 'branch', 'items.item'])->findOrFail($id);

        $phone = $po->supplier?->phone ?? '';
        $message = $this->whatsAppService->generatePurchaseOrderMessage($po);
        $waUrl = ! empty($phone) ? $this->whatsAppService->generateWhatsAppUrl($phone, $message) : null;

        $orderDateStr = $po->order_date ? $po->order_date->format('d/m/Y') : date('d/m/Y');
        $expectedDateStr = $po->expected_date ? $po->expected_date->format('d/m/Y') : '-';

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

        $phone = $po->supplier?->phone ?? '';
        if (! $phone) {
            return redirect()->back()->with('error', 'Supplier ini belum memiliki nomor telepon / WhatsApp yang valid.');
        }

        $message = $this->whatsAppService->generatePurchaseOrderMessage($po);
        $waUrl = $this->whatsAppService->generateWhatsAppUrl($phone, $message);

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

    public function exportPdf()
    {
        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id;
        $branchName = $branchId ? (Branch::find($branchId)?->name ?? 'Cabang Unknown') : 'Semua Cabang';
        $purchaseOrders = PurchaseOrder::with(['supplier', 'pr', 'items.item'])
            ->orderBy('id', 'desc')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.procurement_po_pdf', compact('purchaseOrders', 'branchName'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('purchase_orders_' . now()->format('Ymd_His') . '.pdf');
    }

    public function exportExcel()
    {
        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id;
        $branchName = $branchId ? (Branch::find($branchId)?->name ?? 'Cabang Unknown') : 'Semua Cabang';
        $purchaseOrders = PurchaseOrder::with(['supplier', 'pr', 'items.item'])
            ->orderBy('id', 'desc')
            ->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\GenericViewExport('exports.procurement_po_pdf', compact('purchaseOrders', 'branchName'), 'Purchase Orders'),
            'purchase_orders_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
}
