<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /** @var array<int, array<string, mixed>> */
    private const TEMPLATES = [
        [
            'name' => 'Contact Reply',
            'slug' => 'contact-reply',
            'subject' => 'Thank you for contacting {site_name}',
            'type' => 'contact_reply',
            'body' => "Hello {name},\n\nThank you for contacting {site_name}. We received your message about \"{subject}\" and our team will contact you soon.\n\nBest regards,\n{site_name}",
            'available_variables' => ['name', 'email', 'phone', 'subject', 'message', 'site_name', 'site_email', 'site_phone', 'site_url', 'date', 'time', 'year'],
        ],
        [
            'name' => 'Contact Admin Alert',
            'slug' => 'contact-admin-alert',
            'subject' => 'New Contact Message Received',
            'type' => 'admin_alert',
            'body' => "Hello Admin,\n\nA new contact message was submitted on {site_name}.\n\nName: {name}\nEmail: {email}\nPhone: {phone}\nSubject: {subject}\nMessage: {message}\n\nReceived: {date} {time}",
            'available_variables' => ['name', 'email', 'phone', 'subject', 'message', 'site_name', 'date', 'time'],
        ],
        [
            'name' => 'Lead Reply',
            'slug' => 'lead-reply',
            'subject' => 'We received your enquiry',
            'type' => 'lead_reply',
            'body' => "Hello {name},\n\nThank you for your enquiry. Our team will review your request and contact you soon.\n\nBest regards,\n{site_name}",
            'available_variables' => ['name', 'email', 'phone', 'whatsapp', 'company_name', 'subject', 'message', 'lead_status', 'service_name', 'budget', 'site_name', 'site_url', 'date', 'time', 'year'],
        ],
        [
            'name' => 'Lead Admin Alert',
            'slug' => 'lead-admin-alert',
            'subject' => 'New Lead / Enquiry Received',
            'type' => 'admin_alert',
            'body' => "Hello Admin,\n\nA new lead was submitted on {site_name}.\n\nName: {name}\nEmail: {email}\nPhone: {phone}\nWhatsApp: {whatsapp}\nCompany: {company_name}\nService: {service_name}\nBudget: {budget}\nSubject: {subject}\nMessage: {message}\n\nReceived: {date} {time}",
            'available_variables' => ['name', 'email', 'phone', 'whatsapp', 'company_name', 'subject', 'message', 'lead_status', 'service_name', 'budget', 'site_name', 'date', 'time'],
        ],
        [
            'name' => 'Newsletter Welcome',
            'slug' => 'newsletter-welcome',
            'subject' => 'Welcome to {site_name}',
            'type' => 'newsletter_welcome',
            'body' => "Hello {name},\n\nThank you for subscribing to our newsletter.\n\nBest regards,\n{site_name}",
            'available_variables' => ['name', 'email', 'site_name', 'unsubscribe_url', 'date', 'time', 'year'],
        ],
        [
            'name' => 'Backup Success',
            'slug' => 'backup-success',
            'subject' => 'Backup completed successfully',
            'type' => 'backup_success',
            'body' => "Hello Admin,\n\nBackup {backup_name} has been completed successfully on {date} at {time}.\n\nStatus: {backup_status}\nFile size: {file_size}",
            'available_variables' => ['backup_name', 'backup_status', 'file_size', 'date', 'time', 'site_name'],
        ],
        [
            'name' => 'Backup Failed',
            'slug' => 'backup-failed',
            'subject' => 'Backup failed',
            'type' => 'backup_failed',
            'body' => "Hello Admin,\n\nBackup {backup_name} failed on {date} at {time}.\n\nStatus: {backup_status}\nError: {error_message}",
            'available_variables' => ['backup_name', 'backup_status', 'error_message', 'date', 'time', 'site_name'],
        ],
        [
            'name' => 'Maintenance Alert',
            'slug' => 'maintenance-alert',
            'subject' => 'Maintenance mode status changed',
            'type' => 'maintenance_alert',
            'body' => "Hello Admin,\n\nMaintenance mode is now {maintenance_status} on {site_name}.\n\nChanged: {date} {time}",
            'available_variables' => ['maintenance_status', 'site_name', 'date', 'time', 'year'],
        ],
    ];

    public function run(): void
    {
        foreach (self::TEMPLATES as $template) {
            $record = EmailTemplate::withTrashed()->firstOrNew(['slug' => $template['slug']]);
            $record->fill([
                ...$template,
                'status' => true,
                'is_default' => true,
            ]);
            $record->deleted_at = null;
            $record->save();
        }
    }
}
