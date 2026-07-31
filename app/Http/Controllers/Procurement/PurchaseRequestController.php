<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseRequestController extends Controller
{
    public function index()
    {
        $purchaseRequests = PurchaseRequest::with(['requestedBy', 'approvedBy', 'items.item'])
            ->orderBy('id', 'desc')
            ->paginate(15);

        // For the creation dropdown
        $inventoryItems = InventoryItem::orderBy('name', 'asc')->get();

        return view('procurement.purchase_requests.index', compact('purchaseRequests', 'inventoryItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost_estimate' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id;
            if (! $branchId || ! Branch::where('id', $branchId)->exists()) {
                $branchId = Branch::first()?->id;
            }
            $branchCode = Branch::find($branchId)?->code ?? 'HQ';
            $yearMonth = now()->format('Ym');

            // Generate PR number
            $count = PurchaseRequest::withoutGlobalScopes()
                ->where('branch_id', $branchId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count();
            $seqStr = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            $prNumber = "PR-{$branchCode}-{$yearMonth}-{$seqStr}";

            $pr = PurchaseRequest::create([
                'branch_id' => $branchId,
                'pr_number' => $prNumber,
                'requested_by' => auth()->id(),
                'status' => 'pending_approval',
                'request_date' => now()->toDateString(),
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                PurchaseRequestItem::create([
                    'pr_id' => $pr->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost_estimate' => $item['unit_cost_estimate'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Permintaan pembelian (PR) berhasil diajukan.');
    }

    public function approve($id)
    {
        $pr = PurchaseRequest::findOrFail($id);

        if ($pr->status !== 'pending_approval') {
            return redirect()->back()->with('error', 'PR ini tidak dalam status pending.');
        }

        $pr->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'PR berhasil disetujui.');
    }

    public function reject($id)
    {
        $pr = PurchaseRequest::findOrFail($id);

        if ($pr->status !== 'pending_approval') {
            return redirect()->back()->with('error', 'PR ini tidak dalam status pending.');
        }

        $pr->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'PR berhasil ditolak.');
    }

    public function destroy($id)
    {
        $pr = PurchaseRequest::findOrFail($id);

        if (! in_array($pr->status, ['pending_approval', 'rejected'])) {
            return redirect()->back()->with('error', 'Hanya PR pending atau ditolak yang dapat dihapus.');
        }

        $pr->delete();

        return redirect()->back()->with('success', 'PR berhasil dihapus.');
    }
}
