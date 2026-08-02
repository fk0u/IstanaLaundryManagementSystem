<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\GoodsReceivedNote;
use App\Models\OperationalExpense;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierPaymentAndOpExTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->branch = Branch::create(['name' => 'Cabang Test', 'code' => 'TST', 'address' => 'Jl. Test', 'phone' => '0812345']);
        $this->user = User::factory()->create([
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->user->assignRole('Developer');

        // Create requisite COAs
        ChartOfAccount::firstOrCreate(['code' => '1-1101'], ['name' => 'Kas Kecil', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3]);
        ChartOfAccount::firstOrCreate(['code' => '1-1102'], ['name' => 'Bank', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3]);
        ChartOfAccount::firstOrCreate(['code' => '2-1101'], ['name' => 'Hutang Usaha', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3]);
        ChartOfAccount::firstOrCreate(['code' => '5-2101'], ['name' => 'Beban Listrik', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 3]);
    }

    public function test_supplier_payment_index_page_loads_successfully(): void
    {
        $supplier = Supplier::create(['name' => 'PT Deterjen Jaya']);
        $po = PurchaseOrder::create([
            'branch_id' => $this->branch->id,
            'supplier_id' => $supplier->id,
            'po_number' => 'PO-TST-001',
            'status' => 'confirmed',
            'subtotal' => 1000000,
            'tax_amount' => 0,
            'total' => 1000000,
            'order_date' => now(),
        ]);

        GoodsReceivedNote::create([
            'po_id' => $po->id,
            'grn_number' => 'GRN-TST-001',
            'status' => 'confirmed',
            'received_by' => $this->user->id,
            'received_date' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['scoped_branch_id' => $this->branch->id])
            ->get('/finance/supplier-payments');

        $response->assertStatus(200);
        $response->assertSee('Pelunasan');
        $response->assertSee('PT Deterjen Jaya');
    }

    public function test_can_create_supplier_payment_and_post_journal(): void
    {
        $supplier = Supplier::create(['name' => 'PT Deterjen Jaya']);

        $response = $this->actingAs($this->user)
            ->withSession(['scoped_branch_id' => $this->branch->id])
            ->post('/finance/supplier-payments', [
                'supplier_id' => $supplier->id,
                'payment_date' => now()->format('Y-m-d'),
                'amount' => 5000000,
                'payment_method' => 'transfer',
                'reference_number' => 'TRF-BCA-12345',
                'notes' => 'Pelunasan invoice Juli',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('supplier_payments', [
            'supplier_id' => $supplier->id,
            'amount' => 5000000,
            'payment_method' => 'transfer',
        ]);
    }

    public function test_operational_expense_index_page_loads_successfully(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['scoped_branch_id' => $this->branch->id])
            ->get('/finance/operational-expenses');

        $response->assertStatus(200);
        $response->assertSee('Beban Operasional');
    }

    public function test_can_create_operational_expense_and_post_journal(): void
    {
        $account = ChartOfAccount::where('code', '5-2101')->first();

        $response = $this->actingAs($this->user)
            ->withSession(['scoped_branch_id' => $this->branch->id])
            ->post('/finance/operational-expenses', [
                'expense_date' => now()->format('Y-m-d'),
                'account_id' => $account->id,
                'amount' => 250000,
                'description' => 'Bayar Listrik Bulan Ini',
                'payment_method' => 'cash',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('operational_expenses', [
            'account_id' => $account->id,
            'amount' => 250000,
            'description' => 'Bayar Listrik Bulan Ini',
        ]);
    }
}
