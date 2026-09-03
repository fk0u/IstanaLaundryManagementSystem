<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Models\UserTrustedDevice;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Rate limiter check (IP limit 10 attempts/min)
        $ipThrottleKey = 'login_ip:'.$request->ip();
        if (RateLimiter::tooManyAttempts($ipThrottleKey, 10)) {
            $seconds = RateLimiter::availableIn($ipThrottleKey);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan masuk dari IP Anda. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        // 2. Check DB lockout (15 minutes)
        if ($user && $user->locked_until && $user->locked_until->isFuture()) {
            $minutes = max(1, (int) ceil(now()->diffInSeconds($user->locked_until) / 60));
            throw ValidationException::withMessages([
                'email' => "Akun Anda terkunci karena terlalu banyak kesalahan sandi. Silakan coba lagi dalam {$minutes} menit.",
            ]);
        }

        // 3. Authenticate credentials without logging in immediately
        $credentials = $request->only('email', 'password');
        if (! Auth::validate($credentials)) {
            RateLimiter::hit($ipThrottleKey, 60);

            if ($user) {
                $user->increment('login_attempts');
                if ($user->login_attempts >= 5) {
                    $user->update([
                        'locked_until' => now()->addMinutes(15),
                    ]);

                    app(AuditLogService::class)->log('lockout', $user);
                } else {
                    app(AuditLogService::class)->log('failed_login', $user);
                }
            }

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Check active user
        if ($user && ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ]);
        }

        RateLimiter::clear($ipThrottleKey);

        // 4. Check 2FA requirement
        if ($user && $user->two_factor_confirmed_at) {
            $rawToken = $request->cookie('2fa_device_trust');
            $isDeviceTrusted = false;

            if ($rawToken) {
                $isDeviceTrusted = UserTrustedDevice::where('user_id', $user->id)
                    ->where('device_token', hash('sha256', $rawToken))
                    ->where('expires_at', '>', now())
                    ->exists();
            }

            if (! $isDeviceTrusted) {
                // Store pending login in session and redirect to 2FA challenge
                $request->session()->put([
                    'login.id' => $user->id,
                    'login.remember' => $request->boolean('remember'),
                ]);

                return redirect()->route('two-factor.login');
            }
        }

        // Success login without 2FA or with trusted device
        Auth::login($user, $request->boolean('remember'));

        $user->update([
            'login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ]);

        app(AuditLogService::class)->log('login', $user);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            app(AuditLogService::class)->log('logout', $user);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
