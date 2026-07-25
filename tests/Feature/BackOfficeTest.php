<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\InventoryItem;
use App\Models\InventoryBatch;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceivedNote;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Order;
use App\Models\Service;
use App\Models\AccountingPeriod;
use App\Models\Journal;
use App\Services\Inventory\InventoryService;
use App\Services\Finance\JournalService;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\AccountingPeriodClosedException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackOfficeTest extends TestCase
{
    use RefreshDatabase;

    protected $developerUser;
    protected $branch;
    protected $inventoryService;
    protected $journalService;

    protected function setUp(): void
    {
        parent::setUp();

        // Instantiate services
        $this->inventoryService = app(InventoryService::class);
        $this->journalService = app(JournalService::class);

        // Create branch
        $this->branch = Branch::create([
            'code' => 'SMD01',
            'name' => 'Samarinda Central',
            'address' => 'Jl. Juanda No. 10',
            'phone' => '08111222333',
            'email' => 'smd01@istanalaundry.com',
            'is_active' => true,
        ]);

        // Create developer user
        Role::create(['name' => 'Developer']);
        $this->developerUser = User::create([
            'name' => 'Dev Antigravity',
            'email' => 'developer@istanalaundry.com',
            'password' => bcrypt('password'),
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->developerUser->assignRole('Developer');

        // Seed COA standard accounts for testing journals
        $coaAccounts = [
            ['code' => '1-1101', 'name' => 'Kas Kecil', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1-1201', 'name' => 'Piutang Usaha', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1-1301', 'name' => 'Persediaan Bahan Habis Pakai', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '2-1101', 'name' => 'Hutang Usaha', 'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '4-1001', 'name' => 'Pendapatan Jasa Laundry', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '5-4105', 'name' => 'Beban Marketing & Promosi', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '2-2101', 'name' => 'Hutang PPN', 'type' => 'liability', 'normal_balance' => 'credit'],
        ];

        foreach ($coaAccounts as $acc) {
            ChartOfAccount::create([
                'code' => $acc['code'],
                'name' => $acc['name'],
                'type' => $acc['type'],
                'normal_balance' => $acc['normal_balance'],
                'level' => 3,
                'is_active' => true,
                'is_system' => true
            ]);
        }
    }

    public function test_fifo_stock_deduction_order()
    {
        $this->actingAs($this->developerUser);

        // Create supplier, PO, and GRN to associate with the batch
        $supplier = Supplier::create([
            'name' => 'Supplier Utama Deterjen',
            'phone' => '0812345678',
            'email' => 'supplier@deterjen.com',
            'address' => 'Surabaya',
        ]);

        $po = PurchaseOrder::create([
            'branch_id' => $this->branch->id,
            'supplier_id' => $supplier->id,
            'po_number' => 'PO-TEST-001',
            'status' => 'confirmed',
            'subtotal' => 100000,
            'tax_amount' => 11000,
            'total' => 111000,
            'order_date' => now()->toDateString(),
            'expected_date' => now()->toDateString(),
        ]);

        $grn = GoodsReceivedNote::create([
            'po_id' => $po->id,
            'grn_number' => 'GRN-TEST-001',
            'status' => 'confirmed',
            'received_by' => $this->developerUser->id,
            'received_date' => now()->toDateString(),
        ]);

        // Create inventory item
        $item = InventoryItem::create([
            'branch_id' => $this->branch->id,
            'name' => 'Deterjen Liquid Lavender',
            'sku' => 'DET-LAV-01',
            'category' => 'deterjen',
            'unit' => 'Liter',
            'min_stock' => 10,
            'current_stock' => 0,
        ]);

        // Create two batches with different dates and costs
        // Batch 1 (oldest): 50 Liters @ Rp 10.000
        $batch1 = InventoryBatch::create([
            'item_id' => $item->id,
            'grn_id' => $grn->id,
            'batch_number' => 'BATCH-1',
            'quantity' => 50,
            'remaining_qty' => 50,
            'unit_cost' => 10000,
            'received_date' => now()->subDays(5)->toDateString(),
        ]);

        // Batch 2: 100 Liters @ Rp 12.000
        $batch2 = InventoryBatch::create([
            'item_id' => $item->id,
            'grn_id' => $grn->id,
            'batch_number' => 'BATCH-2',
            'quantity' => 100,
            'remaining_qty' => 100,
            'unit_cost' => 12000,
            'received_date' => now()->subDays(2)->toDateString(),
        ]);

        // Total stock now is 150
        $item->update(['current_stock' => 150]);

        // Deduct 75 Liters using FIFO
        // It should take 50 Liters from Batch 1 and 25 Liters from Batch 2
        // Expected COGS = (50 * 10000) + (25 * 12000) = 500000 + 300000 = 800000
        $result = $this->inventoryService->updateStock($item->id, -75, 'OrderUsage', 1);

        $this->assertEquals(75, $result['total_quantity']);
        $this->assertEquals(800000, $result['total_cogs']);

        // Verify remaining batch quantities in DB
        $batch1->refresh();
        $batch2->refresh();
        $this->assertEquals(0, (float)$batch1->remaining_qty);
        $this->assertEquals(75, (float)$batch2->remaining_qty);

        // Verify item stock in DB
        $item->refresh();
        $this->assertEquals(75, (float)$item->current_stock);
    }

    public function test_insufficient_stock_throws_exception()
    {
        $item = InventoryItem::create([
            'branch_id' => $this->branch->id,
            'name' => 'Deterjen',
            'sku' => 'DET-01',
            'category' => 'deterjen',
            'unit' => 'Liter',
            'min_stock' => 5,
            'current_stock' => 10,
        ]);

        $this->expectException(InsufficientStockException::class);
        $this->inventoryService->updateStock($item->id, -20, 'ManualCorrection', 1);
    }

    public function test_auto_journal_balance_and_order_observer()
    {
        $this->actingAs($this->developerUser);

        // Create standard service with required fields
        $service = Service::create([
            'name' => 'Cuci Kering Setrika 2 Hari',
            'type' => 'kilogram',
            'unit' => 'Kg',
            'base_price' => 10000,
            'is_active' => true,
        ]);

        // Create order with required cashier_id
        $order = Order::create([
            'branch_id' => $this->branch->id,
            'customer_id' => null,
            'cashier_id' => $this->developerUser->id,
            'order_number' => 'SMD01-202607-0001',
            'subtotal' => 50000,
            'discount_amount' => 5000,
            'tax_amount' => 4950, // PPN 11% of subtotal-discount
            'total' => 49950,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'production_status' => 'TERIMA',
            'status' => 'active'
        ]);

        // Trigger payment update -> fires OrderObserver
        $order->update(['payment_status' => 'paid']);

        // Assert journal was posted
        $journal = Journal::where('source_type', Order::class)->where('source_id', $order->id)->first();
        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);

        // Assert lines Debit = Credit balance
        $totalDebit = $journal->journalLines()->sum('debit');
        $totalCredit = $journal->journalLines()->sum('credit');
        $this->assertEquals($totalDebit, $totalCredit);
        $this->assertEquals(54950, (float)$totalDebit); // Kas (49950) + Diskon (5000) = Pendapatan (50000) + PPN (4950)
    }

    public function test_closed_accounting_period_prevents_journal_posting()
    {
        $this->actingAs($this->developerUser);

        // Close current accounting period
        $period = AccountingPeriod::create([
            'branch_id' => $this->branch->id,
            'year' => now()->year,
            'month' => now()->month,
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $this->developerUser->id
        ]);

        $order = Order::create([
            'branch_id' => $this->branch->id,
            'customer_id' => null,
            'cashier_id' => $this->developerUser->id,
            'order_number' => 'SMD01-202607-0002',
            'subtotal' => 20000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 20000,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'production_status' => 'TERIMA',
            'status' => 'active'
        ]);

        // Attempting to pay order (which auto-posts journal) should throw AccountingPeriodClosedException
        $this->expectException(AccountingPeriodClosedException::class);
        $this->journalService->postOrderJournal($order);
    }
}
