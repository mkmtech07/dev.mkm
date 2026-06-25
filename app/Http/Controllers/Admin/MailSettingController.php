<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MailSettingRequest;
use App\Http\Requests\TestMailRequest;
use App\Models\MailSetting;
use App\Services\ActivityLogger;
use App\Services\AdminNotificationService;
use App\Services\DynamicMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MailSettingController extends Controller
{
    public function edit(): View
    {
        $mailSetting = MailSetting::firstOrCreateSetting();

        return view('admin.mail-settings.edit', [
            'mailSetting' => $mailSetting,
        ]);
    }

    public function update(
        MailSettingRequest $request,
        DynamicMailService $mail,
        ActivityLogger $logger,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        $mailSetting = MailSetting::firstOrCreateSetting();
        $oldValues = $mail->maskSensitiveData($mailSetting);
        $data = $request->validated();

        if (($data['password'] ?? null) === null) {
            unset($data['password']);
        }

        $mailSetting->update($data);
        $mailSetting->refresh();

        $logger->log(
            'settings',
            'mail_settings',
            'Mail settings updated.',
            $mailSetting,
            $oldValues,
            $mail->maskSensitiveData($mailSetting),
        );

        $notifications->notifyAllAdmins(
            'Mail Settings Updated',
            'Mail configuration was updated from the admin panel.',
            'warning',
            'mail_settings',
            route('admin.mail-settings.edit', absolute: false),
            [
                'mail_setting_id' => $mailSetting->id,
                'mailer' => $mailSetting->mailer,
                'status' => $mailSetting->status,
            ],
        );

        return to_route('admin.mail-settings.edit')
            ->with('success', 'Mail settings updated successfully.');
    }

    public function test(
        TestMailRequest $request,
        DynamicMailService $mail,
        ActivityLogger $logger,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        $recipient = $request->validated('test_recipient');
        $mailSetting = MailSetting::firstOrCreateSetting();
        $mailSetting->update(['test_recipient' => $recipient]);

        $log = $mail->sendTestMail($recipient);

        if ($log?->status === 'sent') {
            $logger->log(
                'test_mail_sent',
                'mail_settings',
                'Test mail sent.',
                $log,
                null,
                ['mail_log_id' => $log->id, 'recipient' => $recipient, 'status' => $log->status],
            );

            return back()->with('success', 'Test email sent successfully.');
        }

        $errorMessage = $log?->error_message ?: 'Mail settings are not configured.';

        $logger->log(
            'test_mail_failed',
            'mail_settings',
            'Test mail failed.',
            $log,
            null,
            ['mail_log_id' => $log?->id, 'recipient' => $recipient, 'status' => 'failed', 'error' => $errorMessage],
            'failed',
        );

        $notifications->notifyAllAdmins(
            'Test Mail Failed',
            'A test email could not be delivered to '.$recipient.'.',
            'danger',
            'mail_settings',
            $log ? route('admin.mail-logs.show', $log, false) : route('admin.mail-settings.edit', absolute: false),
            ['mail_log_id' => $log?->id, 'status' => 'failed'],
        );

        return back()->with('error', 'Test email could not be sent. '.$errorMessage);
    }
}
