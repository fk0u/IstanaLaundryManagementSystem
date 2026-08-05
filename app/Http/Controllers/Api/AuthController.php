<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserTrustedDevice;
use App\Services\Security\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Authenticate a user and issue a Sanctum API token.
     */
    public function login(Request $request, TwoFactorService $twoFactorService)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'two_factor_code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
            'trust_device' => ['nullable', 'boolean'],
            'device_trust_token' => ['nullable', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda tidak aktif. Hubungi administrator.'],
            ]);
        }

        $issuedDeviceTrustToken = null;

        // Check 2FA requirement
        if ($user->two_factor_confirmed_at) {
            $deviceToken = $request->header('X-Device-Trust-Token') ?? $request->input('device_trust_token');
            $isDeviceTrusted = false;

            if ($deviceToken) {
                $isDeviceTrusted = UserTrustedDevice::where('user_id', $user->id)
                    ->where('device_token', hash('sha256', $deviceToken))
                    ->where('expires_at', '>', now())
                    ->exists();
            }

            if (! $isDeviceTrusted) {
                if ($request->filled('two_factor_code')) {
                    $secret = decrypt($user->two_factor_secret);
                    if (! $twoFactorService->verifyKey($secret, $request->input('two_factor_code'))) {
                        throw ValidationException::withMessages([
                            'two_factor_code' => ['Kode 2FA tidak valid.'],
                        ]);
                    }
                } elseif ($request->filled('recovery_code')) {
                    $recoveryCode = trim($request->input('recovery_code'));
                    $codes = json_decode($user->two_factor_recovery_codes ?? '[]', true) ?: [];
                    $index = array_search($recoveryCode, $codes, true);

                    if ($index === false) {
                        throw ValidationException::withMessages([
                            'recovery_code' => ['Kode pemulihan tidak valid.'],
                        ]);
                    }

                    unset($codes[$index]);
                    $user->update([
                        'two_factor_recovery_codes' => json_encode(array_values($codes)),
                    ]);
                } else {
                    return response()->json([
                        'status' => '2fa_required',
                        'message' => 'Autentikasi 2FA diperlukan. Kirimkan dua_factor_code atau recovery_code.',
                    ], 422);
                }

                if ($request->boolean('trust_device')) {
                    $issuedDeviceTrustToken = Str::random(40);
                    UserTrustedDevice::create([
                        'user_id' => $user->id,
                        'device_token' => hash('sha256', $issuedDeviceTrustToken),
                        'device_name' => substr($credentials['device_name'] ?? $request->userAgent() ?? 'API Client', 0, 255),
                        'ip_address' => $request->ip(),
                        'expires_at' => now()->addDays(30),
                    ]);
                }
            }
        }

        $token = $user->createToken($credentials['device_name'] ?? 'api-token')->plainTextToken;

        $response = [
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'branch_id' => $user->branch_id,
                'roles' => $user->getRoleNames(),
            ],
        ];

        if ($issuedDeviceTrustToken) {
            $response['device_trust_token'] = $issuedDeviceTrustToken;
        }

        return response()->json($response);
    }

    /**
     * Revoke the token used for the current request.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil logout.']);
    }

    /**
     * Return the currently authenticated user.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'branch_id' => $user->branch_id,
            'roles' => $user->getRoleNames(),
        ]);
    }
}
