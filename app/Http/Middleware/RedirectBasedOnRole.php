<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectBasedOnRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Redirect logic based on primary role
        if ($user->hasRole(['Developer', 'Owner', 'Super_Admin'])) {
            return redirect()->intended('/dashboard');
        } elseif ($user->hasRole('Branch_Admin')) {
            return redirect()->intended('/branches');
        } elseif ($user->hasRole('Cashier')) {
            return redirect()->intended('/pos');
        } elseif ($user->hasRole(['Workshop_Admin', 'Workshop_Staff'])) {
            return redirect()->intended('/production');
        } elseif ($user->hasRole('Finance')) {
            return redirect()->intended('/finance');
        } elseif ($user->hasRole('CS_Marketing')) {
            return redirect()->intended('/customers');
        }

        return redirect()->intended('/dashboard');
    }
}
