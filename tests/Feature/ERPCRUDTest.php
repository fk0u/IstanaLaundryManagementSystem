<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\FixedAsset;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ERPCRUDTest extends TestCase
{
    use RefreshDatabase;

    protected $developerUser;
    protected $branch;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a branch
        $this->branch = Branch::create([
            'code' => 'SMD01',
            'name' => 'Samarinda Central',
            'address' => 'Jl. Juanda No. 10',
            'phone' => '08111222333',
            'email' => 'smd01@istanalaundry.com',
            'is_active' => true,
        ]);

        // Create developer role and user
        Role::create(['name' => 'Developer']);
        $this->developerUser = User::create([
            'name' => 'Dev Antigravity',
            'email' => 'developer@istanalaundry.com',
            'password' => bcrypt('password'),
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->developerUser->assignRole('Developer');
    }

    public function test_customer_crud_operations()
    {
        $this->actingAs($this->developerUser);

        // 1. Create (Store)
        $response = $this->post('/customers', [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'budi@gmail.com',
            'address' => 'Jl. Slamet Riyadi',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['name' => 'Budi Santoso']);

        $customer = Customer::where('name', 'Budi Santoso')->first();

        // 2. Update
        $response = $this->put('/customers/' . $customer->id, [
            'name' => 'Budi Santoso Updated',
            'phone' => '081234567890',
            'email' => 'budi.new@gmail.com',
            'address' => 'Jl. Slamet Riyadi Baru',
            'loyalty_tier' => 'Silver',
            'loyalty_points' => 1200,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['name' => 'Budi Santoso Updated', 'loyalty_tier' => 'Silver']);

        // 3. Delete
        $response = $this->delete('/customers/' . $customer->id);
        $response->assertRedirect();
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_promotions_crud_operations()
    {
        $this->actingAs($this->developerUser);

        // 1. Create (Store)
        $response = $this->post('/promotions', [
            'name' => 'Diskon Merdeka',
            'code' => 'MERDEKA80',
            'type' => 'percent',
            'value' => 17,
            'min_transaction' => 45000,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('promotions', ['code' => 'MERDEKA80']);

        $promo = Promotion::where('code', 'MERDEKA80')->first();

        // 2. Delete
        $response = $this->delete('/promotions/' . $promo->id);
        $response->assertRedirect();
        $this->assertDatabaseMissing('promotions', ['id' => $promo->id]);
    }

    public function test_inventory_crud_operations()
    {
        $this->actingAs($this->developerUser);

        // 1. Create (Store)
        $response = $this->post('/inventory', [
            'name' => 'Deterjen Liquid Lavender',
            'sku' => 'DET-LAV-01',
            'category' => 'deterjen',
            'unit' => 'Liter',
            'min_stock' => 10,
            'current_stock' => 150,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('inventory_items', ['sku' => 'DET-LAV-01']);

        $item = \App\Models\InventoryItem::where('sku', 'DET-LAV-01')->first();

        // 2. Stock Adjustment (Adjust)
        $response = $this->put('/inventory/' . $item->id . '/adjust', [
            'current_stock' => 125,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'current_stock' => 125]);
    }

    public function test_hr_employee_crud_operations()
    {
        $this->actingAs($this->developerUser);

        // 1. Create (Store)
        $response = $this->post('/hr', [
            'name' => 'Siti Aminah',
            'nik' => 'NIK-SMD-005',
            'position' => 'Kasir',
            'base_salary' => 3200000,
            'joined_at' => '2026-01-01',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('employees', ['nik' => 'NIK-SMD-005']);

        $employee = Employee::where('nik', 'NIK-SMD-005')->first();

        // 2. Update (Salary / Status / Position)
        $response = $this->put('/hr/' . $employee->id, [
            'name' => 'Siti Aminah',
            'position' => 'Senior Kasir',
            'base_salary' => 3800000,
            'is_active' => 1,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'position' => 'Senior Kasir', 'base_salary' => 3800000]);
    }

    public function test_fixed_asset_crud_operations()
    {
        $this->actingAs($this->developerUser);

        // Create standard COA to attach asset to
        $coa = ChartOfAccount::create([
            'code' => '12100',
            'name' => 'Peralatan Laundry',
            'type' => 'asset',
            'normal_balance' => 'debit',
            'level' => 1,
            'is_active' => true,
            'is_system' => true,
        ]);

        // 1. Create (Store)
        $response = $this->post('/assets', [
            'name' => 'Mesin Kering Electrolux X10',
            'asset_code' => 'AST-DRY-01',
            'category' => 'mesin',
            'acquisition_cost' => 12000000,
            'salvage_value' => 1000000,
            'useful_life_months' => 48,
            'depreciation_method' => 'straight_line',
            'acquisition_date' => '2026-06-01',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('fixed_assets', ['asset_code' => 'AST-DRY-01']);

        $asset = FixedAsset::where('asset_code', 'AST-DRY-01')->first();

        // 2. Delete (Disposal)
        $response = $this->delete('/assets/' . $asset->id);
        $response->assertRedirect();
        $this->assertDatabaseMissing('fixed_assets', ['id' => $asset->id]);
    }

    public function test_chart_of_accounts_crud_operations()
    {
        $this->actingAs($this->developerUser);

        // 1. Create (Store)
        $response = $this->post('/finance', [
            'code' => '11140',
            'name' => 'Kas Kecil Seberang',
            'type' => 'asset',
            'normal_balance' => 'debit',
            'parent_id' => null,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('chart_of_accounts', ['code' => '11140']);

        $coa = ChartOfAccount::where('code', '11140')->first();

        // 2. Delete
        $response = $this->delete('/finance/' . $coa->id);
        $response->assertRedirect();
        $this->assertDatabaseMissing('chart_of_accounts', ['id' => $coa->id]);
    }
}
