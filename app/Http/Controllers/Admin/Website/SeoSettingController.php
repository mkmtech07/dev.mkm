<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeoSettingRequest;
use App\Models\SeoSetting;
use App\Services\SeoManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SeoSettingController extends Controller
{
    public function edit(SeoManager $seoManager): View
    {
        return view('admin.website.seo.settings.edit', [
            'seoSetting' => $seoManager->settings(create: true),
        ]);
    }

    public function update(SeoSettingRequest $request): RedirectResponse
    {
        $seoSetting = SeoSetting::query()->oldest('id')->first()
            ?? SeoSetting::create(SeoSetting::defaults());
        $seoSetting->update($request->validated());
        Cache::forget(SeoManager::SITEMAP_CACHE_KEY);

        return to_route('admin.website.seo.settings.edit')->with('success', 'SEO settings updated successfully.');
    }
}
