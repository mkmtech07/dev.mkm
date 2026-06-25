<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailAutomationSettingRequest;
use App\Models\MailSetting;
use App\Services\ActivityLogger;
use App\Services\EmailAutomationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailAutomationSettingController extends Controller
{
    public function edit(EmailAutomationService $automation): View
    {
        return view('admin.email-automation.edit', [
            'automationSetting' => $automation->getSettings(),
            'templateStatuses' => $automation->templateStatuses(),
            'mailSetting' => MailSetting::firstSetting(),
        ]);
    }

    public function update(
        EmailAutomationSettingRequest $request,
        EmailAutomationService $automation,
        ActivityLogger $logger,
    ): RedirectResponse {
        $settings = $automation->getSettings();
        $oldValues = $settings?->getAttributes() ?? [];
        $settings->update($request->validated());
        $settings->refresh();

        $logger->log(
            'settings',
            'email_automation',
            'Email automation settings updated.',
            $settings,
            $oldValues,
            $settings->getAttributes(),
        );

        return to_route('admin.email-automation.edit')
            ->with('success', 'Email automation settings updated successfully.');
    }
}
