<?php

namespace App\Models;

use App\Models\Traits\BranchScoped;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'branch_id',
    'name',
    'code',
    'type',
    'value',
    'min_transaction',
    'service_id',
    'applicable_tier',
    'target_customer_type',
    'max_member_age_days',
    'usage_limit',
    'usage_count',
    'per_customer_limit',
    'start_date',
    'end_date',
    'is_active',
])]
class Promotion extends Model
{
    use BranchScoped, HasFactory;

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_transaction' => 'decimal:2',
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
            'per_customer_limit' => 'integer',
            'max_member_age_days' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check promo eligibility for a given customer and transaction subtotal.
     * Returns ['eligible' => bool, 'reason' => string|null].
     */
    public function isEligibleForCustomer(?Customer $customer, float $transactionAmount = 0): array
    {
        if (! $this->is_active) {
            return ['eligible' => false, 'reason' => "Kupon promo '{$this->code}' sedang tidak aktif."];
        }

        $today = now()->startOfDay();
        if ($this->start_date && $this->start_date->startOfDay() > $today) {
            return ['eligible' => false, 'reason' => "Kupon promo '{$this->code}' belum berlaku (mulai {$this->start_date->format('d/m/Y')})."];
        }

        if ($this->end_date && $this->end_date->endOfDay() < now()) {
            return ['eligible' => false, 'reason' => "Kupon promo '{$this->code}' sudah berakhir pada {$this->end_date->format('d/m/Y')}."];
        }

        if ($transactionAmount < (float) $this->min_transaction) {
            return ['eligible' => false, 'reason' => "Minimal transaksi Rp ".number_format($this->min_transaction, 0, ',', '.')." belum terpenuhi."];
        }

        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return ['eligible' => false, 'reason' => "Kuota penggunaan kupon '{$this->code}' sudah habis."];
        }

        // Target Customer Validation
        if ($this->target_customer_type === 'new_member_only') {
            if (! $customer) {
                return ['eligible' => false, 'reason' => "Kupon '{$this->code}' khusus untuk Member terdaftar (bukan Pelanggan Walk-In)."];
            }

            $maxDays = $this->max_member_age_days ?? 60;
            $memberAgeDays = (int) $customer->created_at->diffInDays(now());
            if ($memberAgeDays > $maxDays) {
                return ['eligible' => false, 'reason' => "Kupon '{$this->code}' khusus untuk Member Baru (maksimal pendaftaran {$maxDays} hari). Umur akun member ini sudah {$memberAgeDays} hari."];
            }
        } elseif ($this->target_customer_type === 'existing_member_only') {
            if (! $customer) {
                return ['eligible' => false, 'reason' => "Kupon '{$this->code}' khusus untuk Member terdaftar."];
            }
        }

        // Per Customer Usage Limit Check
        $limitPerCustomer = $this->per_customer_limit ?? ($this->target_customer_type === 'new_member_only' ? 1 : null);
        if ($limitPerCustomer !== null && $customer) {
            $usedCount = Order::where('customer_id', $customer->id)
                ->where('promo_id', $this->id)
                ->count();

            if ($usedCount >= $limitPerCustomer) {
                return ['eligible' => false, 'reason' => "Member '{$customer->name}' sudah pernah menggunakan kupon ini sebanyak {$usedCount}x (Batas pemakaian {$limitPerCustomer}x per member)."];
            }
        }

        return ['eligible' => true, 'reason' => null];
    }

    /**
     * Override bootBranchScoped to allow global promotions (branch_id is null)
     */
    protected static function bootBranchScoped(): void
    {
        static::addGlobalScope('branch_scope', function (Builder $builder) {
            $branchId = session('scoped_branch_id');

            if ($branchId !== null) {
                $builder->where(function (Builder $query) use ($branchId) {
                    $query->where(static::getBranchColumn(), $branchId)
                        ->orWhereNull(static::getBranchColumn());
                });
            }
        });

        static::creating(function ($model) {
            // Nullable branch_id is allowed for global promotions,
            // so we only auto-set if not explicitly set and not intended to be global.
            if (! $model->{static::getBranchColumn()}) {
                $branchId = session('scoped_branch_id') ?? auth()->user()?->branch_id;
                if ($branchId) {
                    $model->{static::getBranchColumn()} = $branchId;
                }
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
