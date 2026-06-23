<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoSetting extends Model
{
    protected $fillable = [
        'sitemap_status', 'robots_status', 'schema_status', 'default_robots_index',
        'default_robots_follow', 'sitemap_cache_minutes', 'robots_content',
        'google_analytics_id', 'google_tag_manager_id', 'google_search_console_code',
        'facebook_pixel_id', 'custom_head_code', 'custom_body_code', 'status',
    ];

    protected function casts(): array
    {
        return [
            'sitemap_status' => 'boolean',
            'robots_status' => 'boolean',
            'schema_status' => 'boolean',
            'default_robots_index' => 'boolean',
            'default_robots_follow' => 'boolean',
            'sitemap_cache_minutes' => 'integer',
            'status' => 'boolean',
        ];
    }

    public static function defaults(): array
    {
        return [
            'sitemap_status' => true,
            'robots_status' => true,
            'schema_status' => true,
            'default_robots_index' => true,
            'default_robots_follow' => true,
            'sitemap_cache_minutes' => 60,
            'status' => true,
        ];
    }
}
