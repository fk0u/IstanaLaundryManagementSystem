<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullRestApiTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;
    protected User $user;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->branch = Branch::create([
            'name' => 'Samarinda Utama',
            'code' => 'SMD',
            'address' => 'Jl. Pahlawan No. 1',
            'phone' => '08115550001',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'branch_id' => $this->branch->id,
            'name' => 'Administrator Admin',
        ]);
        $this->user->assignRole('Developer');

        $this->token = $this->user->createToken('test_token')->plainTextToken;
    }

    public function test_dashboard_api_returns_kpi_stats()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson(route('api.v1.dashboard.stats'));

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    public function test_orders_api_returns_paginated_orders()
    {
        Order::create([
            'order_number' => 'WJK-TEST-0001',
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->user->id,
            'order_type' => 'outlet',
            'production_status' => 'TERIMA',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'subtotal' => 50000,
            'total' => 50000,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson(route('api.v1.orders.index'));

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    public function test_customers_api_can_create_and_list_members()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson(route('api.v1.customers.store'), [
                'name' => 'Bpk. Ahmad',
                'phone' => '081299988877',
                'address' => 'Samarinda Seberang',
            ]);

        $response->assertStatus(201)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('customers', ['phone' => '081299988877']);
    }

    public function test_inventory_api_can_create_and_list_items()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson(route('api.v1.inventory.store'), [
                'name' => 'Detergen Premium 20L',
                'sku' => 'DET-20L',
                'category' => 'Bahan Kimia',
                'unit' => 'Jerigen',
                'min_stock' => 5,
                'current_stock' => 20,
            ]);

        $response->assertStatus(201)
            ->assertJson(['status' => 'success']);
    }

    public function test_finance_api_returns_coa_list()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson(route('api.v1.finance.coa.index'));

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }
}
