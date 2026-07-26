<?php

namespace Tests\Feature\Api;

use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Cashier']);
        Role::create(['name' => 'Developer']);

        $this->branch = Branch::create([
            'name' => 'Samarinda Kota',
            'code' => 'SMD01',
            'address' => 'Jl. Bhayangkara No. 1, Samarinda',
            'phone' => '081122334455',
            'is_active' => true,
        ]);

        $this->cashier = User::create([
            'name' => 'Demo Kasir',
            'email' => 'api-cashier@istanalaundry.com',
            'password' => bcrypt('password'),
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->cashier->assignRole('Cashier');
    }

    public function test_login_issues_a_token(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => $this->cashier->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'email', 'roles']]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => $this->cashier->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_production_endpoints_require_authentication(): void
    {
        $this->getJson('/api/production')->assertStatus(401);
    }

    public function test_can_update_production_status_to_kering_and_diambil_via_api(): void
    {
        $this->cashier->assignRole('Developer');

        $keringOrder = Order::create([
            'order_number' => 'SMD01-202607-8001',
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'production_status' => 'CUCI',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'subtotal' => 10000,
            'total' => 10000,
        ]);

        $diambilOrder = Order::create([
            'order_number' => 'SMD01-202607-8002',
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
                ->patchJson("/api/production/{$order->id}/status", ['status' => $status]);

            $response->assertStatus(200);
            $response->assertJsonPath('data.production_status', $status);

            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'production_status' => $status,
            ]);
            $this->assertDatabaseHas('audit_logs', [
                'model_id' => $order->id,
                'action' => "prod_status_{$status}",
            ]);
        }
    }

    public function test_non_linear_status_jump_is_rejected_via_api(): void
    {
        $order = Order::create([
            'order_number' => 'SMD01-202607-8003',
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'production_status' => 'TERIMA',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'subtotal' => 10000,
            'total' => 10000,
        ]);

        $response = $this->actingAs($this->cashier)
            ->patchJson("/api/production/{$order->id}/status", ['status' => 'CUCI']);

        $response->assertStatus(422);
        $order->refresh();
        $this->assertEquals('TERIMA', $order->production_status);
    }

    public function test_public_order_tracking_endpoint(): void
    {
        $order = Order::create([
            'order_number' => 'SMD01-202607-8004',
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->cashier->id,
            'production_status' => 'CUCI',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'subtotal' => 10000,
            'total' => 10000,
        ]);

        $response = $this->getJson('/api/track/'.$order->order_number);

        $response->assertStatus(200);
        $response->assertJsonPath('data.order_number', $order->order_number);
    }

    public function test_public_order_tracking_returns_404_for_unknown_order(): void
    {
        $this->getJson('/api/track/UNKNOWN-NUMBER')->assertStatus(404);
    }
}
