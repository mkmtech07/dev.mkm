<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class TenantSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $color = ['nullable', 'string', 'max:20', 'regex:/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/'];

        return [
            'logo' => ['nullable', File::image(allowSvg: true)->max(2048), 'mimes:jpg,jpeg,png,webp,svg'],
            'favicon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,ico,webp,svg', 'max:1024'],
            'primary_color' => $color,
            'secondary_color' => $color,
            'accent_color' => $color,
            'contact_email' => ['nullable', 'email:rfc', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:5000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'custom_css' => ['nullable', 'string', 'max:200000', $this->safeCss(...)],
            'custom_js' => ['nullable', 'string', 'max:200000', $this->safeJavaScript(...)],
            'timezone' => ['required', 'string', 'max:100'],
            'locale' => ['required', 'string', 'max:20'],
        ];
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
