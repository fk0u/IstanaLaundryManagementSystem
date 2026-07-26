<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackfillOrderJournalsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Developer']);

        $this->branch = Branch::create([
            'code' => 'SMD01',
            'name' => 'Samarinda Central',
            'address' => 'Jl. Juanda No. 10',
            'phone' => '08111222333',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Dev Antigravity',
            'email' => 'developer@istanalaundry.com',
            'password' => bcrypt('password'),
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->user->assignRole('Developer');

        foreach ([
            ['code' => '1-1101', 'name' => 'Kas Kecil', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '4-1001', 'name' => 'Pendapatan Jasa Laundry', 'type' => 'revenue', 'normal_balance' => 'credit'],
        ] as $acc) {
            ChartOfAccount::create([...$acc, 'level' => 3, 'is_active' => true, 'is_system' => true]);
        }
    }

    public function test_backfill_posts_journals_for_paid_orders_missing_one(): void
    {
        $this->actingAs($this->user);

        // Simulate legacy/seeded orders inserted before the OrderObserver fix
        // (payment_status already 'paid' at creation, but no journal exists).
        $order = Order::create([
            'order_number' => 'SMD01-202607-5001',
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->user->id,
            'production_status' => 'TERIMA',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'subtotal' => 40000,
            'total' => 40000,
        ]);

        // Force-remove any journal the (fixed) observer may have just posted,
        // to specifically exercise the backfill path in isolation.
        Journal::where('source_type', Order::class)->where('source_id', $order->id)->delete();
        $this->assertDatabaseCount('journals', 0);

        $this->artisan('finance:backfill-order-journals')
            ->expectsOutputToContain('1 journal(s) posted, 0 failed')
            ->assertSuccessful();

        $journal = Journal::where('source_type', Order::class)->where('source_id', $order->id)->first();
        $this->assertNotNull($journal);
        $this->assertEquals('posted', $journal->status);

        // Running it again must be idempotent (no duplicate journals).
        $this->artisan('finance:backfill-order-journals')
            ->expectsOutputToContain('No paid orders are missing a journal entry')
            ->assertSuccessful();

        $this->assertDatabaseCount('journals', 1);
    }

    public function test_backfill_dry_run_does_not_write_anything(): void
    {
        $this->actingAs($this->user);

        $order = Order::create([
            'order_number' => 'SMD01-202607-5002',
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->user->id,
            'production_status' => 'TERIMA',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'subtotal' => 15000,
            'total' => 15000,
        ]);

        Journal::where('source_type', Order::class)->where('source_id', $order->id)->delete();

        $this->artisan('finance:backfill-order-journals', ['--dry-run' => true])
            ->assertSuccessful();

        $this->assertDatabaseCount('journals', 0);
    }
}
