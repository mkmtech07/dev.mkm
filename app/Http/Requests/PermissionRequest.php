<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $description = trim(strip_tags((string) $this->input('description')));
        $module = trim(strip_tags((string) $this->input('module')));
        $slug = strtolower(trim((string) $this->input('slug')));
        $slug = preg_replace('/\s+/', '.', $slug) ?? $slug;

        $this->merge([
            'name' => trim(strip_tags((string) $this->input('name'))),
            'slug' => $slug,
            'module' => $module === '' ? null : $module,
            'description' => $description === '' ? null : $description,
        ]);
    }

    public function rules(): array
    {
        $permission = $this->route('permission');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'regex:/^[a-z0-9_]+(?:\.[a-z0-9_]+)+$/',
                Rule::unique('permissions', 'slug')->ignore($permission?->getKey()),
            ],
            'module' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
