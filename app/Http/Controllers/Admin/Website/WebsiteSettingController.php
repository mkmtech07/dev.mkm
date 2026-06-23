<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\WebsiteSettingRequest;
use App\Models\WebsiteSetting;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WebsiteSettingController extends Controller
{
    private const IMAGE_FIELDS = ['logo', 'white_logo', 'favicon', 'og_image'];

    public function edit(): View
    {
        $settings = $this->settings();

        if (request()->routeIs('admin.settings.*')) {
            if (! $settings->site_name) {
                $settings->update(['site_name' => config('app.name', 'Laravel')]);
            }

            return view('admin.settings.edit', compact('settings'));
        }

        return view('admin.website.settings.edit', [
            'websiteSetting' => $settings,
        ]);
    }

    public function update(WebsiteSettingRequest $request): RedirectResponse
    {
        $settings = $this->settings();
        $data = $request->safe()->except(self::IMAGE_FIELDS);
        $oldImages = [];

        foreach (self::IMAGE_FIELDS as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $data[$field] = PublicImage::store($request->file($field), 'settings');

            if ($settings->{$field}) {
                $oldImages[] = $settings->{$field};
            }
        }

        $settings->update($data);

        foreach ($oldImages as $oldImage) {
            PublicImage::delete($oldImage);
        }

        $route = $request->routeIs('admin.settings.*')
            ? 'admin.settings.edit'
            : 'admin.website.settings.edit';

        return to_route($route)->with('success', 'Website settings updated successfully.');
    }

    private function settings(): WebsiteSetting
    {
        return WebsiteSetting::query()->firstOrCreate([], [
            'site_name' => null,
            'status' => true,
        ]);
    }
}
