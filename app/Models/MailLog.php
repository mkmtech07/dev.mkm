<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MailLog extends Model
{
    use SoftDeletes;

    public const STATUSES = ['pending', 'sent', 'failed'];

    public const STATUS_CLASSES = [
        'pending' => 'text-bg-warning',
        'sent' => 'text-bg-success',
        'failed' => 'text-bg-danger',
    ];

    protected $fillable = [
        'recipient',
        'subject',
        'template_slug',
        'mail_type',
        'status',
        'error_message',
        'sent_at',
        'data',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return in_array($status, self::STATUSES, true)
            ? $query->where('status', $status)
            : $query;
    }

    public static function label(?string $value): string
    {
        return str($value ?: 'unknown')->replace(['_', '-'], ' ')->title()->toString();
    }

    public function statusClass(): string
    {
        return self::STATUS_CLASSES[$this->status] ?? 'text-bg-secondary';
    }
}
