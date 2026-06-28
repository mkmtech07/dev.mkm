<?php

namespace App\Http\Requests;

use App\Support\MediaPicker;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FooterSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'newsletter_status' => $this->boolean('newsletter_status'),
            'status' => $this->boolean('status'),
            'remove_footer_logo' => $this->boolean('remove_footer_logo'),
        ]);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'footer_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ...MediaPicker::validationRules(['footer_logo']),
            'remove_footer_logo' => ['required', 'boolean'],
            'footer_description' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:5000'],
            'copyright_text' => ['nullable', 'string', 'max:255'],
            'newsletter_status' => ['required', 'boolean'],
            'status' => ['required', 'boolean'],
        ];
    }
}
