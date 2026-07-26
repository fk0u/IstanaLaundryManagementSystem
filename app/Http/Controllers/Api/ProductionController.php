<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Branch;
use App\Models\Order;
use App\Services\ProductionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductionController extends Controller
{
    protected array $superRoles = ['Developer', 'Owner', 'Super_Admin'];

    public function __construct(protected ProductionService $productionService) {}

    /**
     * List orders in production for the authenticated user's branch.
     *
     * Query params:
     * - status: filter by a single production status (e.g. KERING, DIAMBIL)
     * - branch_id: override branch (super roles only)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $branchId = $this->resolveBranchId($request, $user);

        $query = Order::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('status')) {
            $status = strtoupper($request->string('status'));
            if (! in_array($status, ProductionService::STATUS_ORDER, true)) {
                throw ValidationException::withMessages([
                    'status' => 'Status produksi tidak valid.',
                ]);
            }
            $query->where('production_status', $status);
        }

        $orders = $query->with(['customer', 'items.service'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return OrderResource::collection($orders);
    }

    /**
     * Show a single order with full production detail.
     */
    public function show(Request $request, Order $order)
    {
        $this->authorizeBranchAccess($request, $order);

        $order->load(['branch', 'customer', 'items.service', 'productionStatusLogs.updater']);

        return new OrderResource($order);
    }

    /**
     * Update the production status of an order.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $this->authorizeBranchAccess($request, $order);

        $validated = $request->validate([
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->productionService->updateStatus(
            $order,
            $validated['status'],
            $request->user(),
            $validated['notes'] ?? null
        );

        $order->load(['branch', 'customer', 'items.service', 'productionStatusLogs.updater']);

        return (new OrderResource($order))->additional([
            'message' => "Status order #{$order->order_number} berhasil diperbarui ke {$order->production_status}.",
        ]);
    }

    protected function resolveBranchId(Request $request, $user): ?int
    {
        $isSuper = $user->hasAnyRole($this->superRoles);

        if ($isSuper && $request->filled('branch_id')) {
            return $request->integer('branch_id');
        }

        return $user->branch_id ?? Branch::first()?->id;
    }

    protected function authorizeBranchAccess(Request $request, Order $order): void
    {
        $user = $request->user();

        if ($user->hasAnyRole($this->superRoles)) {
            return;
        }

        if ($order->branch_id !== $user->branch_id) {
            abort(403, 'Anda tidak memiliki akses ke order cabang lain.');
        }
    }
}
