<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductionStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionService
{
    /**
     * Linear production status order (single source of truth).
     * Shared by both the web ProductionController and the REST API.
     */
    public const STATUS_ORDER = [
        'TERIMA',
        'PILAH',
        'CUCI',
        'KERING',
        'LIPAT',
        'CEK',
        'SIAP',
        'DIAMBIL',
    ];

    protected array $superRoles = ['Developer', 'Owner', 'Super_Admin'];

    public function __construct(protected AuditLogService $auditLogService) {}

    /**
     * Transition an order to a new production status, enforcing the linear
     * forward-only rule (and 1-step-only rule for non-super roles).
     *
     * @throws ValidationException When the requested transition is not allowed.
     */
    public function updateStatus(Order $order, string $status, User $user, ?string $notes = null): Order
    {
        $newStatus = strtoupper($status);

        if (! in_array($newStatus, self::STATUS_ORDER, true)) {
            throw ValidationException::withMessages([
                'status' => 'Status produksi tidak valid.',
            ]);
        }

        $currentIndex = array_search($order->production_status, self::STATUS_ORDER, true);
        $newIndex = array_search($newStatus, self::STATUS_ORDER, true);

        // Enforce linear forward-only check
        if ($newIndex <= $currentIndex) {
            throw ValidationException::withMessages([
                'status' => "Transisi status tidak valid. Status saat ini: {$order->production_status}. Status baru harus lebih maju.",
            ]);
        }

        // Enforce strictly 1-step transition (unless Developer/Owner/Super_Admin)
        if (! $user->hasAnyRole($this->superRoles) && $newIndex !== $currentIndex + 1) {
            $expectedNext = self::STATUS_ORDER[$currentIndex + 1] ?? 'SELESAI';
            throw ValidationException::withMessages([
                'status' => "Transisi tidak boleh melompat. Langkah selanjutnya yang diwajibkan: {$expectedNext}.",
            ]);
        }

        DB::transaction(function () use ($order, $newStatus, $notes, $user) {
            $oldStatus = $order->production_status;

            $order->update([
                'production_status' => $newStatus,
            ]);

            ProductionStatusLog::create([
                'order_id' => $order->id,
                'status' => $newStatus,
                'updated_by' => $user->id,
                'notes' => $notes ?? "Perubahan status dari {$oldStatus} ke {$newStatus}.",
            ]);

            // If production is taken, mark paid if cash and update payment status
            if ($newStatus === 'DIAMBIL' && $order->payment_status !== 'paid') {
                if ($order->paid_amount >= $order->total) {
                    $order->update(['payment_status' => 'paid', 'paid_at' => now()]);
                }
            }

            // Log activity to audit_logs (short action to fit column limit safely)
            $this->auditLogService->log("prod_status_{$newStatus}", $order, ['production_status' => $oldStatus], ['production_status' => $newStatus]);
        });

        return $order->fresh();
    }
}
