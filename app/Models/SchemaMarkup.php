<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchemaMarkup extends Model
{
    use SoftDeletes;

    public const TYPES = ['Organization', 'LocalBusiness', 'Website', 'Breadcrumb', 'FAQ', 'Article', 'Product', 'Custom'];

    protected $fillable = ['name', 'type', 'schema_json', 'status', 'sort_order'];

    protected function casts(): array
    {
        return ['status' => 'boolean', 'sort_order' => 'integer'];
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
