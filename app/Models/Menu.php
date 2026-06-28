<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use BelongsToTenant, SoftDeletes;

    public const LOCATIONS = ['header', 'footer', 'sidebar'];

    protected $fillable = ['tenant_id', 'name', 'location', 'status'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function menuItems(): HasMany
    {
        return $this->items();
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
