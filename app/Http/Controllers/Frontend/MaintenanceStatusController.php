<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceStatusController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'path' => ['nullable', 'string', 'max:2000'],
        ]);

        $settings = MaintenanceSetting::firstSetting();
        if (! $settings) {
            return response()->json((new MaintenanceSetting())->publicPayload(false));
        }

        $enabled = $settings->shouldBlockRequest($request, $validated['path'] ?? null);

        return response()->json($settings->publicPayload($enabled));
    }
}
