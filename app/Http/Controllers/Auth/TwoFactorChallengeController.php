<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserTrustedDevice;
use App\Services\AuditLogService;
use App\Services\Security\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    /**
     * Display the 2FA challenge view.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify the 2FA code or recovery code.
     */
    public function store(Request $request, TwoFactorService $twoFactorService): RedirectResponse
    {
        if (! $request->session()->has('login.id')) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
            'trust_device' => ['nullable', 'boolean'],
        ]);

        $user = User::findOrFail($request->session()->get('login.id'));

        if ($request->filled('code')) {
            if (! $user->two_factor_secret) {
                throw ValidationException::withMessages([
                    'code' => '2FA belum disiapkan dengan benar untuk akun ini.',
                ]);
            }

            $secret = decrypt($user->two_factor_secret);
            $isValid = $twoFactorService->verifyKey($secret, $request->input('code'));

            if (! $isValid) {
                throw ValidationException::withMessages([
                    'code' => 'Kode 2FA 6-digit yang Anda masukkan salah atau telah kadaluwarsa.',
                ]);
            }
        } elseif ($request->filled('recovery_code')) {
            $recoveryCode = trim($request->input('recovery_code'));
            $codes = json_decode($user->two_factor_recovery_codes ?? '[]', true) ?: [];

            $index = array_search($recoveryCode, $codes, true);

            if ($index === false) {
                throw ValidationException::withMessages([
                    'recovery_code' => 'Kode pemulihan yang Anda masukkan tidak valid.',
                ]);
            }

            unset($codes[$index]);
            $user->update([
                'two_factor_recovery_codes' => json_encode(array_values($codes)),
            ]);
        } else {
            throw ValidationException::withMessages([
                'code' => 'Silakan masukkan kode 2FA atau kode pemulihan.',
            ]);
        }

        // Authentication success
        $remember = $request->session()->get('login.remember', false);
        Auth::login($user, $remember);

        $request->session()->forget(['login.id', 'login.remember']);
        $request->session()->regenerate();

        $user->update([
            'login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ]);

        app(AuditLogService::class)->log('login_2fa', $user);

        $response = redirect()->intended(route('dashboard', absolute: false));

        if ($request->boolean('trust_device')) {
            $rawToken = Str::random(40);

            UserTrustedDevice::create([
                'user_id' => $user->id,
                'device_token' => hash('sha256', $rawToken),
                'device_name' => substr($request->userAgent() ?? 'Unknown Device', 0, 255),
                'ip_address' => $request->ip(),
                'expires_at' => now()->addDays(30),
            ]);

            // Cookie valid for 30 days (43200 minutes)
            $cookie = cookie('2fa_device_trust', $rawToken, 43200, null, null, false, true);
            $response->withCookie($cookie);
        }

        return $response;
    }
}
