<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateWebsiteSettingRequest;
use App\Models\WebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
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

            $data[$field] = $request->file($field)->store('website-settings', 'public');

            if ($settings->{$field}) {
                $oldImages[] = $settings->{$field};
            }
        }

        $settings->update($data);

        if ($oldImages !== []) {
            Storage::disk('public')->delete($oldImages);
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
