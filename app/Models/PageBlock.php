<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PageBlock extends Model
{
    use BelongsToTenant, SoftDeletes;

    public const TYPES = [
        'hero',
        'text',
        'image',
        'text_image',
        'services',
        'gallery',
        'testimonials',
        'faq',
        'blog',
        'cta',
        'features',
        'pricing',
        'contact_form',
        'newsletter',
        'custom_html',
    ];

    protected $fillable = [
        'tenant_id',
        'page_id',
        'title',
        'subtitle',
        'block_key',
        'type',
        'content',
        'image',
        'background_image',
        'button_text',
        'button_url',
        'secondary_button_text',
        'secondary_button_url',
        'background_color',
        'text_color',
        'settings',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'page_id' => 'integer',
            'settings' => 'array',
            'status' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function typeLabel(): string
    {
        return Str::headline(str_replace('_', ' ', $this->type));
    }

    public function publicContent(): ?string
    {
        if ($this->content === null) {
            return null;
        }

        if ($this->type !== 'custom_html') {
            return $this->content;
        }

        return self::sanitizeCustomHtml($this->content);
    }

    public static function sanitizeCustomHtml(string $html): string
    {
        $html = preg_replace('/<\s*(script|iframe|object|embed|style)\b[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $html) ?? '';
        $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace('/\s+(href|src)\s*=\s*([\'"])\s*javascript:[^\'"]*\2/i', '', $html) ?? $html;

        return $html;
    }
}
