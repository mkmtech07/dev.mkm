<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicNewsletterSubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->cleanText($this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => $this->cleanText($this->input('phone')),
            'source' => $this->input('source') ?: 'footer',
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'source' => ['required', Rule::in(['footer', 'popup', 'contact_page', 'blog', 'api', 'other'])],
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }

    private function cleanText(mixed $value): ?string
    {
        $value = trim(strip_tags((string) $value));

        return $value === '' ? null : $value;
    }
}
