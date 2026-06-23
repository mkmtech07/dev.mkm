<?php

namespace App\Http\Requests;

use App\Models\SchemaMarkup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchemaMarkupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['status' => $this->boolean('status')]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(SchemaMarkup::TYPES)],
            'schema_json' => ['required', 'json'],
            'status' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
