<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;
    protected Service $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->branch = Branch::create([
            'name' => 'Istana Laundry Samarinda Pusat',
            'code' => 'WJK',
            'address' => 'Jl. Wijaya Kusuma Blok V-C Gg. Rina',
            'phone' => '08115550001',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'branch_id' => $this->branch->id,
            'name' => 'Kasir Admin',
        ]);
        $this->user->assignRole('Cashier');

        $this->service = Service::create([
            'branch_id' => $this->branch->id,
            'name' => 'Cuci Kiloan Express',
            'type' => 'kilogram',
            'unit' => 'kg',
            'base_price' => 18000,
            'is_active' => true,
        ]);
    }

    public function test_can_fetch_public_branches_list()
    {
        $response = $this->getJson(route('api.v1.branches'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonFragment([
                'code' => 'WJK',
                'name' => 'Istana Laundry Samarinda Pusat',
            ]);
    }

    public function test_can_fetch_public_services_list()
    {
        $response = $this->getJson(route('api.v1.services', ['branch_id' => $this->branch->id]));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonFragment([
                'name' => 'Cuci Kiloan Express',
                'price' => 18000,
            ]);
    }

    public function test_can_submit_online_order_with_gps_coordinates()
    {
        $payload = [
            'branch_code' => 'WJK',
            'customer_name' => 'Bpk. Ghani',
            'customer_phone' => '081234567890',
            'delivery_address' => 'Jl. Air Hitam No. 88, Samarinda',
            'latitude' => -0.4851234,
            'longitude' => 117.1423456,
            'service_id' => $this->service->id,
            'quantity' => 5,
            'notes' => 'Tolong antar jam 5 sore',
        ];

        $response = $this->postJson(route('api.v1.orders.online'), $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Pemesanan online berhasil dibuat!',
            ]);

        $orderNumber = $response->json('data.order_number');
        $this->assertNotEmpty($orderNumber);
        $this->assertStringContainsString('ONLINE-WJK-', $orderNumber);

        $this->assertDatabaseHas('orders', [
            'order_number' => $orderNumber,
            'order_type' => 'pickup_delivery',
            'delivery_address' => 'Jl. Air Hitam No. 88, Samarinda',
            'latitude' => -0.4851234,
            'longitude' => 117.1423456,
        ]);

        $this->assertNotNull($response->json('data.whatsapp_url'));
        $this->assertNotNull($response->json('data.tracking_url'));
    }

    public function test_can_track_order_status_by_order_number()
    {
        $order = Order::create([
            'order_number' => 'WJK-202608-9999',
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->user->id,
            'order_type' => 'pickup_delivery',
            'customer_name_walkin' => 'Ibu Siti',
            'delivery_address' => 'Jl. Pahlawan No. 12',
            'latitude' => -0.4900000,
            'longitude' => 117.1500000,
            'google_maps_url' => 'https://www.google.com/maps?q=-0.4900000,117.1500000',
            'production_status' => 'CUCI',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'subtotal' => 90000,
            'total' => 90000,
            'paid_amount' => 90000,
            'estimated_done_at' => now()->addDays(1),
        ]);

        $response = $this->getJson(route('api.v1.track', ['orderNumber' => 'WJK-202608-9999']));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'order_number' => 'WJK-202608-9999',
                    'production_status' => 'CUCI',
                    'payment_status' => 'paid',
                ],
            ]);
    }
}
