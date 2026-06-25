<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminNotification extends Model
{
    use SoftDeletes;

    public const TYPES = ['info', 'success', 'warning', 'danger', 'system'];

    public const TYPE_CLASSES = [
        'info' => 'text-bg-info',
        'success' => 'text-bg-success',
        'warning' => 'text-bg-warning',
        'danger' => 'text-bg-danger',
        'system' => 'text-bg-dark',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'module',
        'action_url',
        'data',
        'is_read',
        'read_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->whereNull('user_id');

            if ($user) {
                $query->orWhere('user_id', $user->getKey());
            }
        });
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public static function label(?string $value): string
    {
        return str($value ?: 'system')->replace(['_', '-'], ' ')->title()->toString();
    }

    public function typeClass(): string
    {
        return self::TYPE_CLASSES[$this->type] ?? 'text-bg-secondary';
    }

    public function targetUrl(): string
    {
        return $this->action_url ?: route('admin.notifications.show', $this, false);
    }
}
