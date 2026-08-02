<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\GoodsReceivedNote;
use App\Models\Journal;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\Finance\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SupplierPaymentController extends Controller
{
    protected JournalService $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $isGlobalUser = $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']);

        $branchId = $request->query('branch_id');
        if (! $isGlobalUser) {
            $branchId = session('scoped_branch_id') ?? $user->branch_id;
        }

        $branches = Branch::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        // GRNs that have been confirmed (potential AP to settle)
        $confirmedGrns = GoodsReceivedNote::withoutGlobalScopes()
            ->with(['supplier', 'purchaseOrder.supplier', 'items'])
            ->where('status', 'confirmed')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('received_date')
            ->get();

        $query = SupplierPayment::with(['supplier', 'goodsReceivedNote', 'branch', 'creator'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        // Date filter
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());

        $query->whereBetween('payment_date', [$startDate, $endDate]);

        $payments = $query->orderByDesc('payment_date')->orderByDesc('id')->paginate(20);

        // Summary
        $totalPayments = (clone $query)->sum('amount');

        return view('finance.supplier-payments.index', compact(
            'payments',
            'branches',
            'branchId',
            'suppliers',
            'confirmedGrns',
            'startDate',
            'endDate',
            'totalPayments'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'grn_id' => 'nullable|exists:goods_received_notes,id',
            'payment_date' => 'required|date|before_or_equal:today',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,transfer',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id;
        if (! $branchId) {
            $branchId = Branch::first()?->id;
        }

        try {
            $payment = SupplierPayment::create([
                'branch_id' => $branchId,
                'supplier_id' => $request->supplier_id,
                'grn_id' => $request->grn_id,
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'status' => 'posted',
                'created_by' => Auth::id(),
            ]);

            $journal = $this->journalService->postSupplierPaymentJournal($payment);

            $supplierName = Supplier::find($request->supplier_id)?->name ?? 'Unknown';

            return redirect()->back()->with('success', "Pembayaran ke supplier {$supplierName} berhasil dicatat! Jurnal tersinkronisasi (Ref: {$journal->reference})");
        } catch (\Exception $e) {
            Log::error('Failed to record supplier payment: '.$e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Gagal mencatat pembayaran supplier: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        $payment = SupplierPayment::findOrFail($id);

        // Check if there's a journal for this payment and reverse it
        $journal = Journal::withoutGlobalScopes()
            ->where('source_type', SupplierPayment::class)
            ->where('source_id', $payment->id)
            ->where('status', '!=', 'reversed')
            ->first();

        if ($journal) {
            try {
                $this->journalService->reverseJournal($journal, auth()->user());
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Gagal membatalkan jurnal terkait: '.$e->getMessage());
            }
        }

        $payment->delete();

        return redirect()->back()->with('success', 'Catatan pembayaran supplier berhasil dihapus dan jurnal dibatalkan.');
    }
}
