<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'source' => $this->input('source') ?: 'other',
            'service_id' => $this->filled('service_id') ? $this->integer('service_id') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')->where(fn ($query) => $query->where('status', true)->whereNull('deleted_at'))],
            'source' => ['required', Rule::in(['contact_form', 'quote_request', 'service_enquiry', 'other'])],
            'budget' => ['nullable', 'string', 'max:100'],
            'preferred_contact_method' => ['nullable', Rule::in(['phone', 'email', 'whatsapp', 'any'])],
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }
}
