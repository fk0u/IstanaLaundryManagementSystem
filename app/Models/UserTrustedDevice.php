<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTrustedDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_token',
        'device_name',
        'ip_address',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the trusted device.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the trusted device token is active.
     */
    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }
}
