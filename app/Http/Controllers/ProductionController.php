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
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id');
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        } else if ($branchId === null && session()->has('scoped_branch_id')) {
            $branchId = session('scoped_branch_id');
        }

        $branch = $branchId ? Branch::find($branchId) : null;
        $branches = $isGlobalUser ? Branch::orderBy('name')->get() : collect();

        $requestedStatus = $request->query('status');
        $search = trim((string) $request->query('search'));

        $query = Order::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($requestedStatus && in_array($requestedStatus, $this->statusOrder, true)) {
            // Explicit filter (including DIAMBIL) — show exactly that status.
            $query->where('production_status', $requestedStatus);
        } else {
            // Default board view: hide DIAMBIL so the operational board stays
            // focused on active production. Use the DIAMBIL filter to see them.
            $query->where('production_status', '!=', 'DIAMBIL');
        }

        $orders = $query->with(['customer', 'items.service', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $isWorkshopRole = $user->hasAnyRole(['Workshop_Staff', 'Workshop_Admin']);

        return view('production.index', compact(
            'branch',
            'branches',
            'branchId',
            'isGlobalUser',
            'orders',
            'requestedStatus',
            'search',
            'isWorkshopRole'
        ));
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
