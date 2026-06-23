<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use Illuminate\Http\JsonResponse;

class ThemeSettingController extends Controller
{
    public function show(): JsonResponse
    {
        $settings = ThemeSetting::query()->where('status', true)->oldest('id')->first();

        return response()->json($settings?->publicValues() ?? (object) []);
    }
}
