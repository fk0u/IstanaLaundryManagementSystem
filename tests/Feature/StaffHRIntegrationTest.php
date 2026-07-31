<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffHRIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        $this->withoutExceptionHandling();

        $this->adminUser = User::first();
        if (! $this->adminUser) {
            $branch = Branch::first() ?? Branch::create(['name' => 'Utama', 'code' => 'HQ', 'address' => 'HQ', 'phone' => '081234567890']);
            $this->adminUser = User::create([
                'name' => 'Admin Test',
                'email' => 'admin.test@istanalaundry.com',
                'password' => bcrypt('password'),
                'branch_id' => $branch->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_user_seeder_creates_matching_employee_profiles()
    {
        $users = User::all();
        $this->assertGreaterThan(0, $users->count());

        foreach ($users as $user) {
            $this->assertNotNull($user->employee, "User {$user->email} must have a linked Employee profile.");
        }
    }

    public function test_store_employee_with_optional_user_account()
    {
        $branch = Branch::first();
        $role = Role::firstOrCreate(['name' => 'Cashier']);

        $response = $this->actingAs($this->adminUser)->post(route('hr.employees.store'), [
            'nik' => 'NIK-TEST-999',
            'name' => 'Staff Tester Barcode',
            'position' => 'Kasir Utama',
            'base_salary' => 3500000,
            'branch_id' => $branch->id,
            'phone' => '081299998888',
            'create_account' => '1',
            'email' => 'tester.barcode@istanalaundry.com',
            'password' => 'password123',
            'role' => $role->name,
        ]);

        $response->assertRedirect(route('hr.index'));
        $this->assertDatabaseHas('employees', ['nik' => 'NIK-TEST-999', 'name' => 'Staff Tester Barcode']);
        $this->assertDatabaseHas('users', ['email' => 'tester.barcode@istanalaundry.com']);

        $createdEmp = Employee::withoutGlobalScopes()->where('nik', 'NIK-TEST-999')->first();
        $createdUser = User::where('email', 'tester.barcode@istanalaundry.com')->first();

        $this->assertEquals($createdUser->id, $createdEmp->user_id);
    }

    public function test_create_account_for_unlinked_employee()
    {
        $branch = Branch::first();
        $employee = Employee::create([
            'nik' => 'NIK-UNLINKED-001',
            'name' => 'Unlinked Staff',
            'position' => 'Operator',
            'base_salary' => 3000000,
            'branch_id' => $branch->id,
            'is_active' => true,
            'joined_at' => now()->toDateString(),
        ]);

        $role = Role::firstOrCreate(['name' => 'Workshop_Staff']);

        $response = $this->actingAs($this->adminUser)->post(route('hr.employees.create-account', $employee->id), [
            'email' => 'unlinked.staff@istanalaundry.com',
            'password' => 'password123',
            'role' => $role->name,
        ]);

        $response->assertSessionHasNoErrors();
        $employee->refresh();
        $this->assertNotNull($employee->user_id);
        $this->assertEquals('unlinked.staff@istanalaundry.com', $employee->user->email);
    }

    public function test_store_attendance_and_calculate_payroll()
    {
        $branch = Branch::first();
        $employee = Employee::create([
            'nik' => 'NIK-ATT-001',
            'name' => 'Attendance Staff',
            'position' => 'Kasir Utama',
            'base_salary' => 3000000,
            'branch_id' => $branch->id,
            'is_active' => true,
            'joined_at' => now()->toDateString(),
        ]);

        // Record attendance log
        $response = $this->actingAs($this->adminUser)->post(route('hr.attendances.store'), [
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
            'status' => 'hadir',
            'check_in' => '08:00',
            'check_out' => '17:00',
            'notes' => 'Presensi Tepat Waktu',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'status' => 'hadir',
        ]);
    }
}
