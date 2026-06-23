<?php

namespace App\Http\Requests;

use App\Models\BackupRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = trim(strip_tags((string) $this->input('name')));
        $this->merge(['name' => $name === '' ? null : $name]);
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(BackupRecord::TYPES)],
        ];
    }
}
