<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'site_tagline',
        'logo',
        'white_logo',
        'favicon',
        'og_image',
        'primary_color',
        'secondary_color',
        'phone',
        'email',
        'whatsapp',
        'address',
        'google_map_embed',
        'facebook_url',
        'instagram_url',
        'linkedin_url',
        'youtube_url',
        'twitter_url',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'custom_css',
        'custom_js',
        'status',
    ];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function getTaglineAttribute(): ?string
    {
        return $this->site_tagline;
    }

    public function getWhatsappNumberAttribute(): ?string
    {
        return $this->whatsapp;
    }
}
