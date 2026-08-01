<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Branch $branch;
    protected Customer $customer;
    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->branch = Branch::create([
            'name' => 'Branch Test',
            'code' => 'TST',
            'address' => 'Jl. Test No 1',
            'phone' => '08123456789',
        ]);

        $this->cashier = User::factory()->create([
            'branch_id' => $this->branch->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $this->cashier->assignRole('Cashier');

        $this->customer = Customer::create([
            'branch_id' => $this->branch->id,
            'name' => 'Pelanggan Test Promo',
            'phone' => '081299998888',
            'loyalty_points' => 100,
            'loyalty_tier' => 'Bronze',
        ]);

        $this->service = Service::create([
            'name' => 'Cuci Lipat Reguler',
            'type' => 'kilogram',
            'unit' => 'kg',
            'base_price' => 10000,
        ]);
    }

    public function test_pos_displays_active_promotions_and_can_apply_promo_code()
    {
        $promo = Promotion::create([
            'name' => 'Diskon 10%',
            'code' => 'HEMAT10',
            'type' => 'percent',
            'value' => 10,
            'min_transaction' => 20000,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->cashier)
            ->withSession(['scoped_branch_id' => $this->branch->id])
            ->get(route('pos.index'));

        $response->assertStatus(200);
        $response->assertSee('HEMAT10');

        $postData = [
            'customer_id' => $this->customer->id,
            'promo_id' => $promo->id,
            'payment_method' => 'cash',
            'paid_amount' => 50000,
            'items' => [
                [
                    'service_id' => $this->service->id,
                    'quantity' => 5, // subtotal = 50,000
                ],
            ],
        ];

        $orderResponse = $this->actingAs($this->cashier)
            ->withSession(['scoped_branch_id' => $this->branch->id])
            ->post(route('pos.store'), $postData);

        $orderResponse->assertRedirect(route('pos.index'));

        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals($promo->id, $order->promo_id);
        $this->assertEquals(5000, $order->discount_amount); // 10% of 50,000 = 5,000
        $this->assertEquals(45000, $order->total); // 50,000 - 5,000 = 45,000
    }
}
