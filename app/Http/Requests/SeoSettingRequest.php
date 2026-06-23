<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SeoSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sitemap_status' => $this->boolean('sitemap_status'),
            'robots_status' => $this->boolean('robots_status'),
            'schema_status' => $this->boolean('schema_status'),
            'default_robots_index' => $this->boolean('default_robots_index'),
            'default_robots_follow' => $this->boolean('default_robots_follow'),
            'status' => $this->boolean('status'),
        ]);
    }

    public function rules(): array
    {
        return [
            'sitemap_status' => ['required', 'boolean'],
            'robots_status' => ['required', 'boolean'],
            'schema_status' => ['required', 'boolean'],
            'default_robots_index' => ['required', 'boolean'],
            'default_robots_follow' => ['required', 'boolean'],
            'sitemap_cache_minutes' => ['required', 'integer', 'min:1', 'max:10080'],
            'robots_content' => ['nullable', 'string'],
            'google_analytics_id' => ['nullable', 'string', 'max:100'],
            'google_tag_manager_id' => ['nullable', 'string', 'max:100'],
            'google_search_console_code' => ['nullable', 'string', 'max:500'],
            'facebook_pixel_id' => ['nullable', 'string', 'max:100'],
            'custom_head_code' => ['nullable', 'string'],
            'custom_body_code' => ['nullable', 'string'],
            'status' => ['required', 'boolean'],
        ];
    }
}
