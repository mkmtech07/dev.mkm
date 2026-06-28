<?php

namespace App\Http\Middleware;

use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(private readonly TenantManager $tenants)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->tenants->resolveForRequest($request);
        $this->tenants->setCurrentTenant($tenant);

        if ($tenant && ! $this->tenants->isAdminRequest($request) && ! $tenant->isPubliclyAvailable()) {
            return response()->view('tenant-unavailable', ['tenant' => $tenant], 503);
        }

        return $next($request);
    }
}
