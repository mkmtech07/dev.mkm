<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestMailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'test_recipient' => trim((string) $this->input('test_recipient')),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'test_recipient' => ['required', 'email', 'max:255'],
        ];
    }
}
