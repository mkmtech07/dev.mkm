<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\MailLog;
use App\Models\MailSetting;
use App\Models\WebsiteSetting;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class DynamicMailService
{
    public function __construct(
        private readonly EmailTemplateService $templates,
        private readonly AdminNotificationService $notifications,
    ) {
    }

    public function getActiveMailSettings(): ?MailSetting
    {
        if (! Schema::hasTable('mail_settings')) {
            return null;
        }

        return MailSetting::query()
            ->where('status', true)
            ->oldest('id')
            ->first();
    }

    public function applyMailConfig(?MailSetting $settings = null): bool
    {
        $settings ??= $this->getActiveMailSettings();

        if (! $settings || ! $settings->isConfigured()) {
            return false;
        }

        $mailer = $settings->mailer;
        $config = match ($mailer) {
            'smtp' => $this->smtpConfig($settings),
            'sendmail' => [
                ...config('mail.mailers.sendmail', []),
                'transport' => 'sendmail',
            ],
            'log' => [
                ...config('mail.mailers.log', []),
                'transport' => 'log',
            ],
            'array' => [
                ...config('mail.mailers.array', []),
                'transport' => 'array',
            ],
            default => [],
        };

        if ($config === []) {
            return false;
        }

        config([
            'mail.default' => $mailer,
            "mail.mailers.{$mailer}" => $config,
            'mail.from.address' => $this->fromAddress($settings),
            'mail.from.name' => $this->fromName($settings),
        ]);

        Mail::forgetMailers();

        return true;
    }

    public function sendTestMail(string $recipient): ?MailLog
    {
        return $this->deliver(
            $recipient,
            'SMTP Test Email from '.$this->siteName(),
            'This is a test email from your CMS mail settings.',
            null,
            'test',
            ['source' => 'mail_settings'],
            notifyOnFailure: false,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function sendMail(
        string $recipient,
        string $subject,
        string $body,
        ?string $templateSlug = null,
        array $data = [],
        ?string $mailType = null,
        array $options = [],
    ): ?MailLog {
        $mailType ??= $this->mailType($templateSlug, $data);
        [$subject, $body] = $this->renderTemplateIfAvailable($subject, $body, $templateSlug, $data);

        return $this->deliver($recipient, $subject, $body, $templateSlug, $mailType, $data, options: $options);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function logMail(
        ?string $recipient,
        ?string $subject,
        ?string $templateSlug,
        ?string $mailType,
        string $status,
        ?string $errorMessage = null,
        array $data = [],
    ): ?MailLog {
        if (! Schema::hasTable('mail_logs')) {
            return null;
        }

        $status = in_array($status, MailLog::STATUSES, true) ? $status : 'failed';

        return MailLog::create([
            'recipient' => $recipient ? Str::limit($recipient, 255, '') : null,
            'subject' => $subject ? Str::limit(strip_tags($subject), 255, '') : null,
            'template_slug' => $templateSlug ? Str::limit(Str::slug($templateSlug), 255, '') : null,
            'mail_type' => $mailType ? Str::limit(Str::snake($mailType), 255, '') : null,
            'status' => $status,
            'error_message' => $errorMessage ? Str::limit(strip_tags($errorMessage), 5000, '') : null,
            'sent_at' => $status === 'sent' ? now() : null,
            'data' => $this->maskSensitiveData($data),
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * @param MailSetting|array<string, mixed> $settings
     * @return array<string, mixed>
     */
    public function maskSensitiveData(MailSetting|array $settings): array
    {
        if ($settings instanceof MailSetting) {
            return [
                'id' => $settings->id,
                'mailer' => $settings->mailer,
                'host' => $settings->host,
                'port' => $settings->port,
                'username' => $settings->username ? '[configured]' : null,
                'password' => $settings->hasStoredPassword() ? '[encrypted]' : null,
                'encryption' => $settings->encryption,
                'from_address' => $settings->from_address,
                'from_name' => $settings->from_name,
                'reply_to_address' => $settings->reply_to_address,
                'reply_to_name' => $settings->reply_to_name,
                'timeout' => $settings->timeout,
                'test_recipient' => $settings->test_recipient,
                'status' => $settings->status,
            ];
        }

        $masked = [];
        foreach (array_slice($settings, 0, 100, true) as $key => $value) {
            $key = (string) $key;
            if ($this->isSensitiveKey($key)) {
                $masked[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $masked[$key] = $this->maskSensitiveData($value);
            } elseif (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
                $masked[$key] = $value;
            } else {
                $masked[$key] = Str::limit(strip_tags((string) $value), 1000, '');
            }
        }

        return $masked;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function deliver(
        string $recipient,
        string $subject,
        string $body,
        ?string $templateSlug,
        string $mailType,
        array $data,
        bool $notifyOnFailure = true,
        array $options = [],
    ): ?MailLog {
        $settings = $this->getActiveMailSettings();

        try {
            if (! $settings || ! $this->applyMailConfig($settings)) {
                throw new \RuntimeException('Mail settings are not configured.');
            }

            Mail::raw($body, function (Message $message) use ($recipient, $subject, $settings, $options): void {
                $message->to($recipient)->subject($subject);

                if ($settings->from_address) {
                    $message->from($settings->from_address, $settings->from_name ?: null);
                }

                if ($settings->reply_to_address) {
                    $message->replyTo($settings->reply_to_address, $settings->reply_to_name ?: null);
                }

                if (! empty($options['cc'])) {
                    $message->cc($options['cc']);
                }

                if (! empty($options['bcc'])) {
                    $message->bcc($options['bcc']);
                }
            });

            return $this->logMail($recipient, $subject, $templateSlug, $mailType, 'sent', data: $data);
        } catch (Throwable $exception) {
            $log = $this->logMail(
                $recipient,
                $subject,
                $templateSlug,
                $mailType,
                'failed',
                $this->safeErrorMessage($exception, $settings),
                $data,
            );

            if ($notifyOnFailure && $log) {
                $this->notifyDeliveryFailure($log);
            }

            return $log;
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return array{0: string, 1: string}
     */
    private function renderTemplateIfAvailable(string $subject, string $body, ?string $templateSlug, array $data): array
    {
        if (! $templateSlug) {
            return [$subject, $body];
        }

        $template = $this->templates->getTemplateBySlug($templateSlug);
        if (! $template instanceof EmailTemplate) {
            return [$subject, $body];
        }

        return [
            $this->templates->renderSubject($template, $data) ?: $subject,
            $this->templates->renderBody($template, $data) ?: $body,
        ];
    }

    /** @param array<string, mixed> $data */
    private function mailType(?string $templateSlug, array $data): string
    {
        $mailType = $data['mail_type'] ?? $data['_mail_type'] ?? null;

        if (is_string($mailType) && trim($mailType) !== '') {
            return $mailType;
        }

        return $templateSlug ? 'template' : 'general';
    }

    /** @return array<string, mixed> */
    private function smtpConfig(MailSetting $settings): array
    {
        $encryption = $settings->encryption ?: 'tls';

        $config = [
            ...config('mail.mailers.smtp', []),
            'transport' => 'smtp',
            'scheme' => $encryption === 'ssl' ? 'smtps' : 'smtp',
            'host' => $settings->host,
            'port' => $settings->port ?: $this->defaultSmtpPort($encryption),
            'username' => $settings->username ?: null,
            'password' => $settings->password ?: null,
            'timeout' => $settings->timeout ?: 30,
            'local_domain' => parse_url((string) config('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'localhost',
        ];

        if ($encryption === 'none') {
            $config['auto_tls'] = false;
        }

        unset($config['url']);

        return $config;
    }

    private function defaultSmtpPort(?string $encryption): int
    {
        return match ($encryption) {
            'ssl' => 465,
            'none' => 25,
            default => 587,
        };
    }

    private function fromAddress(MailSetting $settings): string
    {
        return $settings->from_address
            ?: $this->websiteSettings()?->email
            ?: (string) config('mail.from.address', 'hello@example.com');
    }

    private function fromName(MailSetting $settings): string
    {
        return $settings->from_name
            ?: $this->siteName();
    }

    private function siteName(): string
    {
        return $this->websiteSettings()?->site_name
            ?: (string) config('mail.from.name', config('app.name', 'CMS Website'));
    }

    private function websiteSettings(): ?WebsiteSetting
    {
        if (! Schema::hasTable('website_settings')) {
            return null;
        }

        return WebsiteSetting::query()->first();
    }

    private function safeErrorMessage(Throwable $exception, ?MailSetting $settings): string
    {
        $message = strip_tags($exception->getMessage()) ?: 'Mail delivery failed.';

        if ($settings) {
            foreach (['password', 'username'] as $field) {
                try {
                    $value = (string) ($settings->{$field} ?? '');
                } catch (Throwable) {
                    $value = '';
                }

                if ($value !== '') {
                    $message = str_replace($value, '[redacted]', $message);
                }
            }
        }

        $message = preg_replace('/(password|passwd|secret|token|api[_-]?key)\s*[:=]\s*[^\s,;]+/i', '$1=[redacted]', $message) ?: $message;

        return Str::limit($message, 1000, '');
    }

    private function notifyDeliveryFailure(MailLog $log): void
    {
        $this->notifications->notifyAllAdmins(
            'Mail Delivery Failed',
            'An email could not be delivered to '.$log->recipient.'.',
            'danger',
            'mail_settings',
            Route::has('admin.mail-logs.show') ? route('admin.mail-logs.show', $log, false) : null,
            [
                'mail_log_id' => $log->id,
                'mail_type' => $log->mail_type,
                'template_slug' => $log->template_slug,
                'status' => $log->status,
            ],
        );
    }

    private function isSensitiveKey(string $key): bool
    {
        return (bool) preg_match('/(?:password|passwd|secret|credential|authorization|access_?token|refresh_?token|api_?key|smtp)/i', $key);
    }
}
