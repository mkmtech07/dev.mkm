<?php

namespace App\Http\Requests;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => str($this->input('slug') ?: $this->input('name'))->slug()->toString(),
            'subdomain' => $this->input('subdomain') ? str($this->input('subdomain'))->slug()->toString() : null,
            'custom_domain' => $this->normalizeDomain($this->input('custom_domain')),
            'is_demo' => $this->boolean('is_demo'),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $tenant = $this->route('tenant');
        $tenantId = $tenant instanceof Tenant ? $tenant->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('tenants', 'slug')->ignore($tenantId)],
            'subdomain' => ['nullable', 'alpha_dash', 'max:120', Rule::unique('tenants', 'subdomain')->ignore($tenantId)],
            'custom_domain' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9][a-z0-9.-]*[a-z0-9]$/',
                Rule::unique('tenants', 'custom_domain')->ignore($tenantId),
            ],
            'status' => ['required', Rule::in(Tenant::STATUSES)],
            'is_demo' => ['nullable', 'boolean'],
            'demo_expires_at' => ['nullable', 'date'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'client_email' => ['nullable', 'email:rfc', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:50'],
            'allowed_modules' => ['nullable', 'array'],
            'allowed_modules.*' => ['string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }

    private function normalizeDomain(mixed $domain): ?string
    {
        $domain = strtolower(trim((string) $domain));
        if ($domain === '') {
            return null;
        }

        $domain = preg_replace('#^https?://#', '', $domain) ?: $domain;
        $domain = explode('/', $domain)[0] ?? $domain;
        $domain = preg_replace('/:\d+$/', '', $domain) ?: $domain;

        return trim($domain, '.');
    }
}
