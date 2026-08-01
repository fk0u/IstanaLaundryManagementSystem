<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PayrollLockingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $branch;
    protected $payroll;
    protected $payrollItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();

        $this->branch = Branch::create([
            'code' => 'SMD01',
            'name' => 'Samarinda Central',
            'address' => 'Jl. Juanda No. 10',
            'phone' => '08111222333',
            'email' => 'smd01@istanalaundry.com',
            'is_active' => true,
        ]);

        Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);

        $rand = rand(1000, 9999);
        $this->user = User::create([
            'name' => 'Owner User Test',
            'email' => 'owner_test_'.$rand.'@istanalaundry.com',
            'password' => bcrypt('password'),
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->user->assignRole('Owner');

        $employee = Employee::create([
            'branch_id' => $this->branch->id,
            'nik' => 'NIK-LOCK-'.$rand,
            'name' => 'Staf Gaji Test',
            'position' => 'Kasir Utama',
            'base_salary' => 3000000,
            'joined_at' => now()->subYear(),
            'is_active' => true,
        ]);

        $this->payroll = Payroll::create([
            'branch_id' => $this->branch->id,
            'month' => 7,
            'year' => 2026,
            'status' => 'final',
            'created_by' => $this->user->id,
            'processed_at' => now(),
        ]);

        $this->payrollItem = PayrollItem::create([
            'payroll_id' => $this->payroll->id,
            'employee_id' => $employee->id,
            'base_salary' => 3000000,
            'work_days' => 26,
            'attendance_days' => 26,
            'allowance' => 500000,
            'deduction' => 0,
            'total_earnings' => 3500000,
            'total_deductions' => 0,
            'net_salary' => 3500000,
        ]);
    }

    public function test_editing_locked_payroll_item_via_url_query_redirects_with_error(): void
    {
        $response = $this->actingAs($this->user, 'web')
            ->get(route('hr.index', ['edit_item' => $this->payrollItem->id]));

        $response->assertRedirect(route('hr.payrolls.show', $this->payroll->id));
        $response->assertSessionHas('error', 'Payroll ini sudah difinalkan dan dikunci! Data tidak dapat diubah.');
    }

    public function test_updating_locked_payroll_item_is_blocked_and_redirects(): void
    {
        $response = $this->actingAs($this->user, 'web')
            ->post(route('hr.payroll-item.update', $this->payrollItem->id), [
                '_method' => 'PUT',
                'allowance' => 9999999,
            ]);

        $response->assertRedirect(route('hr.payrolls.show', $this->payroll->id));
        $response->assertSessionHas('error', 'Payroll sudah berstatus FINAL dan dikunci dari perubahan!');

        $this->payrollItem->refresh();
        $this->assertEquals(500000, $this->payrollItem->allowance);
    }

    public function test_deleting_locked_payroll_is_blocked_and_redirects(): void
    {
        $response = $this->actingAs($this->user, 'web')
            ->post(route('hr.payroll.destroy', $this->payroll->id), [
                '_method' => 'DELETE',
            ]);

        $response->assertRedirect(route('hr.payrolls.show', $this->payroll->id));
        $response->assertSessionHas('error', 'Payroll yang sudah berstatus FINAL & DIKUNCI tidak dapat dihapus!');

        $this->assertDatabaseHas('payrolls', ['id' => $this->payroll->id]);
    }

    public function test_custom_404_page_renders_properly(): void
    {
        $response = $this->get('/non-existent-page-url-123456');

        $response->assertStatus(404);
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('ISTANA LAUNDRY');
    }
}
