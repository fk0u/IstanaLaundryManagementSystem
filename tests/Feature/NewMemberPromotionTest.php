<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewMemberPromotionTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Branch $branch;
    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Cabang Utama Test',
            'code' => 'CBG-TEST-01',
            'address' => 'Jl. Test No. 123',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Cashier']);

        $this->cashier = User::factory()->create([
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->cashier->assignRole('Cashier');

        $this->service = Service::create([
            'branch_id' => $this->branch->id,
            'name' => 'Cuci Kiloan Test',
            'type' => 'kilogram',
            'unit' => 'Kg',
            'base_price' => 50000,
            'is_active' => true,
        ]);
    }

    public function test_new_member_promo_allows_fresh_member_and_blocks_repeat_or_old_member(): void
    {
        // 1. Create New Member Promo (Target: new_member_only, Max Age: 60 days, Limit: 1x per member)
        $newMemberPromo = Promotion::create([
            'branch_id' => $this->branch->id,
            'name' => 'Diskon Member Baru 20%',
            'code' => 'NEWBARU20',
            'type' => 'percent',
            'value' => 20,
            'min_transaction' => 30000,
            'target_customer_type' => 'new_member_only',
            'max_member_age_days' => 60,
            'per_customer_limit' => 1,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'is_active' => true,
        ]);

        // 2. Fresh Member (Registered 5 days ago)
        $freshMember = Customer::create([
            'branch_id' => $this->branch->id,
            'name' => 'Budi Member Baru',
            'phone' => '081299990001',
            'created_at' => now()->subDays(5),
        ]);

        // 3. Old Member (Registered 90 days ago)
        $oldMember = Customer::create([
            'branch_id' => $this->branch->id,
            'name' => 'Siti Member Lama',
            'phone' => '081299990002',
        ]);
        Customer::where('id', $oldMember->id)->update(['created_at' => now()->subDays(90)]);
        $oldMember->refresh();

        // Test A: Fresh Member CAN apply new member promo
        $responseA = $this->actingAs($this->cashier)
            ->withSession(['scoped_branch_id' => $this->branch->id])
            ->post(route('pos.store'), [
                'customer_id' => $freshMember->id,
                'order_type' => 'outlet',
                'items' => [
                    ['service_id' => $this->service->id, 'quantity' => 1],
                ],
                'promo_id' => $newMemberPromo->id,
                'payment_method' => 'cash',
                'paid_amount' => 40000, // 50,000 - 20% (10,000) = 40,000
            ]);

        $responseA->assertRedirect(route('pos.index'));
        $responseA->assertSessionHas('success');

        // Test B: Fresh Member trying to use the same promo a 2nd time is BLOCKED
        $responseB = $this->actingAs($this->cashier)
            ->withSession(['scoped_branch_id' => $this->branch->id])
            ->post(route('pos.store'), [
                'customer_id' => $freshMember->id,
                'order_type' => 'outlet',
                'items' => [
                    ['service_id' => $this->service->id, 'quantity' => 1],
                ],
                'promo_id' => $newMemberPromo->id,
                'payment_method' => 'cash',
                'paid_amount' => 50000,
            ]);

        $responseB->assertSessionHasErrors();

        // Test C: Old Member (> 60 days) trying to use new member promo is BLOCKED
        $responseC = $this->actingAs($this->cashier)
            ->withSession(['scoped_branch_id' => $this->branch->id])
            ->post(route('pos.store'), [
                'customer_id' => $oldMember->id,
                'order_type' => 'outlet',
                'items' => [
                    ['service_id' => $this->service->id, 'quantity' => 1],
                ],
                'promo_id' => $newMemberPromo->id,
                'payment_method' => 'cash',
                'paid_amount' => 50000,
            ]);

        $responseC->assertSessionHasErrors();

        // Test D: Walk-In (no customer_id) trying to use new member promo is BLOCKED
        $responseD = $this->actingAs($this->cashier)
            ->withSession(['scoped_branch_id' => $this->branch->id])
            ->post(route('pos.store'), [
                'customer_id' => null,
                'customer_name_walkin' => 'Pelanggan Umum',
                'order_type' => 'outlet',
                'items' => [
                    ['service_id' => $this->service->id, 'quantity' => 1],
                ],
                'promo_id' => $newMemberPromo->id,
                'payment_method' => 'cash',
                'paid_amount' => 50000,
            ]);

        $responseD->assertSessionHasErrors();
    }
}
