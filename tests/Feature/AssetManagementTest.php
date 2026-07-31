<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\DepreciationSchedule;
use App\Models\FixedAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->user = User::first();
        if (! $this->user->hasRole('Super_Admin')) {
            $this->user->assignRole('Super_Admin');
        }
    }

    public function test_assets_index_page_loads_and_generates_schedules()
    {
        $response = $this->actingAs($this->user)->get('/assets');

        $response->assertStatus(200);
        $response->assertSee('Visualisasi Nilai Perolehan');
        $response->assertSee('Proyeksi Beban Penyusutan');

        // Verify schedules were generated
        $this->assertGreaterThan(0, DepreciationSchedule::count());

        // Verify monthly depreciation forecast has non-zero values
        $response->assertViewHas('monthlyDepreciationForecast', function ($forecast) {
            $nonZero = array_filter($forecast, fn ($item) => $item['amount'] > 0);
            return count($nonZero) > 0;
        });
    }
}
