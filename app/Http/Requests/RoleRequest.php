<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim(strip_tags((string) $this->input('name'))),
            'slug' => str((string) $this->input('slug'))->slug()->toString(),
            'description' => $this->filled('description') ? trim(strip_tags((string) $this->input('description'))) : null,
        ]);
    }

    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('roles', 'slug')->ignore($role?->getKey())],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'distinct', Rule::exists('permissions', 'id')->whereNull('deleted_at')],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
