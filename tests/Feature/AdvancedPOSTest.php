<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\DraftOrder;
use App\Models\Order;
use App\Models\PettyCashRecord;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdvancedPOSTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected Branch $branch;

    protected Service $service;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Cashier']);

        $this->branch = Branch::create([
            'name' => 'Samarinda Kota',
            'code' => 'SMD01',
            'address' => 'Jl. Bhayangkara No. 1, Samarinda',
            'phone' => '081122334455',
            'is_active' => true,
        ]);

        $this->cashier = User::create([
            'name' => 'Demo Kasir POS',
            'email' => 'kasirpos@istanalaundry.com',
            'password' => bcrypt('password'),
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->cashier->assignRole('Cashier');

        $this->service = Service::create([
            'name' => 'Cuci Express 6 Jam',
            'type' => 'kilogram',
            'unit' => 'kg',
            'base_price' => 15000,
            'est_duration_hours' => 6,
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'branch_id' => $this->branch->id,
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'member_code' => 'CUST-SMD-001',
            'loyalty_tier' => 'Bronze',
            'loyalty_points' => 100,
        ]);
    }

    public function test_cashier_can_open_and_close_shift(): void
    {
        $this->actingAs($this->cashier);

        // 1. Open shift
        $response = $this->post(route('pos.shift.open'), [
            'opening_cash' => 200000,
            'notes' => 'Modal awal kasir',
        ]);

        $response->assertRedirect(route('pos.index'));
        $this->assertDatabaseHas('cashier_shifts', [
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'opening_cash' => 200000,
            'status' => 'OPEN',
        ]);

        $shift = CashierShift::first();

        // 2. Close shift
        $closeResponse = $this->post(route('pos.shift.close'), [
            'closing_cash_actual' => 200000,
            'notes' => 'Closing shift normal',
        ]);

        $closeResponse->assertRedirect(route('pos.index'));
        $this->assertDatabaseHas('cashier_shifts', [
            'id' => $shift->id,
            'status' => 'CLOSED',
            'closing_cash_actual' => 200000,
        ]);
    }

    public function test_cashier_can_record_petty_cash(): void
    {
        $this->actingAs($this->cashier);

        $shift = CashierShift::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'opened_at' => now(),
            'opening_cash' => 300000,
            'status' => 'OPEN',
        ]);

        $response = $this->post(route('pos.petty-cash.store'), [
            'category' => 'Perlengkapan',
            'amount' => 50000,
            'description' => 'Beli plastik laundry 5kg',
        ]);

        $response->assertRedirect(route('pos.index'));
        $this->assertDatabaseHas('petty_cash_records', [
            'cashier_shift_id' => $shift->id,
            'amount' => 50000,
            'description' => 'Beli plastik laundry 5kg',
        ]);

        $this->assertEquals(50000, $shift->fresh()->petty_cash_total);
    }

    public function test_cashier_can_create_and_delete_hold_order_draft(): void
    {
        $this->actingAs($this->cashier);

        // 1. Create draft
        $response = $this->postJson(route('pos.drafts.store'), [
            'draft_name' => 'Order Pak Budi',
            'customer_id' => $this->customer->id,
            'cart_data' => [
                ['service_id' => $this->service->id, 'quantity' => 3, 'price' => 15000]
            ],
            'total_amount' => 45000,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $draft = DraftOrder::first();
        $this->assertEquals('Order Pak Budi', $draft->draft_name);

        // 2. Delete draft
        $deleteResponse = $this->deleteJson(route('pos.drafts.destroy', $draft->id));
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('draft_orders', ['id' => $draft->id]);
    }

    public function test_cashier_can_process_order_with_shift_linkage(): void
    {
        $this->actingAs($this->cashier);

        $shift = CashierShift::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'opened_at' => now(),
            'opening_cash' => 200000,
            'status' => 'OPEN',
        ]);

        $response = $this->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'items' => [
                ['service_id' => $this->service->id, 'quantity' => 2]
            ],
            'payment_method' => 'cash',
            'paid_amount' => 30000,
        ]);

        $response->assertRedirect(route('pos.index'));
        
        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals($shift->id, $order->cashier_shift_id);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertDatabaseHas('order_payments', [
            'order_id' => $order->id,
            'payment_method' => 'CASH',
            'amount' => 30000,
        ]);
    }

    public function test_cashier_can_export_shift_summary_pdf(): void
    {
        $this->actingAs($this->cashier);

        $shift = CashierShift::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'opened_at' => now(),
            'closed_at' => now(),
            'opening_cash' => 200000,
            'closing_cash_system' => 250000,
            'closing_cash_actual' => 250000,
            'status' => 'CLOSED',
        ]);

        $response = $this->get(route('pos.shift.summary-pdf', $shift->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
