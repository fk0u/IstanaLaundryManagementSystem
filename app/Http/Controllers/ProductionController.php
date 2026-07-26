<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Services\ProductionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProductionController extends Controller
{
    // Status order for validation (kept for view/UI usage)
    protected array $statusOrder = ProductionService::STATUS_ORDER;

    public function __construct(protected ProductionService $productionService) {}

    /**
     * Display Production Dashboard.
     */
    public function index(Request $request)
    {
        $branchId = session('scoped_branch_id') ?? Auth::user()->branch_id;
        if (! $branchId) {
            $firstBranch = Branch::first();
            $branchId = $firstBranch?->id;
        }

        $branch = Branch::find($branchId);

        // Fetch orders not yet taken (DIAMBIL)
        $query = Order::where('branch_id', $branchId)
            ->where('production_status', '!=', 'DIAMBIL');

        if ($request->has('status') && in_array($request->status, $this->statusOrder)) {
            $query->where('production_status', $request->status);
        }

        $orders = $query->with(['customer', 'items.service'])->orderBy('created_at', 'desc')->get();

        return view('production.index', compact('branch', 'orders'));
    }

    /**
     * Update Order Production Status.
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        try {
            $this->productionService->updateStatus(
                $order,
                (string) $request->input('status'),
                Auth::user(),
                $request->input('notes')
            );
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('production.index')->with('success', "Status order #{$order->order_number} berhasil diperbarui ke {$order->production_status}!");
    }
}
