<?php

namespace Tests\Feature;

use App\Models\AccountingPeriod;
use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\Employee;
use App\Models\Journal;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\User;
use App\Services\Finance\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollFinancialSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeUser;
    protected Branch $branch;
    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\ChartOfAccountSeeder::class);

        $this->branch = Branch::create([
            'name' => 'Cabang Test Sync',
            'code' => 'SYC',
            'address' => 'Jl. Finance Sync No. 1',
            'phone' => '081299990000',
        ]);

        AccountingPeriod::create([
            'branch_id' => $this->branch->id,
            'year' => now()->year,
            'month' => now()->month,
            'status' => 'open',
        ]);

        $this->financeUser = User::factory()->create([
            'branch_id' => $this->branch->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $this->financeUser->assignRole('Finance');

        $this->employee = Employee::create([
            'branch_id' => $this->branch->id,
            'nik' => 'NIK-SYC-001',
            'name' => 'Staf Keuangan Test',
            'position' => 'Akuntan',
            'base_salary' => 3000000,
            'is_active' => true,
            'joined_at' => now()->toDateString(),
        ]);
    }

    public function test_finalizing_payroll_posts_balanced_financial_journal()
    {
        $payroll = Payroll::create([
            'branch_id' => $this->branch->id,
            'month' => now()->month,
            'year' => now()->year,
            'status' => 'draft',
            'created_by' => $this->financeUser->id,
        ]);

        $item = PayrollItem::create([
            'payroll_id' => $payroll->id,
            'employee_id' => $this->employee->id,
            'base_salary' => 3000000,
            'allowance' => 500000,
            'deduction' => 100000,
            'net_salary' => 3400000,
            'total_earnings' => 3500000,
            'total_deductions' => 100000,
            'attendance_days' => 20,
            'work_days' => 20,
        ]);

        // Finalize payroll via HTTP POST
        $response = $this->actingAs($this->financeUser)
            ->withSession(['scoped_branch_id' => $this->branch->id])
            ->post(route('hr.payrolls.finalize', $payroll->id));

        $response->assertRedirect();

        $payroll->refresh();
        $this->assertEquals('final', $payroll->status);

        // Assert financial journal was created
        $journal = Journal::withoutGlobalScopes()
            ->where('source_type', Payroll::class)
            ->where('source_id', $payroll->id)
            ->first();

        $this->assertNotNull($journal, 'Financial journal entry must be automatically posted upon payroll finalization');
        $this->assertEquals($this->branch->id, $journal->branch_id);

        $lines = $journal->journalLines;
        $totalDebit = $lines->sum('debit');
        $totalCredit = $lines->sum('credit');

        $this->assertEquals($totalDebit, $totalCredit, 'Financial journal entry must be balanced');
        $this->assertEquals(3500000, $totalDebit); // Gross Earnings (3,000,000 + 500,000)
    }

    public function test_sync_journal_is_idempotent()
    {
        $payroll = Payroll::create([
            'branch_id' => $this->branch->id,
            'month' => now()->month,
            'year' => now()->year,
            'status' => 'final',
            'created_by' => $this->financeUser->id,
        ]);

        PayrollItem::create([
            'payroll_id' => $payroll->id,
            'employee_id' => $this->employee->id,
            'base_salary' => 2500000,
            'allowance' => 0,
            'deduction' => 0,
            'net_salary' => 2500000,
            'total_earnings' => 2500000,
            'total_deductions' => 0,
            'attendance_days' => 20,
            'work_days' => 20,
        ]);

        $journalService = app(JournalService::class);

        $journal1 = $journalService->postPayrollJournal($payroll);
        $journal2 = $journalService->postPayrollJournal($payroll);

        $this->assertEquals($journal1->id, $journal2->id, 'Repeated sync must return existing journal without creating duplicates');

        $journalCount = Journal::withoutGlobalScopes()
            ->where('source_type', Payroll::class)
            ->where('source_id', $payroll->id)
            ->count();

        $this->assertEquals(1, $journalCount);
    }
}
