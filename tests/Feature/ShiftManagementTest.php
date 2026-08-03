<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CashierShift;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\PettyCashRecord;
use App\Models\User;
use App\Services\Finance\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShiftManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Owner']);

        $this->branch = Branch::create([
            'name' => 'Pusat Wijaya',
            'code' => 'PST01',
            'address' => 'Jl. Utama',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Bambang Owner',
            'email' => 'owner@istana.com',
            'password' => bcrypt('password'),
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->user->assignRole('Owner');
    }

    public function test_can_view_shift_history_index_page(): void
    {
        $shift = CashierShift::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->user->id,
            'opened_at' => now()->subHours(8),
            'closed_at' => now(),
            'opening_cash' => 500000,
            'closing_cash_system' => 700000,
            'closing_cash_actual' => 700000,
            'cash_difference' => 0,
            'petty_cash_total' => 0,
            'status' => 'CLOSED',
        ]);

        $response = $this->actingAs($this->user)->get(route('shifts.index'));

        $response->assertStatus(200);
        $response->assertSee('Rekapitulasi &amp; Audit Shift Kasir', false);
        $response->assertSee("Shift #{$shift->id}");
    }

    public function test_can_view_shift_detail_show_page(): void
    {
        $shift = CashierShift::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->user->id,
            'opened_at' => now()->subHours(8),
            'closed_at' => now(),
            'opening_cash' => 300000,
            'closing_cash_system' => 500000,
            'closing_cash_actual' => 490000,
            'cash_difference' => -10000,
            'petty_cash_total' => 50000,
            'status' => 'CLOSED',
            'notes' => 'Selisih Rp 10rb karena kembalian kurang pecahan',
        ]);

        PettyCashRecord::create([
            'cashier_shift_id' => $shift->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'category' => 'Operasional',
            'amount' => 50000,
            'description' => 'Beli plastik laundry',
        ]);

        $response = $this->actingAs($this->user)->get(route('shifts.show', $shift->id));

        $response->assertStatus(200);
        $response->assertSee("Audit Rekapitulasi Shift #{$shift->id}");
        $response->assertSee('Beli plastik laundry');
        $response->assertSee('Selisih Rp 10rb karena kembalian kurang pecahan');
    }

    public function test_closing_shift_triggers_financial_journal_sync(): void
    {
        $shift = CashierShift::create([
            'branch_id' => $this->branch->id,
            'cashier_id' => $this->user->id,
            'opened_at' => now()->subHours(4),
            'opening_cash' => 200000,
            'status' => 'OPEN',
        ]);

        PettyCashRecord::create([
            'cashier_shift_id' => $shift->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'category' => 'Konsumsi',
            'amount' => 20000,
            'description' => 'Beli makan siang kasir',
        ]);

        // Close shift with a deficit (-10.000)
        $response = $this->actingAs($this->user)->post(route('pos.shift.close'), [
            'closing_cash_actual' => 170000, // Expected = 200.000 - 20.000 = 180.000 -> Diff = -10.000
            'notes' => 'Defisit selisih 10.000',
        ]);

        $response->assertRedirect(route('pos.index'));
        $this->assertDatabaseHas('cashier_shifts', [
            'id' => $shift->id,
            'status' => 'CLOSED',
            'closing_cash_system' => 180000,
            'closing_cash_actual' => 170000,
            'cash_difference' => -10000,
        ]);

        // Verify financial journal was created for the shift closing
        $this->assertDatabaseHas('journals', [
            'source_type' => CashierShift::class,
            'source_id' => $shift->id,
            'branch_id' => $this->branch->id,
        ]);
    }
}
