<?php

namespace Tests\Feature;

use App\Models\AccountingPeriod;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClosingChecklistTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_access_closing_checklist_page(): void
    {
        Role::firstOrCreate(['name' => 'Owner']);

        $branch = Branch::create([
            'name' => 'Cabang Utama',
            'code' => 'CBG01',
            'address' => 'Jl. Utama',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Owner Finance',
            'email' => 'finance@istana.com',
            'password' => bcrypt('password'),
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
        $user->assignRole('Owner');

        AccountingPeriod::create([
            'branch_id' => $branch->id,
            'year' => now()->year,
            'month' => now()->month,
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)->get(route('finance.closing-checklist'));

        $response->assertStatus(200);
        $response->assertSee('Closing Checklist Periode Akuntansi');
        $response->assertSee('1 Periode Terbuka');
    }
}
