<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadNote extends Model
{
    use SoftDeletes;

    public const TYPES = ['general', 'call', 'email', 'whatsapp', 'meeting', 'follow_up'];

    protected $fillable = ['lead_id', 'user_id', 'note', 'note_type', 'next_follow_up_date'];

    protected function casts(): array
    {
        return ['lead_id' => 'integer', 'user_id' => 'integer', 'next_follow_up_date' => 'datetime'];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
