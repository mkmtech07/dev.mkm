<?php

namespace App\Models;

use App\Services\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_SUSPENDED,
    ];

    protected $fillable = [
        'name',
        'slug',
        'subdomain',
        'custom_domain',
        'status',
        'is_demo',
        'demo_expires_at',
        'client_name',
        'client_email',
        'client_phone',
        'allowed_modules',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_demo' => 'boolean',
            'demo_expires_at' => 'datetime',
            'allowed_modules' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Tenant $tenant): void {
            $tenant->slug = Str::slug($tenant->slug ?: $tenant->name);
            $tenant->subdomain = $tenant->subdomain ? Str::slug($tenant->subdomain) : null;
            $tenant->custom_domain = $tenant->custom_domain ? strtolower(trim($tenant->custom_domain)) : null;
        });
    }

    public function setting(): HasOne
    {
        return $this->hasOne(TenantSetting::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeDemo(Builder $query): Builder
    {
        return $query->where('is_demo', true);
    }

    public function isDefault(): bool
    {
        return $this->slug === 'default';
    }

    public function isPubliclyAvailable(): bool
    {
        return app(TenantManager::class)->isPubliclyAvailable($this);
    }

    public function publicUrl(?string $path = null): string
    {
        return app(TenantManager::class)->publicUrl($this, $path);
    }

    public function statusLabel(): string
    {
        return str($this->status)->replace('_', ' ')->title()->toString();
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'text-bg-success',
            self::STATUS_SUSPENDED => 'text-bg-danger',
            default => 'text-bg-secondary',
        };
    }
}
