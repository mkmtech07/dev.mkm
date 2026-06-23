<?php

namespace App\Http\Requests;

use App\Models\FooterSocialLink;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FooterSocialLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['status' => $this->boolean('status')]);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'platform' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url:http,https', 'max:2048'],
            'icon' => ['nullable', 'string', 'max:255'],
            'target' => ['required', Rule::in(FooterSocialLink::TARGETS)],
            'status' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
