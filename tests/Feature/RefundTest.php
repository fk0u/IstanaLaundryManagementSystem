<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Journal;
use App\Models\Order;
use App\Models\Refund;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RefundTest extends TestCase
{
    use RefreshDatabase;

    protected $branch;

    protected $cashier;

    protected $branchAdmin;

    protected $finance;

    protected $owner;

    protected $customer;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Spatie Roles
        Role::create(['name' => 'Developer']);
        Role::create(['name' => 'Owner']);
        Role::create(['name' => 'Super_Admin']);
        Role::create(['name' => 'Branch_Admin']);
        Role::create(['name' => 'Cashier']);
        Role::create(['name' => 'Finance']);

        // Create Branch
        $this->branch = Branch::create([
            'code' => 'SMD01',
            'name' => 'Samarinda Central',
            'address' => 'Jl. Juanda No. 10',
            'phone' => '08111222333',
            'email' => 'smd01@istanalaundry.com',
            'is_active' => true,
        ]);

        // Create Users for each role
        $this->cashier = User::create([
            'name' => 'Siti Kasir',
            'email' => 'cashier@laundry.com',
            'password' => bcrypt('password'),
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->cashier->assignRole('Cashier');

        $this->branchAdmin = User::create([
            'name' => 'Budi Admin Cabang',
            'email' => 'branchadmin@laundry.com',
            'password' => bcrypt('password'),
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->branchAdmin->assignRole('Branch_Admin');

        $this->finance = User::create([
            'name' => 'Fani Keuangan',
            'email' => 'finance@laundry.com',
            'password' => bcrypt('password'),
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->finance->assignRole('Finance');

        $this->owner = User::create([
            'name' => 'Hendra Owner',
            'email' => 'owner@laundry.com',
            'password' => bcrypt('password'),
            'branch_id' => null,
            'is_active' => true,
        ]);
        $this->owner->assignRole('Owner');

        // Create Customer
        $this->customer = Customer::create([
            'branch_id' => $this->branch->id,
            'name' => 'Rian Pelanggan',
            'phone' => '081299998888',
            'loyalty_points' => 500, // Starts with 500 points
            'loyalty_tier' => 'Bronze',
        ]);

        // Create Service
        $this->service = Service::create([
            'name' => 'Cuci Kilat 6 Jam',
            'type' => 'kilogram',
            'unit' => 'Kg',
            'base_price' => 15000,
            'is_active' => true,
        ]);

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
                'is_system' => true,
            ]);
        }
    }

    public function test_4_stage_refund_approval_workflow()
    {
        // 1. Create a Pending Order
        $order = Order::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'cashier_id' => $this->cashier->id,
            'order_number' => 'SMD01-202607-0005',
            'subtotal' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 100000,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'production_status' => 'TERIMA',
            'status' => 'active',
        ]);

        // Update payment status to paid to trigger OrderObserver auto-post
        $order->update(['payment_status' => 'paid']);

        $this->customer->refresh();
        $this->assertEquals(600, $this->customer->loyalty_points); // 500 + 100 (auto-awarded by observer)

        // Assert auto journal entry is posted for the paid order
        $journal = Journal::where('source_type', Order::class)->where('source_id', $order->id)->first();
        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);

        // --- STAGE 1: Cashier requests refund ---
        $this->actingAs($this->cashier);
        $response = $this->post('/refunds', [
            'order_id' => $order->id,
            'amount' => 100000,
            'reason' => 'Pakaian robek dan luntur parah',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('refunds', [
            'order_id' => $order->id,
            'amount' => 100000,
            'status' => 'pending',
        ]);

        $refund = Refund::where('order_id', $order->id)->first();
        $this->assertNotNull($refund->cashier_approved_at);

        // --- STAGE 2: Branch Admin approves ---
        $this->actingAs($this->branchAdmin);
        $response = $this->post("/refunds/{$refund->id}/approve");
        $response->assertRedirect();

        $refund->refresh();
        $this->assertEquals('branch_approved', $refund->status);
        $this->assertNotNull($refund->branch_approved_at);

        // --- STAGE 3: Finance approves ---
        $this->actingAs($this->finance);
        $response = $this->post("/refunds/{$refund->id}/approve");
        $response->assertRedirect();

        $refund->refresh();
        $this->assertEquals('finance_approved', $refund->status);
        $this->assertNotNull($refund->finance_approved_at);

        // --- STAGE 4: Owner approves and completes ---
        $this->actingAs($this->owner);
        $response = $this->post("/refunds/{$refund->id}/approve");
        $response->assertRedirect();

        $refund->refresh();
        $this->assertEquals('completed', $refund->status);
        $this->assertNotNull($refund->owner_approved_at);
        $this->assertNotNull($refund->processed_at);

        // Verify Order status is now 'refunded'
        $order->refresh();
        $this->assertEquals('refunded', $order->payment_status);

        // Verify Customer loyalty points reduced proportionally (100 points deducted)
        $this->customer->refresh();
        $this->assertEquals(500, $this->customer->loyalty_points); // Should go back to 500

        // Verify Journal entry is reversed
        $journal->refresh();
        $this->assertEquals('reversed', $journal->status);

        $reversalJournal = Journal::where('reference', 'REV-'.$journal->reference)->first();
        $this->assertNotNull($reversalJournal);
        $this->assertEquals('posted', $reversalJournal->status);

        $totalRevDebit = $reversalJournal->journalLines()->sum('debit');
        $totalRevCredit = $reversalJournal->journalLines()->sum('credit');
        $this->assertEquals($totalRevDebit, $totalRevCredit);
        $this->assertEquals(100000, (float) $totalRevDebit);
    }

    public function test_non_owner_cannot_approve_final_stage()
    {
        $order = Order::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'cashier_id' => $this->cashier->id,
            'order_number' => 'SMD01-202607-0006',
            'subtotal' => 50000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 50000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'production_status' => 'TERIMA',
            'status' => 'active',
        ]);

        $refund = Refund::create([
            'order_id' => $order->id,
            'branch_id' => $order->branch_id,
            'requested_by' => $this->cashier->id,
            'amount' => 50000,
            'reason' => 'Refund test',
            'status' => 'finance_approved',
            'cashier_approved_at' => now(),
            'branch_approved_at' => now(),
            'finance_approved_at' => now(),
        ]);

        // Branch Admin tries to approve final stage (finance_approved -> completed)
        $this->actingAs($this->branchAdmin);
        $response = $this->post("/refunds/{$refund->id}/approve");
        $response->assertStatus(403); // Forbidden
    }

    public function test_reject_refund_stops_workflow()
    {
        $order = Order::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'cashier_id' => $this->cashier->id,
            'order_number' => 'SMD01-202607-0007',
            'subtotal' => 40000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 40000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'production_status' => 'TERIMA',
            'status' => 'active',
        ]);

        $refund = Refund::create([
            'order_id' => $order->id,
            'branch_id' => $order->branch_id,
            'requested_by' => $this->cashier->id,
            'amount' => 40000,
            'reason' => 'Refund test reject',
            'status' => 'pending',
            'cashier_approved_at' => now(),
        ]);

        // Branch Admin rejects it
        $this->actingAs($this->branchAdmin);
        $response = $this->post("/refunds/{$refund->id}/reject");
        $response->assertRedirect();

        $refund->refresh();
        $this->assertEquals('rejected', $refund->status);
    }
}
