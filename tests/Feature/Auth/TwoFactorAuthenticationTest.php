<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\UserTrustedDevice;
use App\Services\Security\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected TwoFactorService $twoFactorService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->twoFactorService = new TwoFactorService();
    }

    private function generateValidCode(string $secret): string
    {
        $reflector = new \ReflectionMethod(TwoFactorService::class, 'calculateCode');
        $reflector->setAccessible(true);
        return $reflector->invoke($this->twoFactorService, $secret, (int) floor(time() / 30));
    }

    public function test_user_without_2fa_can_login_directly(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_user_with_2fa_redirects_to_2fa_challenge(): void
    {
        $secret = $this->twoFactorService->generateSecretKey();

        $user = User::factory()->create([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('two-factor.login'));
        $this->assertEquals($user->id, session('login.id'));

        $challengePage = $this->get('/two-factor-challenge');
        $challengePage->assertStatus(200);
        $challengePage->assertSee('Autentikasi 2FA');
    }

    public function test_user_fails_2fa_with_invalid_code(): void
    {
        $secret = $this->twoFactorService->generateSecretKey();

        $user = User::factory()->create([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->withSession(['login.id' => $user->id]);

        $response = $this->post('/two-factor-challenge', [
            'code' => '999999',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('code');
    }

    public function test_user_completes_2fa_challenge_with_valid_totp_code(): void
    {
        $secret = $this->twoFactorService->generateSecretKey();
        $validCode = $this->generateValidCode($secret);

        $user = User::factory()->create([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->withSession(['login.id' => $user->id]);

        $response = $this->post('/two-factor-challenge', [
            'code' => $validCode,
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_user_can_trust_device_for_30_days(): void
    {
        $secret = $this->twoFactorService->generateSecretKey();
        $validCode = $this->generateValidCode($secret);

        $user = User::factory()->create([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->withSession(['login.id' => $user->id]);

        $response = $this->post('/two-factor-challenge', [
            'code' => $validCode,
            'trust_device' => '1',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertCookie('2fa_device_trust');

        $this->assertDatabaseHas('user_trusted_devices', [
            'user_id' => $user->id,
        ]);

        $rawCookie = $response->getCookie('2fa_device_trust')->getValue();
        $this->assertNotEmpty($rawCookie);

        // Logout
        $this->post('/logout');
        $this->assertGuest();

        // Login again using trusted device cookie -> bypass 2FA!
        $loginResponse = $this->withCookie('2fa_device_trust', $rawCookie)->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $loginResponse->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_user_can_authenticate_using_recovery_code(): void
    {
        $secret = $this->twoFactorService->generateSecretKey();
        $recoveryCodes = ['code-one-12345', 'code-two-67890'];

        $user = User::factory()->create([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->withSession(['login.id' => $user->id]);

        $response = $this->post('/two-factor-challenge', [
            'recovery_code' => 'code-one-12345',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));

        // Ensure used recovery code was consumed
        $updatedCodes = json_decode($user->fresh()->two_factor_recovery_codes, true);
        $this->assertCount(1, $updatedCodes);
        $this->assertNotContains('code-one-12345', $updatedCodes);
    }

    public function test_api_2fa_login_requires_code_and_supports_device_trust(): void
    {
        $secret = $this->twoFactorService->generateSecretKey();
        $validCode = $this->generateValidCode($secret);

        $user = User::factory()->create([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ]);

        // 1. Without 2FA code -> returns 422 2fa_required
        $req1 = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $req1->assertStatus(422)->assertJson(['status' => '2fa_required']);

        // 2. With valid 2FA code & trust_device=true -> returns token & device_trust_token
        $req2 = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
            'two_factor_code' => $validCode,
            'trust_device' => true,
        ]);
        $req2->assertStatus(200)->assertJsonStructure(['token', 'user', 'device_trust_token']);
        $deviceTrustToken = $req2->json('device_trust_token');

        // 3. Subsequent API login using X-Device-Trust-Token header -> bypasses 2FA code!
        $req3 = $this->withHeader('X-Device-Trust-Token', $deviceTrustToken)->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $req3->assertStatus(200)->assertJsonStructure(['token', 'user']);
    }
}
