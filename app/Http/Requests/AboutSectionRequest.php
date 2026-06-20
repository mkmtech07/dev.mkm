<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AboutSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->boolean('status'),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'description' => ['required', 'string', 'max:10000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'mission' => ['nullable', 'string', 'max:5000'],
            'vision' => ['nullable', 'string', 'max:5000'],
            'years_of_experience' => ['nullable', 'integer', 'min:0'],
            'projects_completed' => ['nullable', 'integer', 'min:0'],
            'clients_served' => ['nullable', 'integer', 'min:0'],
            'team_members' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'boolean'],
        ];
    }
}
