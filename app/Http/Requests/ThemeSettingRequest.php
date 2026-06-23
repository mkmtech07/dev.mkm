<?php

namespace App\Http\Requests;

use App\Models\ThemeSetting;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThemeSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nullableFields = [
            'theme_name', ...ThemeSetting::COLOR_FIELDS, 'font_family', 'heading_font_family',
            'font_size', 'container_width', 'border_radius', 'button_radius', 'custom_css',
        ];
        $values = [];
        foreach ($nullableFields as $field) {
            $value = is_string($this->input($field)) ? trim((string) $this->input($field)) : $this->input($field);
            $values[$field] = $value === '' ? null : $value;
        }
        $values['theme_name'] = $values['theme_name'] === null ? null : strip_tags((string) $values['theme_name']);
        $values['status'] = $this->has('status') ? $this->boolean('status') : false;
        $this->merge($values);
    }

    public function rules(): array
    {
        $color = ['nullable', 'string', 'max:20', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/'];
        $dimension = ['nullable', 'string', 'max:20', 'regex:/^(?:0|(?:\d{1,4}(?:\.\d+)?)(?:px|rem|em|%|vw))$/'];

        return [
            'theme_name' => ['nullable', 'string', 'max:255'],
            ...array_fill_keys(ThemeSetting::COLOR_FIELDS, $color),
            'font_family' => ['nullable', 'string', 'max:255', Rule::in(array_keys(ThemeSetting::FONT_FAMILIES))],
            'heading_font_family' => ['nullable', 'string', 'max:255', Rule::in(array_keys(ThemeSetting::FONT_FAMILIES))],
            'font_size' => $dimension,
            'container_width' => $dimension,
            'border_radius' => $dimension,
            'button_radius' => $dimension,
            'layout_style' => ['required', Rule::in(ThemeSetting::LAYOUT_STYLES)],
            'header_style' => ['required', Rule::in(ThemeSetting::HEADER_STYLES)],
            'footer_style' => ['required', Rule::in(ThemeSetting::FOOTER_STYLES)],
            'theme_mode' => ['required', Rule::in(ThemeSetting::THEME_MODES)],
            'custom_css' => ['nullable', 'string', 'max:200000', $this->safeCss(...)],
            'status' => ['nullable', 'boolean'],
        ];
    }

    private function safeCss(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value && preg_match('~</?style|</?script|javascript\s*:|expression\s*\(|@import|-moz-binding|behavior\s*:|url\s*\(\s*["\']?\s*(?:javascript|data)\s*:~i', (string) $value)) {
            $fail('The custom CSS contains an unsafe construct.');
        }
    }
}
