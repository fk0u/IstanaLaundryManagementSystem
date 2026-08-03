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

    public function test_promo_and_redeem_points_prevents_earning_new_points()
    {
        $loyaltyService = app(\App\Services\CRM\LoyaltyService::class);

        // Order 1: With points redemption
        $orderRedeem = Order::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'cashier_id' => $this->cashier->id,
            'order_number' => 'ORD-TEST-001',
            'subtotal' => 50000,
            'discount_amount' => 0,
            'points_used' => 50,
            'total' => 49950,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'paid_amount' => 50000,
            'production_status' => 'TERIMA',
        ]);

        $logRedeem = $loyaltyService->awardPoints($orderRedeem);
        $this->assertNull($logRedeem, 'Order with points_used > 0 should not earn new loyalty points');

        // Order 2: With promo code / coupon discount
        $orderPromoOnly = Order::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'cashier_id' => $this->cashier->id,
            'order_number' => 'ORD-TEST-002',
            'subtotal' => 50000,
            'discount_amount' => 5000,
            'points_used' => 0,
            'total' => 45000,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'paid_amount' => 45000,
            'production_status' => 'TERIMA',
        ]);

        $logPromo = $loyaltyService->awardPoints($orderPromoOnly);
        $this->assertNull($logPromo, 'Order with promo code / coupon discount should not earn new loyalty points');

        // Order 3: Normal order without promo/coupon or points redemption
        $orderNormal = Order::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'cashier_id' => $this->cashier->id,
            'order_number' => 'ORD-TEST-003',
            'subtotal' => 50000,
            'discount_amount' => 0,
            'points_used' => 0,
            'total' => 50000,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'paid_amount' => 50000,
            'production_status' => 'TERIMA',
        ]);

        $logNormal = $loyaltyService->awardPoints($orderNormal);
        $this->assertNotNull($logNormal, 'Normal order without promo or points redemption should earn loyalty points');
        $this->assertEquals(50, $logNormal->points);
    }
}
