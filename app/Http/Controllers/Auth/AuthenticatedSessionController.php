<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $ipThrottleKey = 'login_ip:' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($ipThrottleKey, 10)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($ipThrottleKey);
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan masuk dari IP Anda. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        $email = $request->input('email');
        $user = \App\Models\User::where('email', $email)->first();

        // 2. Check DB lockout (15 minutes)
        if ($user && $user->locked_until && $user->locked_until->isFuture()) {
            $minutes = $user->locked_until->diffInMinutes(now()) + 1;
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => "Akun Anda terkunci karena terlalu banyak kesalahan sandi. Silakan coba lagi dalam {$minutes} menit.",
            ]);
        }

        // 3. Authenticate
        try {
            $request->authenticate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Failed login attempt
            \Illuminate\Support\Facades\RateLimiter::hit($ipThrottleKey, 60);

            if ($user) {
                $user->increment('login_attempts');
                if ($user->login_attempts >= 5) {
                    $user->update([
                        'locked_until' => now()->addMinutes(15),
                    ]);

                    // Audit log: Lockout
                    app(\App\Services\AuditLogService::class)->log('lockout', $user);
                } else {
                    // Audit log: Failed login
                    app(\App\Services\AuditLogService::class)->log('failed_login', $user);
                }
            }

            throw $e;
        }

        // Success login
        \Illuminate\Support\Facades\RateLimiter::clear($ipThrottleKey);

        $user = Auth::user();
        $user->update([
            'login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ]);

        // Audit log: Success login
        app(\App\Services\AuditLogService::class)->log('login', $user);

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
            app(\App\Services\AuditLogService::class)->log('logout', $user);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
