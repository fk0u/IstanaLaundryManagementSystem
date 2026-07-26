<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchScopeMiddleware
{
    /**
     * Super-level roles that have access to all branches
     */
    protected array $superRoles = ['Developer', 'Owner', 'Super_Admin'];

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user has super-level role
        $isSuperUser = false;
        foreach ($this->superRoles as $role) {
            if ($user->hasRole($role)) {
                $isSuperUser = true;
                break;
            }
        }

        if (! $isSuperUser) {
            // Branch-level user: set branch_id in session
            if (! $user->branch_id) {
                abort(403, 'User tidak memiliki branch assignment');
            }

            session(['scoped_branch_id' => $user->branch_id]);
        } else {
            // Super-level user: no branch restriction by default, but keep manual session scoping if set
        }

        return $next($request);
    }
}
