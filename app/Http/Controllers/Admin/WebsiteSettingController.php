<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateWebsiteSettingRequest;
use App\Models\WebsiteSetting;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WebsiteSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => $this->settings(),
        ]);
    }

    public function update(UpdateWebsiteSettingRequest $request): RedirectResponse
    {
        $settings = $this->settings();
        $data = $request->safe()->except(['logo', 'favicon']);
        $oldImages = [];

        foreach (['logo', 'favicon'] as $field) {
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

        return to_route('admin.settings.edit')
            ->with('success', 'Website settings updated successfully.');
    }

    private function settings(): WebsiteSetting
    {
        return WebsiteSetting::firstOrCreate(
            [],
            ['site_name' => config('app.name', 'Laravel')]
        );
    }
}
