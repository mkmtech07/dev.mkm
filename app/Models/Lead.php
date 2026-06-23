<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    public const SOURCES = ['contact_form', 'quote_request', 'service_enquiry', 'phone_call', 'whatsapp', 'manual', 'other'];
    public const STATUSES = ['new', 'contacted', 'follow_up', 'interested', 'converted', 'not_interested', 'spam', 'closed'];
    public const PRIORITIES = ['low', 'medium', 'high', 'urgent'];
    public const CONTACT_METHODS = ['phone', 'email', 'whatsapp', 'any'];

    protected $fillable = [
        'name', 'email', 'phone', 'whatsapp', 'company_name', 'subject', 'message', 'service_id',
        'source', 'status', 'priority', 'budget', 'preferred_contact_method', 'follow_up_date',
        'assigned_to', 'ip_address', 'user_agent', 'status_active',
    ];

    protected function casts(): array
    {
        return [
            'service_id' => 'integer',
            'assigned_to' => 'integer',
            'follow_up_date' => 'datetime',
            'status_active' => 'boolean',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class)->latest();
    }

    public function latestNote(): HasOne
    {
        return $this->hasOne(LeadNote::class)->latestOfMany();
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
