<?php

namespace App\Http\Requests;

use App\Models\PageBlock;
use App\Support\MediaPicker;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class PageBlockRequest extends FormRequest
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
        $safeUrl = 'regex:~^(?:/(?!/)|https?://|mailto:|tel:|#)~i';

        return [
            'page_id' => ['required', 'integer', Rule::exists('pages', 'id')->whereNull('deleted_at')],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'block_key' => ['nullable', 'string', 'max:255', 'alpha_dash:ascii'],
            'type' => ['required', Rule::in(PageBlock::TYPES)],
            'content' => [
                'nullable',
                'string',
                function (string $attribute, mixed $value, callable $fail): void {
                    if ($this->input('type') === 'custom_html' && preg_match('/<\s*\/?\s*script\b/i', (string) $value)) {
                        $fail('Custom HTML blocks cannot contain script tags.');
                    }
                },
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
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_url' => ['nullable', 'string', 'max:500', $safeUrl],
            'secondary_button_text' => ['nullable', 'string', 'max:255'],
            'secondary_button_url' => ['nullable', 'string', 'max:500', $safeUrl],
            'background_color' => ['nullable', 'string', 'max:20', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/'],
            'text_color' => ['nullable', 'string', 'max:20', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/'],
            'settings' => ['nullable', 'array'],
            'status' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'block_key.alpha_dash' => 'The block key may contain only letters, numbers, dashes, and underscores.',
            'button_url.regex' => 'Enter an internal path, web URL, email link, phone link, or anchor.',
            'secondary_button_url.regex' => 'Enter an internal path, web URL, email link, phone link, or anchor.',
            'settings.array' => 'The settings must contain valid JSON.',
        ];
    }
}
