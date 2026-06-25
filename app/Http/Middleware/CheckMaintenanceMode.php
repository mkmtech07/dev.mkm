<?php

namespace App\Http\Middleware;

use App\Models\MaintenanceSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = MaintenanceSetting::firstSetting();

        if (! $settings || ! $settings->shouldBlockRequest($request)) {
            return $next($request);
        }

        $headers = ['Retry-After' => (string) $settings->retryAfterSeconds()];

        if ($request->expectsJson() || str_starts_with('/'.ltrim($request->path(), '/'), '/api')) {
            return response()->json([
                'message' => 'The website is currently under maintenance.',
                'maintenance' => $settings->publicPayload(true),
            ], 503, $headers);
        }

        return response()->view('frontend.app', [
            'maintenanceStatus' => $settings->publicPayload(true),
        ], 503, $headers);
    }
}
