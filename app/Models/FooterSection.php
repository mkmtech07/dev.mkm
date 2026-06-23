<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FooterSection extends Model
{
    use SoftDeletes;

    public const TYPES = ['about', 'links', 'contact', 'social', 'newsletter', 'custom'];

    protected $fillable = ['title', 'type', 'content', 'status', 'sort_order'];

    protected function casts(): array
    {
        return ['status' => 'boolean', 'sort_order' => 'integer'];
    }

    public function links(): HasMany
    {
        return $this->hasMany(FooterLink::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
