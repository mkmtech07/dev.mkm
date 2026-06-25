<?php

namespace App\Http\Requests;

use App\Models\EmailTemplate;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $slugSource = trim((string) $this->input('slug')) ?: (string) $this->input('name');
        $variables = $this->normalizeVariables($this->input('available_variables'));

        $this->merge([
            'name' => strip_tags(trim((string) $this->input('name'))),
            'slug' => Str::slug($slugSource),
            'subject' => $this->blankToNull($this->input('subject')),
            'type' => $this->input('type') ?: 'custom',
            'available_variables' => $variables === [] ? null : $variables,
            'status' => $this->has('status') ? $this->boolean('status') : false,
            'is_default' => $this->has('is_default') ? $this->boolean('is_default') : false,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $template = $this->route('email_template') ?? $this->route('emailTemplate');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('email_templates', 'slug')->ignore($template),
            ],
            'subject' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(EmailTemplate::TYPES)],
            'body' => ['required', 'string', $this->safeBody(...)],
            'available_variables' => ['nullable', 'array'],
            'available_variables.*' => ['string', 'max:100', 'regex:/^[A-Za-z0-9_]+$/'],
            'status' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    private function safeBody(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value && preg_match('~</?script|javascript\s*:|on\w+\s*=|<iframe|<object|<embed|<form|<input|<button|<meta|<link~i', (string) $value)) {
            $fail('The email body contains unsafe HTML.');
        }
    }

    private function blankToNull(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return $value === '' || $value === null ? null : strip_tags((string) $value);
    }

    /** @return array<int, string> */
    private function normalizeVariables(mixed $value): array
    {
        if (is_array($value)) {
            $items = $value;
        } else {
            $raw = trim((string) $value);
            if ($raw === '') {
                return [];
            }

            $decoded = json_decode($raw, true);
            $items = is_array($decoded) ? $decoded : (preg_split('/[\r\n,]+/', $raw) ?: []);
        }

        return collect($items)
            ->map(fn (mixed $item) => trim((string) $item))
            ->map(fn (string $item) => trim($item, "{} \t\n\r\0\x0B"))
            ->filter(fn (string $item) => $item !== '')
            ->unique()
            ->values()
            ->all();
    }
}
