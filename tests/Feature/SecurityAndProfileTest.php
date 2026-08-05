<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Services\Security\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAndProfileTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;
    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->branch = Branch::create([
            'name' => 'Samarinda Utama',
            'code' => 'SMD',
            'address' => 'Jl. Pahlawan No. 1',
            'phone' => '08115550001',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'branch_id' => $this->branch->id,
            'name' => 'Secured User Admin',
        ]);
        $this->user->assignRole('Developer');

        $this->token = $this->user->createToken('sec_token')->plainTextToken;
    }

    public function test_api_returns_security_headers()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson(route('api.v1.profile.show'));

        $response->assertStatus(200)
            ->assertHeader('Strict-Transport-Security')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_user_can_enable_confirm_and_disable_totp_2fa()
    {
        // 1. Enable 2FA
        $enableResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson(route('api.v1.profile.2fa.enable'));

        $enableResponse->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['secret', 'otpauth_url']]);

        $secret = $enableResponse->json('data.secret');
        $this->assertNotEmpty($secret);

        // Calculate valid TOTP 6-digit code
        $twoFactorService = new TwoFactorService();
        $reflector = new \ReflectionMethod(TwoFactorService::class, 'calculateCode');
        $reflector->setAccessible(true);
        $validCode = $reflector->invoke($twoFactorService, $secret, (int) floor(time() / 30));

        // 2. Confirm 2FA
        $confirmResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson(route('api.v1.profile.2fa.confirm'), [
                'code' => $validCode,
            ]);

        $confirmResponse->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['recovery_codes']]);

        $this->assertNotNull($this->user->fresh()->two_factor_confirmed_at);

        // 3. Disable 2FA
        $disableResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson(route('api.v1.profile.2fa.disable'), [
                'current_password' => 'password',
            ]);

        $disableResponse->assertStatus(200);
        $this->assertNull($this->user->fresh()->two_factor_confirmed_at);
    }

    public function test_user_can_upload_avatar_compressed_to_webp_under_200kb()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('my_avatar.png', 800, 800);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson(route('api.v1.profile.avatar'), [
                'avatar' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $avatarPath = $response->json('data.avatar_path');
        $this->assertNotEmpty($avatarPath);
        $this->assertStringEndsWith('.webp', $avatarPath);

        Storage::disk('public')->assertExists($avatarPath);

        $size = Storage::disk('public')->size($avatarPath);
        $this->assertLessThanOrEqual(204800, $size); // Strict <= 200KB check!
    }
}
