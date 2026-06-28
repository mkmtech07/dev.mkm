<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TenantManager
{
    public const SESSION_KEY = 'admin_selected_tenant_id';

    private ?Tenant $currentTenant = null;

    public function current(): ?Tenant
    {
        if ($this->currentTenant) {
            return $this->currentTenant;
        }

        $tenantId = $this->currentId();

        return $tenantId ? Tenant::query()->find($tenantId) : null;
    }

    public function currentId(): ?int
    {
        if ($this->currentTenant) {
            return $this->currentTenant->id;
        }

        return $this->defaultTenant()?->id;
    }

    public function setCurrentTenant(?Tenant $tenant): void
    {
        $this->currentTenant = $tenant;
    }

    public function defaultTenant(): ?Tenant
    {
        if (! $this->tenantsReady()) {
            return null;
        }

        return Tenant::query()->where('slug', 'default')->first()
            ?? Tenant::query()->oldest('id')->first();
    }

    public function selectedTenant(): ?Tenant
    {
        if (! $this->tenantsReady() || ! app()->bound('session')) {
            return $this->defaultTenant();
        }

        $tenantId = session(self::SESSION_KEY);
        if ($tenantId) {
            $tenant = Tenant::query()->find($tenantId);
            if ($tenant) {
                return $tenant;
            }
        }

        return $this->defaultTenant();
    }

    public function switchTo(Tenant|int $tenant): ?Tenant
    {
        $tenant = $tenant instanceof Tenant ? $tenant : Tenant::query()->find($tenant);

        if (! $tenant || ! app()->bound('session')) {
            return null;
        }

        session([self::SESSION_KEY => $tenant->id]);
        $this->setCurrentTenant($tenant);

        return $tenant;
    }

    public function resolveForRequest(Request $request): ?Tenant
    {
        if (! $this->tenantsReady()) {
            return null;
        }

        if ($this->isAdminRequest($request)) {
            return $this->selectedTenant();
        }

        $host = $this->normalizedHost($request->getHost());

        if ($host) {
            $tenant = Tenant::query()
                ->whereNotNull('custom_domain')
                ->where('custom_domain', $host)
                ->first();

            if ($tenant) {
                return $tenant;
            }

            $subdomain = $this->subdomainFromHost($host);
            if ($subdomain) {
                $tenant = Tenant::query()->where('subdomain', $subdomain)->first();
                if ($tenant) {
                    return $tenant;
                }
            }
        }

        return $this->defaultTenant();
    }

    public function isPubliclyAvailable(Tenant $tenant): bool
    {
        if ($tenant->status !== Tenant::STATUS_ACTIVE) {
            return false;
        }

        return ! $tenant->is_demo
            || ! $tenant->demo_expires_at
            || $tenant->demo_expires_at->isFuture();
    }

    public function isAdminRequest(Request $request): bool
    {
        return $request->is('admin')
            || $request->is('admin/*')
            || $request->is('dashboard')
            || $request->is('profile*')
            || $request->is('login')
            || $request->is('register')
            || $request->is('logout')
            || $request->is('forgot-password')
            || $request->is('reset-password*')
            || $request->is('verify-email*')
            || $request->is('password/*');
    }

    public function publicUrl(Tenant $tenant, ?string $path = null): string
    {
        $base = rtrim((string) config('app.url'), '/');
        $scheme = parse_url($base, PHP_URL_SCHEME) ?: 'http';
        $host = parse_url($base, PHP_URL_HOST) ?: 'localhost';

        if ($tenant->custom_domain) {
            $base = $scheme.'://'.$tenant->custom_domain;
        } elseif ($tenant->subdomain) {
            $base = $scheme.'://'.$tenant->subdomain.'.'.$host;
        }

        if (! $path) {
            return $base;
        }

        return $base.'/'.ltrim($path, '/');
    }

    public function cacheKeySuffix(): string
    {
        return (string) ($this->currentId() ?? 'global');
    }

    private function tenantsReady(): bool
    {
        return Schema::hasTable('tenants');
    }

    private function normalizedHost(?string $host): ?string
    {
        $host = strtolower(trim((string) $host));
        $host = preg_replace('/:\d+$/', '', $host) ?: $host;
        $host = trim($host, '.');

        if ($host === '' || $host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        return $host;
    }

    private function subdomainFromHost(string $host): ?string
    {
        $parts = explode('.', $host);
        if (count($parts) < 3) {
            return null;
        }

        $subdomain = $parts[0];

        return in_array($subdomain, ['www', 'admin', 'mail', 'api'], true)
            ? null
            : Str::slug($subdomain);
    }
}
