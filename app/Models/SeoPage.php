<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeoPage extends Model
{
    use BelongsToTenant, SoftDeletes;

    public const TYPES = ['static', 'page', 'blog', 'blog_category', 'service', 'gallery', 'custom'];

    public const STATIC_KEYS = ['home', 'about', 'services', 'gallery', 'blog', 'contact', 'faq'];

    public const CHANGE_FREQUENCIES = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];

    protected $fillable = [
        'tenant_id',
        'page_key', 'page_type', 'related_id', 'route_path', 'title', 'meta_title',
        'meta_description', 'meta_keywords', 'canonical_url', 'og_title', 'og_description',
        'og_image', 'twitter_title', 'twitter_description', 'twitter_image', 'robots_index',
        'robots_follow', 'priority', 'change_frequency', 'status',
    ];

    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'related_id' => 'integer',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'priority' => 'decimal:1',
            'status' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
