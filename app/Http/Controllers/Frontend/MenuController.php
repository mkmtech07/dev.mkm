<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\MenuCacheService;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function header(MenuCacheService $menus): JsonResponse
    {
        return response()->json(['data' => $menus->get('header')]);
    }
}
