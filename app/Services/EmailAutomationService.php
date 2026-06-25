<?php

namespace App\Services;

use App\Jobs\SendAutomatedEmailJob;
use App\Models\BackupRecord;
use App\Models\ContactMessage;
use App\Models\EmailAutomationSetting;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\MailLog;
use App\Models\MailSetting;
use App\Models\MaintenanceSetting;
use App\Models\NewsletterSubscriber;
use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class EmailAutomationService
{
    /** @var array<string, array{label: string, template: string}> */
    public const TEMPLATE_MAP = [
        'contact_auto_reply' => ['label' => 'Contact Auto Reply', 'template' => 'contact_reply'],
        'contact_admin_alert' => ['label' => 'Contact Admin Alert', 'template' => 'contact-admin-alert'],
        'lead_auto_reply' => ['label' => 'Lead Auto Reply', 'template' => 'lead_reply'],
        'lead_admin_alert' => ['label' => 'Lead Admin Alert', 'template' => 'lead-admin-alert'],
        'newsletter_welcome' => ['label' => 'Newsletter Welcome', 'template' => 'newsletter_welcome'],
        'backup_success_alert' => ['label' => 'Backup Success Alert', 'template' => 'backup_success'],
        'backup_failed_alert' => ['label' => 'Backup Failed Alert', 'template' => 'backup_failed'],
        'maintenance_alert' => ['label' => 'Maintenance Alert', 'template' => 'maintenance_alert'],
    ];

    public function __construct(
        private readonly DynamicMailService $mail,
        private readonly EmailTemplateService $templates,
        private readonly AdminNotificationService $notifications,
        private readonly ActivityLogger $logger,
    ) {
    }

    public function getSettings(): ?EmailAutomationSetting
    {
        if (! Schema::hasTable('email_automation_settings')) {
            return null;
        }

        return EmailAutomationSetting::firstOrCreateSetting();
    }

    public function isEnabled(string $key): bool
    {
        $settings = $this->getSettings();

        return (bool) ($settings?->status && in_array($key, EmailAutomationSetting::EVENT_TOGGLES, true) && $settings->{$key});
    }

    public function getAdminEmail(): ?string
    {
        $settings = $this->getSettings();
        $mailSettings = MailSetting::firstSetting();
        $websiteSettings = $this->websiteSettings();

        return $settings?->admin_email
            ?: $mailSettings?->from_address
            ?: $websiteSettings?->email
            ?: null;
    }

    public function sendContactAutoReply(ContactMessage $contactMessage): ?MailLog
    {
        if (! $this->isEnabled('contact_auto_reply')) {
            return null;
        }

        return $this->safeSend($contactMessage->email, 'contact_reply', $this->contactVariables($contactMessage), 'contact_auto_reply');
    }

    public function sendContactAdminAlert(ContactMessage $contactMessage): ?MailLog
    {
        if (! $this->isEnabled('contact_admin_alert')) {
            return null;
        }

        return $this->safeSend($this->getAdminEmail(), 'contact-admin-alert', $this->contactVariables($contactMessage), 'contact_admin_alert');
    }

    public function sendLeadAutoReply(Lead $lead): ?MailLog
    {
        if (! $this->isEnabled('lead_auto_reply')) {
            return null;
        }

        return $this->safeSend($lead->email, 'lead_reply', $this->leadVariables($lead), 'lead_auto_reply');
    }

    public function sendLeadAdminAlert(Lead $lead): ?MailLog
    {
        if (! $this->isEnabled('lead_admin_alert')) {
            return null;
        }

        return $this->safeSend($this->getAdminEmail(), 'lead-admin-alert', $this->leadVariables($lead), 'lead_admin_alert');
    }

    public function sendNewsletterWelcome(NewsletterSubscriber $subscriber): ?MailLog
    {
        if (! $this->isEnabled('newsletter_welcome')) {
            return null;
        }

        return $this->safeSend($subscriber->email, 'newsletter_welcome', $this->newsletterVariables($subscriber), 'newsletter_welcome');
    }

    public function sendBackupSuccessAlert(BackupRecord $backup): ?MailLog
    {
        if (! $this->isEnabled('backup_success_alert')) {
            return null;
        }

        return $this->safeSend($this->getAdminEmail(), 'backup_success', $this->backupVariables($backup, 'Completed'), 'backup_success_alert');
    }

    public function sendBackupFailedAlert(BackupRecord $backup): ?MailLog
    {
        if (! $this->isEnabled('backup_failed_alert')) {
            return null;
        }

        return $this->safeSend($this->getAdminEmail(), 'backup_failed', $this->backupVariables($backup, 'Failed'), 'backup_failed_alert');
    }

    public function sendMaintenanceAlert(MaintenanceSetting $maintenanceSetting, string $status): ?MailLog
    {
        if (! $this->isEnabled('maintenance_alert')) {
            return null;
        }

        return $this->safeSend($this->getAdminEmail(), 'maintenance_alert', $this->buildCommonVariables([
            'maintenance_status' => Str::headline($status),
            'status' => Str::headline($status),
            'maintenance_setting_id' => $maintenanceSetting->id,
        ]), 'maintenance_alert');
    }

    /** @param array<string, mixed> $extra @return array<string, mixed> */
    public function buildCommonVariables(array $extra = []): array
    {
        $settings = $this->websiteSettings();

        return [
            'site_name' => $settings?->site_name ?: config('app.name', 'CMS Website'),
            'site_email' => $settings?->email ?: '',
            'site_phone' => $settings?->phone ?: '',
            'site_url' => url('/'),
            'date' => now()->format('d M Y'),
            'time' => now()->format('h:i A'),
            'year' => now()->format('Y'),
            ...$this->sanitizeData($extra),
        ];
    }

    /** @param array<string, mixed> $data */
    public function safeSend(?string $recipient, string $templateTypeOrSlug, array $data = [], ?string $mailType = null): ?MailLog
    {
        try {
            $settings = $this->getSettings();
            if ($settings?->queue_emails && $this->queueIsSafe()) {
                SendAutomatedEmailJob::dispatch($recipient, $templateTypeOrSlug, $data, $mailType);

                return $this->mail->logMail(
                    $recipient,
                    null,
                    Str::slug($templateTypeOrSlug),
                    $mailType ?: 'automated',
                    'pending',
                    data: ['queued' => true, 'template' => $templateTypeOrSlug],
                );
            }

            return $this->sendNow($recipient, $templateTypeOrSlug, $data, $mailType);
        } catch (Throwable $exception) {
            return $this->recordFailure($recipient, $templateTypeOrSlug, $mailType, 'Email automation failed safely.', [
                'exception' => $exception::class,
            ]);
        }
    }

    /** @param array<string, mixed> $data */
    public function sendNow(?string $recipient, string $templateTypeOrSlug, array $data = [], ?string $mailType = null): ?MailLog
    {
        $mailType ??= Str::snake($templateTypeOrSlug);
        $recipient = $recipient ? trim($recipient) : null;

        if (! $recipient) {
            return $this->recordFailure(null, $templateTypeOrSlug, $mailType, 'No recipient was available for automated email.', $data);
        }

        $template = $this->resolveTemplate($templateTypeOrSlug);
        if (! $template) {
            return $this->recordFailure($recipient, $templateTypeOrSlug, $mailType, 'Required email template is missing or inactive.', $data);
        }

        $mailSettings = $this->mail->getActiveMailSettings();
        if (! $mailSettings?->isConfigured()) {
            return $this->recordFailure($recipient, $template->slug, $mailType, 'Mail settings are inactive or not configured.', $data);
        }

        try {
            $payload = $this->buildCommonVariables($data);
            $subject = $this->templates->renderSubject($template, $payload) ?: EmailTemplate::label($template->type);
            $body = $this->templates->renderBody($template, $payload);
            $log = $this->mail->sendMail(
                $recipient,
                $subject,
                $body,
                $template->slug,
                $payload,
                $mailType,
                $this->mailOptions(),
            );

            if ($log?->status === 'sent') {
                $this->logger->log(
                    'automated_email_sent',
                    'email_automation',
                    'Automated email sent.',
                    $log,
                    null,
                    ['mail_log_id' => $log->id, 'mail_type' => $mailType, 'template_slug' => $template->slug],
                );
            } else {
                $this->logger->log(
                    'automated_email_failed',
                    'email_automation',
                    'Automated email failed.',
                    $log,
                    null,
                    ['mail_log_id' => $log?->id, 'mail_type' => $mailType, 'template_slug' => $template->slug],
                    'failed',
                );
            }

            return $log;
        } catch (Throwable $exception) {
            return $this->recordFailure($recipient, $template->slug, $mailType, 'Automated email failed safely.', [
                ...$data,
                'exception' => $exception::class,
            ]);
        }
    }

    /** @return array<string, array<string, mixed>> */
    public function templateStatuses(): array
    {
        $statuses = [];
        foreach (self::TEMPLATE_MAP as $key => $meta) {
            $template = $this->resolveTemplate($meta['template']);
            $statuses[$key] = [
                ...$meta,
                'available' => (bool) $template,
                'slug' => $template?->slug,
                'subject' => $template?->subject,
            ];
        }

        return $statuses;
    }

    private function resolveTemplate(string $templateTypeOrSlug): ?EmailTemplate
    {
        $slug = Str::slug($templateTypeOrSlug);
        $template = $this->templates->getTemplateBySlug($slug);

        if ($template) {
            return $template;
        }

        if (in_array($templateTypeOrSlug, EmailTemplate::TYPES, true)) {
            return $this->templates->getTemplateByType($templateTypeOrSlug);
        }

        return match ($slug) {
            'contact-admin-alert', 'lead-admin-alert' => $this->templates->getTemplateByType('admin_alert'),
            default => null,
        };
    }

    /** @param array<string, mixed> $data */
    private function recordFailure(?string $recipient, string $templateTypeOrSlug, ?string $mailType, string $message, array $data = []): ?MailLog
    {
        $mailType ??= Str::snake($templateTypeOrSlug);
        $log = $this->mail->logMail(
            $recipient,
            null,
            Str::slug($templateTypeOrSlug),
            $mailType,
            'failed',
            $message,
            $data,
        );

        $this->logger->log(
            'automated_email_failed',
            'email_automation',
            $message,
            $log,
            null,
            ['mail_log_id' => $log?->id, 'mail_type' => $mailType, 'template' => $templateTypeOrSlug],
            'failed',
        );

        if (in_array($mailType, ['contact_admin_alert', 'lead_admin_alert', 'backup_failed_alert', 'backup_success_alert', 'maintenance_alert'], true)) {
            $this->notifications->notifyAllAdmins(
                str_contains($message, 'template') ? 'Email Template Missing' : 'Automated Email Failed',
                $message,
                'warning',
                'email_automation',
                $log ? route('admin.mail-logs.show', $log, false) : route('admin.email-automation.edit', absolute: false),
                ['mail_log_id' => $log?->id, 'mail_type' => $mailType, 'template' => $templateTypeOrSlug],
            );
        }

        return $log;
    }

    private function queueIsSafe(): bool
    {
        return config('queue.default') === 'deferred';
    }

    /** @return array<string, string> */
    private function mailOptions(): array
    {
        $settings = $this->getSettings();

        return array_filter([
            'cc' => $settings?->cc_email,
            'bcc' => $settings?->bcc_email,
        ], fn (?string $value) => filled($value));
    }

    /** @return array<string, mixed> */
    private function contactVariables(ContactMessage $contactMessage): array
    {
        return [
            'name' => $contactMessage->name,
            'email' => $contactMessage->email,
            'phone' => $contactMessage->phone,
            'subject' => $contactMessage->subject,
            'message' => $contactMessage->message,
            'contact_message_id' => $contactMessage->id,
        ];
    }

    /** @return array<string, mixed> */
    private function leadVariables(Lead $lead): array
    {
        $lead->loadMissing('service:id,title');

        return [
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'whatsapp' => $lead->whatsapp,
            'company_name' => $lead->company_name,
            'subject' => $lead->subject,
            'message' => $lead->message,
            'lead_status' => Lead::label($lead->status ?: 'new'),
            'service_name' => $lead->service?->title,
            'budget' => $lead->budget,
            'lead_id' => $lead->id,
        ];
    }

    /** @return array<string, mixed> */
    private function newsletterVariables(NewsletterSubscriber $subscriber): array
    {
        return [
            'name' => $subscriber->name ?: 'Subscriber',
            'email' => $subscriber->email,
            'unsubscribe_url' => url('/newsletter/unsubscribe?email='.urlencode($subscriber->email).'&unsubscribe_token='.urlencode((string) $subscriber->unsubscribe_token)),
            'newsletter_subscriber_id' => $subscriber->id,
        ];
    }

    /** @return array<string, mixed> */
    private function backupVariables(BackupRecord $backup, string $status): array
    {
        return [
            'backup_name' => $backup->name ?: $backup->file_name ?: 'Backup',
            'backup_status' => $status,
            'file_size' => $backup->formattedFileSize(),
            'error_message' => $backup->message,
            'backup_id' => $backup->id,
        ];
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function sanitizeData(array $data): array
    {
        $sanitized = [];
        foreach (array_slice($data, 0, 100, true) as $key => $value) {
            $key = (string) $key;
            if (preg_match('/(?:password|passwd|secret|token|credential|authorization|api_?key|smtp)/i', $key)) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeData($value);
            } elseif (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
                $sanitized[$key] = $value;
            } else {
                $sanitized[$key] = Str::limit(strip_tags((string) $value), 2000, '');
            }
        }

        return $sanitized;
    }

    private function websiteSettings(): ?WebsiteSetting
    {
        if (! Schema::hasTable('website_settings')) {
            return null;
        }

        return WebsiteSetting::query()->first();
    }
}
