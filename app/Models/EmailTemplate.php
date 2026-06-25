<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'contact_reply',
        'lead_reply',
        'newsletter_welcome',
        'backup_success',
        'backup_failed',
        'maintenance_alert',
        'admin_alert',
        'password_reset',
        'custom',
    ];

    public const TYPE_CLASSES = [
        'contact_reply' => 'text-bg-primary',
        'lead_reply' => 'text-bg-success',
        'newsletter_welcome' => 'text-bg-info',
        'backup_success' => 'text-bg-success',
        'backup_failed' => 'text-bg-danger',
        'maintenance_alert' => 'text-bg-warning',
        'admin_alert' => 'text-bg-dark',
        'password_reset' => 'text-bg-secondary',
        'custom' => 'text-bg-light',
    ];

    protected $fillable = [
        'name',
        'slug',
        'subject',
        'type',
        'body',
        'available_variables',
        'status',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'available_variables' => 'array',
            'status' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public static function label(?string $value): string
    {
        return str($value ?: 'custom')->replace(['_', '-'], ' ')->title()->toString();
    }

    public function typeClass(): string
    {
        return self::TYPE_CLASSES[$this->type] ?? 'text-bg-secondary';
    }
}
