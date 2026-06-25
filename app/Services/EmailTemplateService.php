<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EmailTemplateService
{
    public function getTemplateBySlug(string $slug): ?EmailTemplate
    {
        if (! Schema::hasTable('email_templates')) {
            return null;
        }

        return EmailTemplate::query()
            ->active()
            ->where('slug', Str::slug($slug))
            ->first();
    }

    public function getTemplateByType(string $type): ?EmailTemplate
    {
        if (! Schema::hasTable('email_templates') || ! in_array($type, EmailTemplate::TYPES, true)) {
            return null;
        }

        return EmailTemplate::query()
            ->active()
            ->where('type', $type)
            ->orderByDesc('is_default')
            ->oldest('id')
            ->first();
    }

    /** @param array<string, mixed> $data */
    public function renderSubject(EmailTemplate $template, array $data = []): string
    {
        return $this->replaceVariables((string) $template->subject, $data);
    }

    /** @param array<string, mixed> $data */
    public function renderBody(EmailTemplate $template, array $data = []): string
    {
        return $this->replaceVariables((string) $template->body, $data);
    }

    /** @param array<string, mixed> $data */
    public function replaceVariables(?string $content, array $data = []): string
    {
        $content ??= '';
        $values = [...$this->getDefaultVariables(), ...$data];

        foreach ($values as $key => $value) {
            $content = str_replace('{'.$key.'}', (string) $value, $content);
        }

        return $content;
    }

    /** @return array<string, string> */
    public function getDefaultVariables(): array
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
        ];
    }

    /** @return array<string, string> */
    public function getSampleData(): array
    {
        return [
            ...$this->getDefaultVariables(),
            'name' => 'Asha Patel',
            'email' => 'asha@example.com',
            'phone' => '+91 98765 43210',
            'subject' => 'Website enquiry',
            'message' => 'Please share more information about your services.',
            'lead_status' => 'New',
            'whatsapp' => '+91 98765 43210',
            'company_name' => 'Asha Industries',
            'service_name' => 'Business Consulting',
            'budget' => 'INR 50,000 - INR 1,00,000',
            'backup_name' => 'Daily database backup',
            'backup_status' => 'Completed',
            'file_size' => '2.50 MB',
            'error_message' => 'The backup archive could not be created.',
            'maintenance_status' => 'Enabled',
            'unsubscribe_url' => url('/newsletter/unsubscribe/example-token'),
        ];
    }

    /** @return array<int, string> */
    public function allVariables(): array
    {
        return array_keys($this->getSampleData());
    }

    private function websiteSettings(): ?WebsiteSetting
    {
        if (! Schema::hasTable('website_settings')) {
            return null;
        }

        return WebsiteSetting::query()->first();
    }
}
