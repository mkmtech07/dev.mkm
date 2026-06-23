<?php

namespace App\Http\Requests;

use App\Models\FooterLink;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FooterLinkRequest extends FormRequest
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
            'footer_section_id' => [
                'nullable',
                'integer',
                Rule::exists('footer_sections', 'id')->whereNull('deleted_at'),
            ],
            'title' => ['required', 'string', 'max:255'],
            'url' => [
                'required', 'string', 'max:2048',
                'regex:~^(?:/(?!/)|https?://|mailto:|tel:|#)~i',
            ],
            'icon' => ['nullable', 'string', 'max:255'],
            'target' => ['required', Rule::in(FooterLink::TARGETS)],
            'status' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return ['url.regex' => 'Enter an internal path, web URL, email link, phone link, or anchor.'];
    }
}
