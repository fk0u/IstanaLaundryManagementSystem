<?php

namespace App\Console\Commands;

use App\Models\Journal;
use App\Models\Order;
use App\Services\Finance\JournalService;
use Illuminate\Console\Command;

class BackfillOrderJournals extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'finance:backfill-order-journals {--dry-run : List affected orders without posting journals}';

    /**
     * The console command description.
     */
    protected $description = 'Post journal entries for existing paid orders that never received one (e.g. orders created before the OrderObserver "created" fix, or seeded demo data).';

    public function handle(JournalService $journalService): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // Orders that are paid but have no corresponding journal yet.
        $journaledOrderIds = Journal::withoutGlobalScopes()
            ->where('source_type', Order::class)
            ->pluck('source_id');

        $orders = Order::withoutGlobalScopes()
            ->where('payment_status', 'paid')
            ->whereNotIn('id', $journaledOrderIds)
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No paid orders are missing a journal entry. Nothing to do.');

            return self::SUCCESS;
        }

        $this->info("Found {$orders->count()} paid order(s) without a journal entry.");

        if ($dryRun) {
            $this->table(
                ['Order ID', 'Order Number', 'Branch ID', 'Total', 'Paid At'],
                $orders->map(fn ($o) => [$o->id, $o->order_number, $o->branch_id, $o->total, $o->paid_at])
            );

            return self::SUCCESS;
        }

        $posted = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($orders->count());
        $bar->start();

        foreach ($orders as $order) {
            try {
                $journalService->postOrderJournal($order);
                $posted++;
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->error("Order #{$order->order_number} (ID {$order->id}): {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Backfill complete: {$posted} journal(s) posted, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
