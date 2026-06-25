<?php

namespace App\Http\Requests;

use App\Models\MaintenanceSetting;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class MaintenanceSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nullableFields = [
            'title',
            'message',
            'button_text',
            'button_url',
            'start_at',
            'end_at',
            'allowed_ips',
            'excluded_paths',
            'retry_after_minutes',
            'custom_css',
        ];

        $values = [];
        foreach ($nullableFields as $field) {
            $value = is_string($this->input($field)) ? trim((string) $this->input($field)) : $this->input($field);
            $values[$field] = $value === '' ? null : $value;
        }

        foreach (['title', 'button_text'] as $field) {
            $values[$field] = $values[$field] === null ? null : strip_tags((string) $values[$field]);
        }

        if ($values['message'] !== null) {
            $values['message'] = strip_tags((string) $values['message']);
        }

        $values['status'] = $this->has('status') ? $this->boolean('status') : false;
        $values['mode'] = $this->input('mode') ?: MaintenanceSetting::MODE_FRONTEND_ONLY;
        $values['meta_robots'] = $this->input('meta_robots') ?: 'noindex';

        $this->merge($values);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'boolean'],
            'mode' => ['required', Rule::in(MaintenanceSetting::MODES)],
            'title' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'image' => ['nullable', File::image(allowSvg: true)->max(2048), 'mimes:jpg,jpeg,png,webp,svg'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_url' => ['nullable', 'string', 'max:500', $this->safeUrl(...)],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'allowed_ips' => ['nullable', 'string', $this->validIpList(...)],
            'excluded_paths' => ['nullable', 'string', $this->validPathList(...)],
            'retry_after_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'meta_robots' => ['required', Rule::in(MaintenanceSetting::ROBOTS)],
            'custom_css' => ['nullable', 'string', 'max:200000', $this->safeCss(...)],
        ];
    }

    private function safeUrl(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $value = (string) $value;
        $isRelative = str_starts_with($value, '/') && ! str_starts_with($value, '//') && ! str_contains($value, '..');
        $isAbsolute = filter_var($value, FILTER_VALIDATE_URL)
            && in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true);

        if (! $isRelative && ! $isAbsolute) {
            $fail('The button URL must be an HTTPS/HTTP URL or a site-relative path.');
        }
    }

    private function validIpList(string $attribute, mixed $value, Closure $fail): void
    {
        foreach ($this->splitLinesAndCommas($value) as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP)) {
                $fail("The allowed IP address '{$ip}' is invalid.");
            }
        }
    }

    private function validPathList(string $attribute, mixed $value, Closure $fail): void
    {
        foreach ($this->splitLinesAndCommas($value) as $path) {
            if (! str_starts_with($path, '/') || str_contains($path, '..') || str_contains($path, '://')) {
                $fail("The excluded path '{$path}' must be a safe site-relative path.");
            }
        }
    }

    private function safeCss(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value && preg_match('~</?style|</?script|javascript\s*:|expression\s*\(|@import|-moz-binding|behavior\s*:|url\s*\(\s*["\']?\s*(?:javascript|data)\s*:~i', (string) $value)) {
            $fail('The custom CSS contains an unsafe construct.');
        }
    }

    /** @return array<int, string> */
    private function splitLinesAndCommas(mixed $value): array
    {
        return collect(preg_split('/[\r\n,]+/', (string) $value) ?: [])
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
