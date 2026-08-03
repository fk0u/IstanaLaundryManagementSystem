<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\Order;
use App\Models\Refund;
use App\Services\AuditLogService;
use App\Services\CRM\LoyaltyService;
use App\Services\Finance\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    protected AuditLogService $auditLogService;

    protected JournalService $journalService;

    protected LoyaltyService $loyaltyService;

    public function __construct(
        AuditLogService $auditLogService,
        JournalService $journalService,
        LoyaltyService $loyaltyService
    ) {
        $this->auditLogService = $auditLogService;
        $this->journalService = $journalService;
        $this->loyaltyService = $loyaltyService;
    }

    /**
     * Display a listing of refunds.
     */
    public function index()
    {
        $branchId = session('scoped_branch_id') ?? Auth::user()->branch_id;

        $refundsQuery = Refund::with(['order', 'branch', 'requester'])
            ->orderBy('created_at', 'desc');

        if ($branchId) {
            $refundsQuery->where('branch_id', $branchId);
        }

        $refunds = $refundsQuery->paginate(15);

        // Fetch paid orders available for refund (no active non-rejected refunds)
        $ordersQuery = Order::with('customer')->where('payment_status', 'paid');
        if ($branchId) {
            $ordersQuery->where('branch_id', $branchId);
        }

        $activeRefundOrderIds = Refund::where('status', '!=', 'rejected')->pluck('order_id')->toArray();
        $refundableOrders = $ordersQuery->whereNotIn('id', $activeRefundOrderIds)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('refunds.index', compact('refunds', 'refundableOrders'));
    }

    /**
     * Store a newly created refund request in storage.
     */
    public function store(Request $request)
    {
        if ($request->filled('order_number') && ! $request->filled('order_id')) {
            $found = Order::where('order_number', trim($request->order_number))->first();
            if ($found) {
                $request->merge(['order_id' => $found->id]);
            }
        }

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:1000',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Prevent double refunds
        $exists = Refund::where('order_id', $order->id)->where('status', '!=', 'rejected')->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'Refund untuk order ini sudah diajukan atau diproses.');
        }

        // Validate amount does not exceed order total
        if ($request->amount > $order->total) {
            return redirect()->back()->with('error', 'Jumlah refund tidak boleh melebihi total nilai transaksi order.');
        }

        $refund = DB::transaction(function () use ($request, $order) {
            $refund = Refund::create([
                'order_id' => $order->id,
                'branch_id' => $order->branch_id,
                'requested_by' => Auth::id(),
                'amount' => $request->amount,
                'reason' => $request->reason,
                'status' => 'pending',
                'cashier_approved_at' => now(), // Cashier requesting counts as cashier approved
            ]);

            $this->auditLogService->log('request_refund', $refund, null, $refund->toArray());

            return $refund;
        });

        return redirect()->route('refunds.index')->with('success', 'Permintaan refund berhasil diajukan dan sedang menunggu persetujuan Branch Admin.');
    }

    /**
     * Approve the refund to the next stage.
     */
    public function approve(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);
        $user = Auth::user();
        $oldRefundData = $refund->toArray();

        // Stage 1: pending -> branch_approved
        if ($refund->status === 'pending') {
            if (! $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin'])) {
                abort(403, 'Anda tidak memiliki hak akses untuk menyetujui tahap ini.');
            }

            $refund->update([
                'status' => 'branch_approved',
                'branch_approved_at' => now(),
            ]);

            $this->auditLogService->log('branch_approve_refund', $refund, $oldRefundData, $refund->toArray());

            return redirect()->back()->with('success', 'Refund disetujui oleh Branch Admin. Menunggu persetujuan Finance.');
        }

        // Stage 2: branch_approved -> finance_approved
        if ($refund->status === 'branch_approved') {
            if (! $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance'])) {
                abort(403, 'Anda tidak memiliki hak akses untuk menyetujui tahap ini.');
            }

            $refund->update([
                'status' => 'finance_approved',
                'finance_approved_at' => now(),
            ]);

            $this->auditLogService->log('finance_approve_refund', $refund, $oldRefundData, $refund->toArray());

            return redirect()->back()->with('success', 'Refund disetujui oleh Finance. Menunggu persetujuan akhir Owner.');
        }

        // Stage 3: finance_approved -> completed
        if ($refund->status === 'finance_approved') {
            if (! $user->hasAnyRole(['Developer', 'Owner'])) {
                abort(403, 'Anda tidak memiliki hak akses untuk menyetujui tahap ini.');
            }

            DB::transaction(function () use ($refund, $user, $oldRefundData) {
                $refund->update([
                    'status' => 'completed',
                    'owner_approved_at' => now(),
                    'processed_at' => now(),
                ]);

                // 1. Set Order payment status to refunded
                $order = $refund->order;
                $order->update([
                    'payment_status' => 'refunded',
                ]);

                // 2. Reverse accounting journal entry if exists
                $journal = Journal::where('source_type', Order::class)
                    ->where('source_id', $order->id)
                    ->where('status', 'posted')
                    ->first();

                if ($journal) {
                    $this->journalService->reverseJournal($journal, $user);
                }

                // 3. Deduct loyalty points proportionally
                $this->loyaltyService->deductPointsForRefund($order, $refund->amount);

                $this->auditLogService->log('owner_complete_refund', $refund, $oldRefundData, $refund->toArray());
            });

            return redirect()->back()->with('success', 'Refund disetujui penuh oleh Owner. Pembayaran dikembalikan, jurnal pembalik dicatat, dan poin loyalitas telah disesuaikan.');
        }

        return redirect()->back()->with('error', 'Status refund tidak dapat diproses lebih lanjut.');
    }

    /**
     * Reject the refund.
     */
    public function reject(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);
        $user = Auth::user();
        $oldRefundData = $refund->toArray();

        // Allow active roles based on current status to reject
        $canReject = false;

        if ($refund->status === 'pending' && $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin'])) {
            $canReject = true;
        } elseif ($refund->status === 'branch_approved' && $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance'])) {
            $canReject = true;
        } elseif ($refund->status === 'finance_approved' && $user->hasAnyRole(['Developer', 'Owner'])) {
            $canReject = true;
        }

        if (! $canReject) {
            abort(403, 'Anda tidak memiliki hak akses untuk menolak permintaan refund ini.');
        }

        $refund->update([
            'status' => 'rejected',
        ]);

        $this->auditLogService->log('reject_refund', $refund, $oldRefundData, $refund->toArray());

        return redirect()->back()->with('success', 'Permintaan refund ditolak.');
    }
}
