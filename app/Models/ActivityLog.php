<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const STATUSES = ['success', 'failed', 'warning'];

    public const ACTIONS = ['create', 'update', 'delete', 'status', 'backup', 'settings', 'login', 'logout', 'failed_login'];

    protected $fillable = [
        'user_id', 'user_name', 'user_email', 'action', 'module', 'model_type', 'model_id',
        'description', 'old_values', 'new_values', 'ip_address', 'user_agent', 'url',
        'method', 'status',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'model_id' => 'integer',
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public static function label(?string $value): string
    {
        return $value ? str($value)->replace('_', ' ')->title()->toString() : 'Unknown';
    }
}
