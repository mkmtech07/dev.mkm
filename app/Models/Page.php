<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'title',
        'slug',
        'meta_title',
        'meta_description',
        'featured_image',
        'content',
        'page_type',
        'template',
        'status',
        'show_in_menu',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'show_in_menu' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class);
    }

    public function activeBlocks(): HasMany
    {
        return $this->blocks()->active()->ordered();
    }
}
