<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $slugSource = trim((string) $this->input('slug')) ?: (string) $this->input('title');

        $this->merge([
            'slug' => Str::slug($slugSource),
            'status' => $this->boolean('status'),
            'show_in_menu' => $this->boolean('show_in_menu'),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages', 'slug')->ignore($this->route('page')),
            ],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'content' => ['required', 'string'],
            'page_type' => ['required', Rule::in(['default', 'page', 'landing'])],
            'template' => ['required', 'string', 'max:100'],
            'status' => ['required', 'boolean'],
            'show_in_menu' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
