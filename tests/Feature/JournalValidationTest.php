<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\BranchSeeder::class);
        $this->seed(\Database\Seeders\ChartOfAccountSeeder::class);
        $this->seed(\Database\Seeders\UserSeeder::class);
    }

    public function test_creating_journal_with_debit_and_credit_on_same_line_is_rejected()
    {
        $user = User::where('email', 'finance.wjk@istanalaundry.com')->first();
        $accounts = ChartOfAccount::where('is_active', true)->take(2)->get();
        $account1 = $accounts[0];
        $account2 = $accounts[1];

        $response = $this->actingAs($user)->post(route('finance.journals.store'), [
            'date' => now()->toDateString(),
            'description' => 'Test Invalid Line Both Debit Credit',
            'lines' => [
                [
                    'account_id' => $account1->id,
                    'debit' => 1000000,
                    'credit' => 1000000, // Invalid: both debit & credit on line 1
                ],
                [
                    'account_id' => $account2->id,
                    'debit' => 0,
                    'credit' => 1000000,
                ],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('journals', [
            'description' => 'Test Invalid Line Both Debit Credit',
        ]);
    }

    public function test_valid_double_entry_journal_is_created_successfully()
    {
        $user = User::where('email', 'finance.wjk@istanalaundry.com')->first();
        $accounts = ChartOfAccount::where('is_active', true)->take(2)->get();
        $account1 = $accounts[0];
        $account2 = $accounts[1];

        $response = $this->actingAs($user)->post(route('finance.journals.store'), [
            'date' => now()->toDateString(),
            'description' => 'Transfer Kas Mandiri ke BCA',
            'lines' => [
                [
                    'account_id' => $account1->id,
                    'debit' => 0,
                    'credit' => 1000000000,
                    'description' => 'Kredit Kas BCA',
                ],
                [
                    'account_id' => $account2->id,
                    'debit' => 1000000000,
                    'credit' => 0,
                    'description' => 'Debit Kas Mandiri',
                ],
            ],
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('journals', [
            'description' => 'Transfer Kas Mandiri ke BCA',
        ]);
    }
}
