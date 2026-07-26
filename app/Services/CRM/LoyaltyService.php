<?php

namespace App\Services\CRM;

use App\Models\Customer;
use App\Models\LoyaltyPointLog;
use App\Models\Order;

class LoyaltyService
{
    /**
     * Award points to customer based on order total.
     * Ratio: 1 point per Rp 1,000.
     */
    public function awardPoints(Order $order): ?LoyaltyPointLog
    {
        if (! $order->customer_id) {
            return null;
        }

        $customer = $order->customer;
        $pointsEarned = floor($order->total / 1000);

        if ($pointsEarned <= 0) {
            return null;
        }

        $balanceBefore = $customer->loyalty_points;
        $balanceAfter = $balanceBefore + $pointsEarned;

        $customer->update([
            'loyalty_points' => $balanceAfter,
            'total_spent' => $customer->total_spent + $order->total,
            'transaction_count' => $customer->transaction_count + 1,
            'last_transaction_at' => now(),
        ]);

        $log = LoyaltyPointLog::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'points' => $pointsEarned,
            'type' => 'earn',
            'balance_after' => $balanceAfter,
            'description' => "Poin didapat dari order #{$order->order_number}",
        ]);

        $this->checkTierUpgrade($customer);

        return $log;
    }

    /**
     * Redeem loyalty points for order discount.
     * Ratio: 1 point = Rp 1 discount.
     */
    public function redeemPoints(Customer $customer, int $points, ?Order $order = null): LoyaltyPointLog
    {
        if ($customer->loyalty_points < $points) {
            throw new \InvalidArgumentException('Saldo poin tidak mencukupi.');
        }

        $balanceBefore = $customer->loyalty_points;
        $balanceAfter = $balanceBefore - $points;

        $customer->update([
            'loyalty_points' => $balanceAfter,
        ]);

        $log = LoyaltyPointLog::create([
            'customer_id' => $customer->id,
            'order_id' => $order?->id,
            'points' => -$points,
            'type' => 'redeem',
            'balance_after' => $balanceAfter,
            'description' => $order ? "Poin digunakan untuk potongan order #{$order->order_number}" : 'Penukaran poin',
        ]);

        $this->checkTierUpgrade($customer);

        return $log;
    }

    /**
     * Check and update customer loyalty tier based on current points.
     * Bronze < 1000
     * Silver 1000 - 4999
     * Gold 5000 - 9999
     * Platinum >= 10000
     */
    public function checkTierUpgrade(Customer $customer): void
    {
        $points = $customer->loyalty_points;
        $newTier = 'Bronze';

        if ($points >= 10000) {
            $newTier = 'Platinum';
        } elseif ($points >= 5000) {
            $newTier = 'Gold';
        } elseif ($points >= 1000) {
            $newTier = 'Silver';
        }

        if ($customer->loyalty_tier !== $newTier) {
            $customer->update([
                'loyalty_tier' => $newTier,
            ]);

            // Optional: log or trigger event
        }
    }

    /**
     * Deduct points from customer when an order is refunded.
     * Ratio: 1 point per Rp 1,000 refunded.
     */
    public function deductPointsForRefund(Order $order, float $refundAmount): ?LoyaltyPointLog
    {
        if (! $order->customer_id) {
            return null;
        }

        $customer = $order->customer;
        $pointsToDeduct = floor($refundAmount / 1000);

        if ($pointsToDeduct <= 0) {
            return null;
        }

        // Deduct points up to customer's current points
        $pointsToDeduct = min($pointsToDeduct, $customer->loyalty_points);

        $balanceBefore = $customer->loyalty_points;
        $balanceAfter = $balanceBefore - $pointsToDeduct;

        $customer->update([
            'loyalty_points' => $balanceAfter,
            'total_spent' => max(0, $customer->total_spent - $refundAmount),
        ]);

        $log = LoyaltyPointLog::create([
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'points' => -$pointsToDeduct,
            'type' => 'adjust',
            'balance_after' => $balanceAfter,
            'description' => "Pengurangan poin karena pengembalian dana order #{$order->order_number}",
        ]);

        $this->checkTierUpgrade($customer);

        return $log;
    }
}
