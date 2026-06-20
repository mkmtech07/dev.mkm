<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_name',
        'company',
        'designation',
        'review',
        'rating',
        'image',
        'status',
        'featured',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'status' => 'boolean',
            'featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
