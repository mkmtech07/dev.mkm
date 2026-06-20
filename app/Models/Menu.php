<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = ['name', 'location', 'status'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function rootItems(): HasMany
    {
        return $this->items()->whereNull('parent_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
