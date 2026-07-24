---
name: branch-scoping
description: Multi-tenant data isolation per branch using Eloquent global scopes and middleware.
glob: "app/Models/**"
---

# Branch Scoping (Multi-Tenancy)

## When to Use
Gunakan pola ini ketika entitas database harus diisolasi berdasarkan cabang (`branch_id`) untuk peran pengguna non-super (seperti `Cashier`, `Branch_Admin`, `Workshop_Admin`, `Workshop_Staff`, `CS_Marketing`, `Finance`).

## Pattern

### 1. Trait Eloquent (`app/Models/Traits/BranchScoped.php`)
Menyediakan filter otomatis pada saat query serta menetapkan `branch_id` secara otomatis ketika data dibuat.
```php
namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BranchScoped
{
    protected static function bootBranchScoped(): void
    {
        static::addGlobalScope('branch_scope', function (Builder $builder) {
            $branchId = session('scoped_branch_id');
            if ($branchId !== null) {
                $builder->where(static::getBranchColumn(), $branchId);
            }
        });

        static::creating(function ($model) {
            if (!$model->{static::getBranchColumn()}) {
                $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id;
                if ($branchId) {
                    $model->{static::getBranchColumn()} = $branchId;
                }
            }
        });
    }

    public static function getBranchColumn(): string
    {
        return 'branch_id';
    }

    public static function withoutBranchScope(): Builder
    {
        return static::withoutGlobalScope('branch_scope');
    }
}
```

### 2. Middleware Request (`app/Http/Middleware/BranchScopeMiddleware.php`)
Memeriksa peran pengguna dan menyematkan ID cabang ke dalam session.
```php
class BranchScopeMiddleware
{
    protected array $superRoles = ['Developer', 'Owner', 'Super_Admin'];

    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $isSuperUser = false;
        foreach ($this->superRoles as $role) {
            if ($user->hasRole($role)) {
                $isSuperUser = true;
                break;
            }
        }

        if (!$isSuperUser) {
            if (!$user->branch_id) {
                abort(403, 'User tidak memiliki branch assignment');
            }
            session(['scoped_branch_id' => $user->branch_id]);
        } else {
            session()->forget('scoped_branch_id');
        }

        return $next($request);
    }
}
```

## Gotchas & Anti-Patterns
- **SQL Error pada Tabel Tanpa Kolom `branch_id`**: Jangan gunakan trait `BranchScoped` pada tabel yang tidak memiliki kolom `branch_id` di database (misal: `suppliers`, `loyalty_point_logs`). Ini akan memicu error `Column not found`.
- **Promosi Global**: Khusus untuk model `Promotion`, override `bootBranchScoped()` untuk memperbolehkan query bernilai null (`orWhereNull('branch_id')`) agar promosi yang berlaku untuk seluruh cabang tetap dapat diakses.
