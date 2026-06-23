<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThemeSettingRequest;
use App\Models\ThemeSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ThemeSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.website.theme-settings.edit', ['themeSetting' => $this->settings()]);
    }

    public function update(ThemeSettingRequest $request, ActivityLogger $logger): RedirectResponse
    {
        $settings = $this->settings();
        $oldValues = $settings->only([...ThemeSetting::PUBLIC_FIELDS, 'status']);
        $settings->update($request->validated());
        $logger->log(
            'settings', 'theme_settings', 'Updated public website theme settings.', $settings,
            $oldValues, $settings->fresh()->only([...ThemeSetting::PUBLIC_FIELDS, 'status'])
        );

        return to_route('admin.website.theme-settings.edit')->with('success', 'Theme settings updated successfully.');
    }

    public function reset(ActivityLogger $logger): RedirectResponse
    {
        $settings = $this->settings();
        $oldValues = $settings->only([...ThemeSetting::PUBLIC_FIELDS, 'status']);
        $settings->update(ThemeSetting::defaults());
        $logger->log(
            'reset', 'theme_settings', 'Reset public website theme settings to safe defaults.', $settings,
            $oldValues, $settings->fresh()->only([...ThemeSetting::PUBLIC_FIELDS, 'status'])
        );

        return to_route('admin.website.theme-settings.edit')->with('success', 'Theme settings reset to defaults.');
    }

    private function settings(): ThemeSetting
    {
        return ThemeSetting::query()->oldest('id')->firstOrCreate([], ThemeSetting::defaults());
    }
}
