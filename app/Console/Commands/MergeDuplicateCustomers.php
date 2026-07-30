<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Order;
use App\Models\LoyaltyPointLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeDuplicateCustomers extends Command
{
    protected $signature = 'customers:merge-duplicates {--dry-run : Preview without making changes}';

    protected $description = 'Merge duplicate customers (same base phone/email) into one global record, reassigning all orders and loyalty logs';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info($dryRun ? '🔍 DRY RUN — no changes will be made.' : '🔧 Merging duplicate customers...');

        // Find customers whose phone has a branch suffix pattern like "081234567890-HID"
        $allCustomers = Customer::orderBy('id')->get();

        // Group by base phone (strip "-XXX" suffix)
        $grouped = $allCustomers->groupBy(function ($c) {
            // Strip branch suffix like "-HID", "-LMG", "-SUT", "-WJK"
            return preg_replace('/-[A-Z]{2,5}$/', '', $c->phone);
        });

        $mergedCount = 0;
        $deletedCount = 0;

        foreach ($grouped as $basePhone => $duplicates) {
            if ($duplicates->count() <= 1) {
                continue; // No duplicates
            }

            // The "primary" record is the one with the most orders, or the lowest ID
            $primary = $duplicates->sortByDesc(function ($c) {
                return $c->orders()->count();
            })->first();

            $duplicatesToMerge = $duplicates->where('id', '!=', $primary->id);

            $this->line('');
            $this->info("📌 Merging \"{$primary->name}\" (ID {$primary->id}, phone {$basePhone})");
            $this->line("   Keeping: ID {$primary->id} | Merging " . $duplicatesToMerge->count() . " duplicates");

            if ($dryRun) {
                foreach ($duplicatesToMerge as $dup) {
                    $orderCount = $dup->orders()->count();
                    $this->line("   → Would merge ID {$dup->id} \"{$dup->name}\" ({$orderCount} orders, {$dup->loyalty_points} pts)");
                }
                $mergedCount++;
                continue;
            }

            DB::transaction(function () use ($primary, $duplicatesToMerge, $basePhone, &$deletedCount) {
                $totalPointsToAdd = 0;
                $totalSpentToAdd = 0;
                $totalTxCountToAdd = 0;

                foreach ($duplicatesToMerge as $dup) {
                    // Reassign all orders from duplicate to primary
                    Order::withoutGlobalScope('branch_scope')
                        ->where('customer_id', $dup->id)
                        ->update(['customer_id' => $primary->id]);

                    // Reassign loyalty point logs
                    LoyaltyPointLog::where('customer_id', $dup->id)
                        ->update(['customer_id' => $primary->id]);

                    // Accumulate stats
                    $totalPointsToAdd += $dup->loyalty_points;
                    $totalSpentToAdd += $dup->total_spent;
                    $totalTxCountToAdd += $dup->transaction_count;

                    $this->line("   ✅ Merged ID {$dup->id} \"{$dup->name}\" → ID {$primary->id}");

                    // Delete the duplicate
                    $dup->delete();
                    $deletedCount++;
                }

                // Clean up primary record
                $primary->update([
                    'name' => preg_replace('/\s*\([A-Z]{2,5}\)$/', '', $primary->name), // Remove "(HID)" suffix
                    'phone' => $basePhone, // Remove "-HID" suffix
                    'loyalty_points' => $primary->loyalty_points + $totalPointsToAdd,
                    'total_spent' => $primary->total_spent + $totalSpentToAdd,
                    'transaction_count' => $primary->transaction_count + $totalTxCountToAdd,
                    'member_code' => 'CUST-' . str_pad($primary->id, 4, '0', STR_PAD_LEFT),
                ]);
            });

            $mergedCount++;
        }

        $this->line('');
        if ($dryRun) {
            $this->info("🔍 Would merge {$mergedCount} customer groups. Run without --dry-run to apply.");
        } else {
            $this->info("✅ Merged {$mergedCount} customer groups. Deleted {$deletedCount} duplicate records.");
        }

        return self::SUCCESS;
    }
}
