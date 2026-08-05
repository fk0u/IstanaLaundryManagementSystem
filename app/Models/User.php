<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
// TODO: Consider enabling MustVerifyEmail for production security.
// Currently disabled to simplify onboarding. To enable:
// 1. Uncomment the above use statement
// 2. Add "implements MustVerifyEmail" to class declaration
// 3. Configure email settings in .env (MAIL_MAILER, MAIL_HOST, etc.)
// 4. Ensure email verification routes are properly configured
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'email',
    'password',
    'branch_id',
    'is_active',
    'last_login_at',
    'login_attempts',
    'locked_until',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'two_factor_confirmed_at',
    'avatar_path',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the branch that the user belongs to.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the orders created by this user (as cashier).
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'cashier_id');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function trustedDevices(): HasMany
    {
        return $this->hasMany(UserTrustedDevice::class);
    }

    public function isOwner(): bool
    {
        return $this->hasAnyRole(['Owner', 'Developer']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasAnyRole(['Super_Admin', 'Developer']);
    }
}
