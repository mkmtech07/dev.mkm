<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use Illuminate\Http\JsonResponse;

class WebsiteSettingController extends Controller
{
    public function show(): JsonResponse
    {
        $settings = WebsiteSetting::query()->where('status', true)->first();

        if (! $settings) {
            return response()->json(null);
        }

        return response()->json([
            'site_name' => $settings->site_name,
            'site_tagline' => $settings->site_tagline,
            'logo' => $settings->logo ? asset($settings->logo) : null,
            'white_logo' => $settings->white_logo ? asset($settings->white_logo) : null,
            'favicon' => $settings->favicon ? asset($settings->favicon) : null,
            'og_image' => $settings->og_image ? asset($settings->og_image) : null,
            'primary_color' => $settings->primary_color,
            'secondary_color' => $settings->secondary_color,
            'phone' => $settings->phone,
            'email' => $settings->email,
            'whatsapp' => $settings->whatsapp,
            'address' => $settings->address,
            'google_map_embed' => $settings->google_map_embed,
            'facebook_url' => $settings->facebook_url,
            'instagram_url' => $settings->instagram_url,
            'linkedin_url' => $settings->linkedin_url,
            'youtube_url' => $settings->youtube_url,
            'twitter_url' => $settings->twitter_url,
            'meta_title' => $settings->meta_title,
            'meta_description' => $settings->meta_description,
            'meta_keywords' => $settings->meta_keywords,
            'custom_css' => $settings->custom_css,
            'custom_js' => $settings->custom_js,
        ]);
    }
}
