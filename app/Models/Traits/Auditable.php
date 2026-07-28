<?php

namespace App\Models\Traits;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot the Auditable trait for a model.
     */
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            self::logAudit('created', $model, null, self::getAuditableAttributes($model));
        });

        static::updated(function ($model) {
            $oldValues = self::getAuditableAttributes($model->getOriginal());
            $newValues = self::getAuditableAttributes($model->getAttributes());

            // Only log if there are actual changes in auditable fields
            if ($oldValues !== $newValues) {
                self::logAudit('updated', $model, $oldValues, $newValues);
            }
        });

        static::deleted(function ($model) {
            self::logAudit('deleted', $model, self::getAuditableAttributes($model), null);
        });
    }

    /**
     * Get attributes that should be included in audit logs.
     * Override this method in models to customize auditable fields.
     *
     * @return array<string, mixed>
     */
    protected static function getAuditableAttributes(array $attributes): array
    {
        // Sensitive fields to exclude from audit logs
        $excludeFields = [
            'password',
            'remember_token',
            'token',
            'api_token',
            'secret',
            'access_token',
            'refresh_token',
        ];

        return collect($attributes)
            ->except($excludeFields)
            ->toArray();
    }

    /**
     * Log an audit entry using the AuditLogService.
     */
    protected static function logAudit(string $action, Model $model, ?array $oldValues = null, ?array $newValues = null): void
    {
        // Skip audit logging if no authenticated user (e.g., during seeder or console commands)
        if (! Auth::check()) {
            return;
        }

        // Skip audit logging for AuditLog model itself to prevent infinite loops
        if ($model instanceof \App\Models\AuditLog) {
            return;
        }

        app(AuditLogService::class)->log($action, $model, $oldValues, $newValues);
    }
}
