<?php

namespace App\Http\Requests;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'service_id' => $this->filled('service_id') ? $this->integer('service_id') : null,
            'assigned_to' => $this->filled('assigned_to') ? $this->integer('assigned_to') : null,
            'status_active' => $this->boolean('status_active'),
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
            'message' => ['nullable', 'string'],
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')->whereNull('deleted_at')],
            'source' => ['required', Rule::in(Lead::SOURCES)],
            'status' => ['required', Rule::in(Lead::STATUSES)],
            'priority' => ['required', Rule::in(Lead::PRIORITIES)],
            'budget' => ['nullable', 'string', 'max:100'],
            'preferred_contact_method' => ['nullable', Rule::in(Lead::CONTACT_METHODS)],
            'follow_up_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'status_active' => ['required', 'boolean'],
        ];
    }
}
