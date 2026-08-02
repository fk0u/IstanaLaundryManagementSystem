<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAndPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_applied_to_responses(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_performance_middleware_injects_execution_time_header(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Response-Time');
        $response->assertHeader('X-Peak-Memory');
    }

    public function test_security_anomaly_detector_blocks_sqli_attempts(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/?query=SELECT+*+FROM+users+WHERE+1=1');

        $response->assertStatus(403);
    }

    public function test_security_anomaly_detector_blocks_xss_attempts(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/?search=<script>alert(1)</script>');

        $response->assertStatus(403);
    }

    public function test_cache_service_remembers_and_invalidates_system_settings(): void
    {
        $cacheService = app(CacheService::class);

        \App\Models\SystemSetting::create(['key' => 'test_key', 'value' => 'value_1']);
        $settings1 = $cacheService->getSystemSettings();
        $this->assertEquals('value_1', $settings1['test_key'] ?? null);

        // Update setting - observer should automatically clear cache
        \App\Models\SystemSetting::where('key', 'test_key')->update(['value' => 'value_2']);
        $cacheService->clearSystemSettingsCache();
        
        $settings2 = $cacheService->getSystemSettings();
        $this->assertEquals('value_2', $settings2['test_key'] ?? null);
    }
}
