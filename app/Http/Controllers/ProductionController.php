<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\ProductionStatusLog;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionController extends Controller
{
    protected AuditLogService $auditLogService;

    // Status order for validation
    protected array $statusOrder = [
        'TERIMA', 
        'PILAH', 
        'CUCI', 
        'KERING', 
        'LIPAT', 
        'CEK', 
        'SIAP', 
        'DIAMBIL'
    ];

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Display Production Dashboard.
     */
    public function index(Request $request)
    {
        $branchId = session('scoped_branch_id') ?? Auth::user()->branch_id;
        if (!$branchId) {
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

        $orders = $query->with(['customer', 'orderItems.service'])->orderBy('created_at', 'desc')->get();

        return view('production.index', compact('branch', 'orders'));
    }

    /**
     * Update Order Production Status.
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $newStatus = strtoupper($request->input('status'));

        if (!in_array($newStatus, $this->statusOrder)) {
            return redirect()->back()->with('error', 'Status produksi tidak valid.');
        }

        $currentIndex = array_search($order->production_status, $this->statusOrder);
        $newIndex = array_search($newStatus, $this->statusOrder);

        // Enforce linear forward-only check
        if ($newIndex <= $currentIndex) {
            return redirect()->back()->with('error', "Transisi status tidak valid. Status saat ini: {$order->production_status}. Status baru harus lebih maju.");
        }

        // Optional: Enforce strictly 1-step transition (unless Developer/Owner/Super_Admin)
        $isSuper = Auth::user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']);
        if (!$isSuper && $newIndex !== $currentIndex + 1) {
            $expectedNext = $this->statusOrder[$currentIndex + 1] ?? 'SELESAI';
            return redirect()->back()->with('error', "Transisi tidak boleh melompat. Langkah selanjutnya yang diwajibkan: {$expectedNext}.");
        }

        DB::transaction(function () use ($order, $newStatus, $request) {
            $oldStatus = $order->production_status;
            
            // Update Order
            $order->update([
                'production_status' => $newStatus,
            ]);

            // Add Production Status Log
            ProductionStatusLog::create([
                'order_id' => $order->id,
                'status' => $newStatus,
                'updated_by' => Auth::id(),
                'notes' => $request->input('notes') ?? "Perubahan status dari {$oldStatus} ke {$newStatus}.",
            ]);

            // If production is taken, mark paid if cash and update payment status
            if ($newStatus === 'DIAMBIL' && $order->payment_status !== 'paid') {
                // If paid amount equals total, set paid
                if ($order->paid_amount >= $order->total) {
                    $order->update(['payment_status' => 'paid', 'paid_at' => now()]);
                }
            }

            // Log activity to audit_logs
            $this->auditLogService->log("update_production_status_{$newStatus}", $order, ['production_status' => $oldStatus], ['production_status' => $newStatus]);
        });

        return redirect()->route('production.index')->with('success', "Status order #{$order->order_number} berhasil diperbarui ke {$newStatus}!");
    }
}
