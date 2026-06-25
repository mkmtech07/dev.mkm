<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class EmailAutomationSetting extends Model
{
    public const TOGGLES = [
        'contact_auto_reply',
        'contact_admin_alert',
        'lead_auto_reply',
        'lead_admin_alert',
        'newsletter_welcome',
        'backup_success_alert',
        'backup_failed_alert',
        'maintenance_alert',
        'queue_emails',
        'status',
    ];

    public const EVENT_TOGGLES = [
        'contact_auto_reply',
        'contact_admin_alert',
        'lead_auto_reply',
        'lead_admin_alert',
        'newsletter_welcome',
        'backup_success_alert',
        'backup_failed_alert',
        'maintenance_alert',
    ];

    protected $fillable = [
        ...self::TOGGLES,
        'admin_email',
        'cc_email',
        'bcc_email',
    ];

    protected function casts(): array
    {
        return [
            'contact_auto_reply' => 'boolean',
            'contact_admin_alert' => 'boolean',
            'lead_auto_reply' => 'boolean',
            'lead_admin_alert' => 'boolean',
            'newsletter_welcome' => 'boolean',
            'backup_success_alert' => 'boolean',
            'backup_failed_alert' => 'boolean',
            'maintenance_alert' => 'boolean',
            'queue_emails' => 'boolean',
            'status' => 'boolean',
        ];
    }

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'contact_auto_reply' => true,
            'contact_admin_alert' => true,
            'lead_auto_reply' => true,
            'lead_admin_alert' => true,
            'newsletter_welcome' => true,
            'backup_success_alert' => true,
            'backup_failed_alert' => true,
            'maintenance_alert' => true,
            'queue_emails' => false,
            'status' => true,
        ];
    }

    public static function firstSetting(): ?self
    {
        if (! Schema::hasTable('email_automation_settings')) {
            return null;
        }

        return self::query()->oldest('id')->first();
    }

    public static function firstOrCreateSetting(): self
    {
        return self::query()->oldest('id')->firstOrCreate([], self::defaults());
    }

    public static function label(string $value): string
    {
        return str($value)->replace('_', ' ')->title()->toString();
    }
}
