<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\FooterSection;
use App\Models\FooterSetting;
use App\Models\FooterSocialLink;
use Illuminate\Http\JsonResponse;

class FooterController extends Controller
{
    public function show(): JsonResponse
    {
        $settings = FooterSetting::query()->where('status', true)->latest('updated_at')->first();

        if (! $settings) {
            return response()->json([
                'settings' => null,
                'sections' => [],
                'social_links' => [],
            ]);
        }

        $sections = FooterSection::query()
            ->active()
            ->with(['links' => fn ($query) => $query->active()->ordered()])
            ->ordered()
            ->get()
            ->map(fn (FooterSection $section) => [
                'title' => $section->title,
                'type' => $section->type,
                'content' => $section->content,
                'links' => $section->links
                    ->filter(fn ($link) => $this->safeLink($link->url))
                    ->map(fn ($link) => [
                        'title' => $link->title,
                        'url' => $link->url,
                        'icon' => $link->icon,
                        'target' => $link->target,
                    ])
                    ->values(),
            ]);

        $socialLinks = FooterSocialLink::query()
            ->active()
            ->ordered()
            ->get()
            ->filter(fn (FooterSocialLink $link) => $this->safeSocialUrl($link->url))
            ->map(fn (FooterSocialLink $link) => [
                'platform' => $link->platform,
                'url' => $link->url,
                'icon' => $link->icon,
                'target' => $link->target,
            ])
            ->values();

        return response()->json([
            'settings' => [
                'footer_logo' => $settings->footer_logo ? asset($settings->footer_logo) : null,
                'footer_description' => $settings->footer_description,
                'phone' => $settings->phone,
                'email' => $settings->email,
                'whatsapp' => $settings->whatsapp,
                'address' => $settings->address,
                'copyright_text' => $settings->copyright_text,
                'newsletter_status' => $settings->newsletter_status,
            ],
            'sections' => $sections,
            'social_links' => $socialLinks,
        ]);
    }

    private function safeLink(string $url): bool
    {
        return (bool) preg_match('~^(?:/(?!/)|https?://|mailto:|tel:|#)~i', $url);
    }

    private function safeSocialUrl(string $url): bool
    {
        return (bool) preg_match('~^https?://~i', $url);
    }
}
