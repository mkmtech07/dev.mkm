<?php

namespace App\Http\Requests;

use App\Models\SeoPage;
use App\Support\MediaPicker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SeoPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $routePath = trim((string) $this->input('route_path'));
        if ($routePath !== '' && ! str_starts_with($routePath, '/')) {
            $routePath = '/'.$routePath;
        }
        if (strlen($routePath) > 1) {
            $routePath = rtrim($routePath, '/');
        }

        $this->merge([
            'page_key' => trim((string) $this->input('page_key')) ?: null,
            'route_path' => $routePath ?: null,
            'related_id' => $this->filled('related_id') ? $this->integer('related_id') : null,
            'robots_index' => $this->boolean('robots_index'),
            'robots_follow' => $this->boolean('robots_follow'),
            'status' => $this->boolean('status'),
        ]);
    }

    public function rules(): array
    {
        $relatedTable = match ($this->input('page_type')) {
            'page' => 'pages',
            'blog' => 'blogs',
            'blog_category' => 'blog_categories',
            'service' => 'services',
            'gallery' => 'galleries',
            default => null,
        };

        return [
            'page_key' => ['nullable', 'string', 'max:255'],
            'page_type' => ['required', Rule::in(SeoPage::TYPES)],
            'related_id' => ['nullable', 'integer', 'min:1', ...($relatedTable ? [Rule::exists($relatedTable, 'id')] : [])],
            'route_path' => ['nullable', 'string', 'max:500', 'regex:/^\/(?!\/)/'],
            'title' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'url:http,https', 'max:500'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ...MediaPicker::validationRules(['og_image', 'twitter_image']),
            'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string', 'max:500'],
            'twitter_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'robots_index' => ['required', 'boolean'],
            'robots_follow' => ['required', 'boolean'],
            'priority' => ['nullable', 'numeric', 'min:0.1', 'max:1.0'],
            'change_frequency' => ['required', Rule::in(SeoPage::CHANGE_FREQUENCIES)],
            'status' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $path = strtolower((string) $this->input('route_path'));
            foreach (['/admin', '/api', '/login', '/register', '/dashboard', '/profile'] as $privatePrefix) {
                if ($path === $privatePrefix || str_starts_with($path, $privatePrefix.'/')) {
                    $validator->errors()->add('route_path', 'Private and administrative routes cannot be managed as public SEO pages.');
                    break;
                }
            }
        }];
    }
}
