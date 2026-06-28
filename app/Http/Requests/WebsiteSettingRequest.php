<?php

namespace App\Http\Requests;

use App\Support\MediaPicker;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class WebsiteSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'site_tagline' => $this->input('site_tagline', $this->input('tagline')),
            'whatsapp' => $this->input('whatsapp', $this->input('whatsapp_number')),
            'status' => $this->has('status') ? $this->boolean('status') : true,
        ]);
    }

    /** @return array<string, ValidationRule|Closure|array<mixed>|string> */
    public function rules(): array
    {
        $image = ['nullable', File::image(allowSvg: true)->max(2048), 'mimes:jpg,jpeg,png,webp,svg'];

        return [
            'site_name' => [
                'required',
                'string',
                'max:255',
            ],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'logo' => $image,
            'white_logo' => $image,
            'favicon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,ico,webp,svg', 'max:1024'],
            'og_image' => ['nullable', File::image()->max(2048), 'mimes:jpg,jpeg,png,webp'],
            ...MediaPicker::validationRules(['logo', 'white_logo', 'favicon', 'og_image']),
            'primary_color' => ['nullable', 'string', 'max:20', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/'],
            'secondary_color' => ['nullable', 'string', 'max:20', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:5000'],
            'google_map_embed' => ['nullable', 'string', 'max:10000', $this->safeGoogleMapEmbed(...)],
            'facebook_url' => ['nullable', 'url:http,https', 'max:2048'],
            'instagram_url' => ['nullable', 'url:http,https', 'max:2048'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:2048'],
            'youtube_url' => ['nullable', 'url:http,https', 'max:2048'],
            'twitter_url' => ['nullable', 'url:http,https', 'max:2048'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'custom_css' => ['nullable', 'string', 'max:200000', $this->safeCss(...)],
            'custom_js' => ['nullable', 'string', 'max:200000', $this->safeJavaScript(...)],
            'status' => ['nullable', 'boolean'],
        ];
    }

    private function safeGoogleMapEmbed(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $safeIframe = preg_match(
            '~^\s*<iframe\b(?=[^>]*\bsrc=["\']https://(?:www\.)?google\.com/maps/embed\?[^"\']+["\'])[^>]*>\s*</iframe>\s*$~i',
            (string) $value
        );
        $unsafeAttribute = preg_match('~\b(?:on\w+|srcdoc)\s*=|javascript:~i', (string) $value);

        if (! $safeIframe || $unsafeAttribute) {
            $fail('The Google Map embed must be a safe Google Maps iframe.');
        }
    }

    private function safeCss(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value && preg_match('~</?style|javascript:|expression\s*\(|@import|-moz-binding|behavior\s*:~i', (string) $value)) {
            $fail('The custom CSS contains an unsafe construct.');
        }
    }

    private function safeJavaScript(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value && preg_match('~</?script~i', (string) $value)) {
            $fail('Enter JavaScript without script tags.');
        }
    }
}
