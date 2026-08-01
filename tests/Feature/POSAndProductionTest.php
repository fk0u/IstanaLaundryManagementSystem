<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class POSAndProductionTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected Branch $branch;

    protected Service $service;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Roles
        Role::create(['name' => 'Cashier']);
        Role::create(['name' => 'Developer']);

        // 2. Setup Branch
        $this->branch = Branch::create([
            'name' => 'Samarinda Kota',
            'code' => 'SMD01',
            'address' => 'Jl. Bhayangkara No. 1, Samarinda',
            'phone' => '081122334455',
            'is_active' => true,
        ]);

        // 3. Setup User
        $this->cashier = User::create([
            'name' => 'Demo Kasir',
            'email' => 'cashier@istanalaundry.com',
            'password' => bcrypt('password'),
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->cashier->assignRole('Cashier');

        // 4. Setup Service
        $this->service = Service::create([
            'name' => 'Cuci Kiloan Premium',
            'type' => 'kilogram',
            'unit' => 'kg',
            'base_price' => 10000,
            'est_duration_hours' => 24,
            'is_active' => true,
        ]);

        // 5. Setup Customer
        $this->customer = Customer::create([
            'branch_id' => $this->branch->id,
            'name' => 'Budi Santoso',
            'phone' => '08123456789',
            'member_code' => 'MEM0001',
            'loyalty_points' => 100,
            'loyalty_tier' => 'Bronze',
        ]);
    }

    /**
     * Test POS page can be rendered.
     */
    public function test_pos_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->cashier)
            ->get(route('pos.index'));

        $response->assertStatus(200);
        $response->assertViewHas('services');
        $response->assertViewHas('customers');
    }

    /**
     * Test POS Order creation with calculation.
     */
    public function test_pos_can_create_order_successfully(): void
    {
        // Setup Promotion
        $promo = Promotion::create([
            'name' => 'Diskon Pembukaan',
            'code' => 'PROMO10',
            'type' => 'percent',
            'value' => 10,
            'min_transaction' => 20000,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->cashier)
            ->post(route('pos.store'), [
                'customer_id' => $this->customer->id,
                'items' => [
                    [
                        'service_id' => $this->service->id,
                        'quantity' => 3, // Total: 3 * 10000 = 30000
                    ],
                ],
                'promo_id' => $promo->id, // 10% discount = 3000
                'points_used' => 50, // 50 Rp discount
                'payment_method' => 'cash',
                'paid_amount' => 30000, // Subtotal 30000 - 3000 - 50 = 26950
            ]);

        $response->assertRedirect(route('pos.index'));
        $response->assertSessionHas('success');

        // Verify Order in DB
        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals('SMD01-'.now()->format('Ym').'-0001', $order->order_number);
        $this->assertEquals(30000, $order->subtotal);
        $this->assertEquals(3000, $order->discount_amount);
        $this->assertEquals(50, $order->points_used);
        $this->assertEquals(26950, $order->total);
        $this->assertEquals('paid', $order->payment_status);

        // Verify Customer loyalty points (100 - 50 used + 0 earned = 50 points, because point redemption orders do not earn new points)
        $this->customer->refresh();
        $this->assertEquals(50, $this->customer->loyalty_points);
    }

    /**
     * Test linear transition verification of production status.
     */
    public function test_production_status_updates_to_kering_and_diambil_are_audited(): void
    {
        $this->cashier->assignRole('Developer');

        $keringOrder = Order::create([
            'order_number' => 'SMD01-202607-9998',
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'production_status' => 'CUCI',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'subtotal' => 10000,
            'total' => 10000,
        ]);

        $diambilOrder = Order::create([
            'order_number' => 'SMD01-202607-9997',
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'production_status' => 'SIAP',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'subtotal' => 10000,
            'total' => 10000,
        ]);

        foreach ([[$keringOrder, 'KERING'], [$diambilOrder, 'DIAMBIL']] as [$order, $status]) {
            $response = $this->actingAs($this->cashier)
                ->post(route('production.update', $order->id), ['status' => $status]);

            $response->assertRedirect(route('production.index'));
            $response->assertSessionHas('success');
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'production_status' => $status,
            ]);
            $this->assertDatabaseHas('production_status_logs', [
                'order_id' => $order->id,
                'status' => $status,
                'updated_by' => $this->cashier->id,
            ]);
            $this->assertDatabaseHas('audit_logs', [
                'model_id' => $order->id,
                'action' => "prod_status_{$status}",
            ]);
        }
    }

    /**
     * Test linear transition verification of production status.
     */
    public function test_production_status_transitions_must_be_linear_forward(): void
    {
        // 1. Create order with status TERIMA
        $order = Order::create([
            'order_number' => 'SMD01-202607-9999',
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'production_status' => 'TERIMA',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'subtotal' => 10000,
            'total' => 10000,
        ]);

        // 2. Try non-linear forward jump (TERIMA -> CUCI, skipping PILAH)
        // Since we restricted skips for non-super users (our cashier role is not super)
        $response = $this->actingAs($this->cashier)
            ->post(route('production.update', $order->id), [
                'status' => 'CUCI',
            ]);

        $response->assertSessionHas('error');
        $order->refresh();
        $this->assertEquals('TERIMA', $order->production_status); // Did not change

        // 3. Valid exact 1-step transition (TERIMA -> PILAH)
        $response = $this->actingAs($this->cashier)
            ->post(route('production.update', $order->id), [
                'status' => 'PILAH',
            ]);

        $response->assertSessionHas('success');
        $order->refresh();
        $this->assertEquals('PILAH', $order->production_status);

        // 4. Try backward transition (PILAH -> TERIMA)
        $response = $this->actingAs($this->cashier)
            ->post(route('production.update', $order->id), [
                'status' => 'TERIMA',
            ]);

        $response->assertSessionHas('error');
        $order->refresh();
        $this->assertEquals('PILAH', $order->production_status); // Stayed PILAH
    }

    /**
     * Test that the Production board hides DIAMBIL orders by default, but
     * shows them when explicitly filtered.
     */
    public function test_production_index_hides_diambil_by_default_but_filter_shows_it(): void
    {
        $activeOrder = Order::create([
            'order_number' => 'SMD01-202607-7001',
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'production_status' => 'CUCI',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'subtotal' => 10000,
            'total' => 10000,
        ]);

        $diambilOrder = Order::create([
            'order_number' => 'SMD01-202607-7002',
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'production_status' => 'DIAMBIL',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'subtotal' => 10000,
            'total' => 10000,
        ]);

        // Default view: DIAMBIL is hidden, active order is shown.
        $response = $this->actingAs($this->cashier)->get(route('production.index'));
        $response->assertStatus(200);
        $response->assertViewHas('orders', function ($orders) use ($activeOrder, $diambilOrder) {
            return $orders->contains('id', $activeOrder->id) && ! $orders->contains('id', $diambilOrder->id);
        });

        // Explicit DIAMBIL filter: only the DIAMBIL order is shown.
        $response = $this->actingAs($this->cashier)->get(route('production.index', ['status' => 'DIAMBIL']));
        $response->assertStatus(200);
        $response->assertViewHas('orders', function ($orders) use ($activeOrder, $diambilOrder) {
            return $orders->contains('id', $diambilOrder->id) && ! $orders->contains('id', $activeOrder->id);
        });
    }

    /**
     * Test that the Production board paginates results (15 per page) instead
     * of loading every matching order at once, since DIAMBIL orders can
     * accumulate indefinitely over time.
     */
    public function test_production_index_paginates_orders(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Order::create([
                'order_number' => sprintf('SMD01-202607-80%02d', $i),
                'branch_id' => $this->branch->id,
                'cashier_id' => $this->cashier->id,
                'production_status' => 'DIAMBIL',
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'subtotal' => 10000,
                'total' => 10000,
            ]);
        }

        $response = $this->actingAs($this->cashier)->get(route('production.index', ['status' => 'DIAMBIL']));

        $response->assertStatus(200);
        $response->assertViewHas('orders', function ($orders) {
            return $orders->count() === 15 && $orders->total() === 20 && $orders->hasMorePages();
        });
    }
}
