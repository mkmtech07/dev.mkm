<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'tagline',
        'logo',
        'favicon',
        'phone',
        'email',
        'address',
        'whatsapp_number',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'meta_title',
        'meta_description',
    ];
}
