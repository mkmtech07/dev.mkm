<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
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
}
