<?php

namespace App\Http\Requests;

use App\Models\HomepageSection;
use App\Support\MediaPicker;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class HomepageSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $settings = $this->input('settings');

        if (is_string($settings)) {
            $settings = trim($settings);

            if ($settings === '') {
                $settings = null;
            } else {
                $decoded = json_decode($settings, true);
                $settings = json_last_error() === JSON_ERROR_NONE ? $decoded : $settings;
            }
        }

        $this->merge([
            'status' => $this->has('status') ? $this->boolean('status') : false,
            'sort_order' => $this->input('sort_order', 0),
            'settings' => $settings,
        ]);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'section_key' => ['nullable', 'string', 'max:255', 'alpha_dash:ascii'],
            'type' => ['required', Rule::in(HomepageSection::TYPES)],
            'content' => ['nullable', 'string'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_url' => [
                'nullable',
                'string',
                'max:500',
                'regex:~^(?:/(?!/)|https?://|mailto:|tel:|#)~i',
            ],
            'image' => [
                'nullable',
                File::image(allowSvg: true)->max(2048),
                'mimes:jpg,jpeg,png,webp,svg',
            ],
            'background_image' => [
                'nullable',
                File::image()->max(4096),
                'mimes:jpg,jpeg,png,webp',
            ],
            ...MediaPicker::validationRules(['image', 'background_image']),
            'background_color' => ['nullable', 'string', 'max:20', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/'],
            'text_color' => ['nullable', 'string', 'max:20', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/'],
            'status' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'settings' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'button_url.regex' => 'Enter an internal path, web URL, email link, phone link, or anchor.',
            'settings.array' => 'The settings must contain valid JSON.',
            'section_key.alpha_dash' => 'The section key may contain only letters, numbers, dashes, and underscores.',
        ];
    }
}
