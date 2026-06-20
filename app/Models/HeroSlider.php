<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HeroSlider extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'subtitle',
        'button_text',
        'button_url',
        'image',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
