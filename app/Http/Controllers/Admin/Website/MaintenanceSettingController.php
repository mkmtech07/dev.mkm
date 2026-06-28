<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaintenanceSettingRequest;
use App\Models\MaintenanceSetting;
use App\Services\ActivityLogger;
use App\Services\AdminNotificationService;
use App\Services\EmailAutomationService;
use App\Support\MediaPicker;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MaintenanceSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.website.maintenance.edit', [
            'maintenanceSetting' => MaintenanceSetting::firstOrCreateSetting(),
        ]);
    }

    public function update(
        MaintenanceSettingRequest $request,
        ActivityLogger $logger,
        AdminNotificationService $notifications,
        EmailAutomationService $automation,
    ): RedirectResponse
    {
        $settings = MaintenanceSetting::firstOrCreateSetting();
        $oldValues = $settings->getAttributes();
        $oldStatus = (bool) $settings->status;
        $data = $request->safe()->except(['image', ...MediaPicker::fieldInputs(['image'])]);

        $data['retry_after_minutes'] = $data['retry_after_minutes'] ?? 60;
        $data['updated_by'] = $request->user()?->getKey();

        $oldImage = null;
        if ($request->hasFile('image')) {
            $data['image'] = PublicImage::store($request->file('image'), 'maintenance');
            $oldImage = $settings->image;
        } elseif ($selectedPath = MediaPicker::selectedPath($request, 'image')) {
            $data['image'] = $selectedPath;
            if ($settings->image && $settings->image !== $selectedPath) {
                $oldImage = $settings->image;
            }
        } elseif (MediaPicker::shouldClear($request, 'image')) {
            $data['image'] = null;
            $oldImage = $settings->image;
        }

        $settings->update($data);

        if ($oldImage) {
            PublicImage::delete($oldImage);
        }

        $settings->refresh();
        $description = match (true) {
            ! $oldStatus && $settings->status => 'Maintenance mode enabled.',
            $oldStatus && ! $settings->status => 'Maintenance mode disabled.',
            default => 'Maintenance settings updated.',
        };

        $logger->log(
            $settings->status !== $oldStatus ? 'status' : 'settings',
            'maintenance',
            $description,
            $settings,
            $oldValues,
            $settings->getAttributes()
        );

        if ($settings->status !== $oldStatus) {
            $notifications->notifyAllAdmins(
                $settings->status ? 'Maintenance Mode Enabled' : 'Maintenance Mode Disabled',
                $settings->status
                    ? 'Public website maintenance mode is now enabled.'
                    : 'Public website maintenance mode is now disabled.',
                $settings->status ? 'warning' : 'info',
                'maintenance',
                route('admin.website.maintenance.edit', absolute: false),
                ['maintenance_setting_id' => $settings->id, 'status' => $settings->status]
            );

            $automation->sendMaintenanceAlert($settings, $settings->status ? 'enabled' : 'disabled');
        }

        return to_route('admin.website.maintenance.edit')
            ->with('success', 'Maintenance settings updated successfully.');
    }
}
