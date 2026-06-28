<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    use BelongsToTenant, SoftDeletes;

    public const SOURCES = ['footer', 'popup', 'contact_page', 'blog', 'manual', 'api', 'other'];
    public const STATUSES = ['subscribed', 'unsubscribed', 'pending', 'blocked'];

    protected $fillable = [
        'tenant_id',
        'name', 'email', 'phone', 'source', 'status', 'ip_address', 'user_agent',
        'subscribed_at', 'unsubscribed_at', 'unsubscribe_token', 'notes', 'status_active',
    ];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'status_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (NewsletterSubscriber $subscriber): void {
            $subscriber->unsubscribe_token ??= Str::random(64);
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status_active', true);
    }

    public static function label(string $value): string
    {
        return str($value)->replace('_', ' ')->title()->toString();
    }
}
