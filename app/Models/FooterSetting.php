<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterSetting extends Model
{
    protected $fillable = [
        'footer_logo',
        'footer_description',
        'phone',
        'email',
        'whatsapp',
        'address',
        'copyright_text',
        'newsletter_status',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'newsletter_status' => 'boolean',
            'status' => 'boolean',
        ];
    }
}
