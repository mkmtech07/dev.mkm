<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Services\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $model = $builder->getModel();

            if (! self::hasTenantColumn($model)) {
                return;
            }

            $tenantId = app(TenantManager::class)->currentId();
            if ($tenantId) {
                $builder->where($model->qualifyColumn('tenant_id'), $tenantId);
            }
        });

        static::creating(function (Model $model): void {
            if (! self::hasTenantColumn($model) || $model->getAttribute('tenant_id')) {
                return;
            }

            $tenantId = app(TenantManager::class)->currentId();
            if ($tenantId) {
                $model->setAttribute('tenant_id', $tenantId);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeForTenant(Builder $query, Tenant|int|null $tenant): Builder
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return $query->withoutGlobalScope('tenant')->where($query->getModel()->qualifyColumn('tenant_id'), $tenantId);
    }

    public function scopeWithoutTenant(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }

    private static function hasTenantColumn(Model $model): bool
    {
        static $cache = [];

        $table = $model->getTable();

        if (! Schema::hasTable($table)) {
            return false;
        }

        return $cache[$table] ??= Schema::hasColumn($table, 'tenant_id');
    }
}
