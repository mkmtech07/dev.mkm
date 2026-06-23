<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\HomepageSection;
use Illuminate\Http\JsonResponse;

class HomepageSectionController extends Controller
{
    public function index(): JsonResponse
    {
        $sections = HomepageSection::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn (HomepageSection $section) => [
                'title' => $section->title,
                'subtitle' => $section->subtitle,
                'section_key' => $section->section_key,
                'type' => $section->type,
                'content' => $section->content ? strip_tags($section->content) : null,
                'button_text' => $section->button_text,
                'button_url' => $section->button_url,
                'image' => $section->image ? asset($section->image) : null,
                'background_image' => $section->background_image ? asset($section->background_image) : null,
                'background_color' => $section->background_color,
                'text_color' => $section->text_color,
                'settings' => (object) ($section->settings ?? []),
            ]);

        return response()->json(['sections' => $sections]);
    }
}
