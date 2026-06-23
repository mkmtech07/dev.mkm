<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomepageSection extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'hero',
        'about',
        'services',
        'gallery',
        'testimonials',
        'blog',
        'faq',
        'cta',
        'custom',
    ];

    protected $fillable = [
        'title',
        'subtitle',
        'section_key',
        'type',
        'content',
        'button_text',
        'button_url',
        'image',
        'background_image',
        'background_color',
        'text_color',
        'status',
        'sort_order',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'sort_order' => 'integer',
            'settings' => 'array',
        ];
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
