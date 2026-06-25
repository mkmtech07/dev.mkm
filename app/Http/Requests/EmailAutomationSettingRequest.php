<?php

namespace App\Http\Requests;

use App\Models\EmailAutomationSetting;
use Illuminate\Foundation\Http\FormRequest;

class EmailAutomationSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = [];
        foreach (EmailAutomationSetting::TOGGLES as $toggle) {
            $data[$toggle] = $this->has($toggle) ? $this->boolean($toggle) : false;
        }

        $this->merge([
            ...$data,
            'admin_email' => $this->emailOrNull($this->input('admin_email')),
            'cc_email' => $this->emailOrNull($this->input('cc_email')),
            'bcc_email' => $this->emailOrNull($this->input('bcc_email')),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'contact_auto_reply' => ['nullable', 'boolean'],
            'contact_admin_alert' => ['nullable', 'boolean'],
            'lead_auto_reply' => ['nullable', 'boolean'],
            'lead_admin_alert' => ['nullable', 'boolean'],
            'newsletter_welcome' => ['nullable', 'boolean'],
            'backup_success_alert' => ['nullable', 'boolean'],
            'backup_failed_alert' => ['nullable', 'boolean'],
            'maintenance_alert' => ['nullable', 'boolean'],
            'admin_email' => ['nullable', 'email', 'max:255'],
            'cc_email' => ['nullable', 'email', 'max:255'],
            'bcc_email' => ['nullable', 'email', 'max:255'],
            'queue_emails' => ['nullable', 'boolean'],
            'status' => ['nullable', 'boolean'],
        ];
    }

    private function emailOrNull(mixed $value): ?string
    {
        $value = strtolower(trim(strip_tags((string) $value)));

        return $value === '' ? null : $value;
    }
}
