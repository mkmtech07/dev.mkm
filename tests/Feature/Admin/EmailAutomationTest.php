<?php

namespace Tests\Feature\Admin;

use App\Models\AdminNotification;
use App\Models\BackupRecord;
use App\Models\EmailAutomationSetting;
use App\Models\MailLog;
use App\Models\MailSetting;
use App\Models\MaintenanceSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\WebsiteSetting;
use App\Services\BackupService;
use Database\Seeders\EmailTemplateSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class EmailAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_automation_routes_require_authentication(): void
    {
        $this->get(route('admin.email-automation.edit'))->assertRedirect(route('login'));
        $this->put(route('admin.email-automation.update'), $this->settingsData())->assertRedirect(route('login'));
    }

    public function test_admin_can_update_email_automation_settings_and_validation_runs(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->get(route('admin.email-automation.edit'))
            ->assertOk()
            ->assertSee('Email Sending Automation')
            ->assertSee('Template missing');
        $this->assertDatabaseCount('email_automation_settings', 1);

        $this->actingAs($admin)
            ->from(route('admin.email-automation.edit'))
            ->put(route('admin.email-automation.update'), [
                'admin_email' => 'not-an-email',
                'cc_email' => 'bad-cc',
                'bcc_email' => 'bad-bcc',
            ])
            ->assertRedirect(route('admin.email-automation.edit'))
            ->assertSessionHasErrors(['admin_email', 'cc_email', 'bcc_email']);

        $this->actingAs($admin)->put(route('admin.email-automation.update'), $this->settingsData([
            'contact_auto_reply' => '0',
            'admin_email' => 'ops@example.com',
            'cc_email' => 'cc@example.com',
            'bcc_email' => 'bcc@example.com',
            'queue_emails' => '1',
        ]))->assertRedirect(route('admin.email-automation.edit'))
            ->assertSessionHasNoErrors();

        $setting = EmailAutomationSetting::firstOrFail();
        $this->assertFalse($setting->contact_auto_reply);
        $this->assertTrue($setting->lead_admin_alert);
        $this->assertTrue($setting->queue_emails);
        $this->assertSame('ops@example.com', $setting->admin_email);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'settings',
            'module' => 'email_automation',
            'model_id' => $setting->id,
        ]);
    }

    public function test_contact_lead_and_newsletter_automation_create_mail_logs_without_breaking_public_flows(): void
    {
        $this->prepareAutomation();

        $this->postJson(route('frontend.contact-messages.store'), [
            'name' => 'Priya Shah',
            'phone' => '+91 98765 43210',
            'email' => 'priya@example.com',
            'subject' => 'Website enquiry',
            'message' => 'Please share more information.',
        ])->assertCreated();

        $this->assertDatabaseHas('mail_logs', [
            'recipient' => 'priya@example.com',
            'template_slug' => 'contact-reply',
            'mail_type' => 'contact_auto_reply',
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('mail_logs', [
            'recipient' => 'admin@example.com',
            'template_slug' => 'contact-admin-alert',
            'mail_type' => 'contact_admin_alert',
            'status' => 'sent',
        ]);

        $this->postJson(route('frontend.leads.store'), [
            'name' => 'Lead Customer',
            'email' => 'lead@example.com',
            'message' => 'Please send a quote.',
            'source' => 'quote_request',
            'preferred_contact_method' => 'email',
        ])->assertCreated();

        $this->assertDatabaseHas('mail_logs', [
            'recipient' => 'lead@example.com',
            'template_slug' => 'lead-reply',
            'mail_type' => 'lead_auto_reply',
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('mail_logs', [
            'recipient' => 'admin@example.com',
            'template_slug' => 'lead-admin-alert',
            'mail_type' => 'lead_admin_alert',
            'status' => 'sent',
        ]);

        $this->postJson(route('frontend.newsletter.subscribe'), [
            'name' => 'Public Reader',
            'email' => 'reader@example.com',
            'source' => 'footer',
        ])->assertCreated();

        $this->postJson(route('frontend.newsletter.subscribe'), [
            'email' => 'reader@example.com',
            'source' => 'footer',
        ])->assertCreated();

        $this->assertSame(1, MailLog::where('mail_type', 'newsletter_welcome')->count());
        $this->assertDatabaseHas('mail_logs', [
            'recipient' => 'reader@example.com',
            'template_slug' => 'newsletter-welcome',
            'mail_type' => 'newsletter_welcome',
            'status' => 'sent',
        ]);
    }

    public function test_backup_and_maintenance_automation_create_alert_mail_logs(): void
    {
        $admin = User::factory()->create();
        $this->prepareAutomation();

        $this->mock(BackupService::class, function ($mock) {
            $mock->shouldReceive('generate')->once()->andReturn([
                'file_name' => 'backup-success.zip',
                'file_path' => BackupService::DIRECTORY.'/backup-success.zip',
                'disk' => BackupService::DISK,
                'file_size' => 1234,
                'message' => 'Backup generated successfully.',
            ]);
        });

        $this->actingAs($admin)->post(route('admin.backups.store'), [
            'name' => 'Successful backup',
            'type' => 'database',
        ])->assertRedirect();

        $this->assertDatabaseHas('mail_logs', [
            'recipient' => 'admin@example.com',
            'template_slug' => 'backup-success',
            'mail_type' => 'backup_success_alert',
            'status' => 'sent',
        ]);

        $this->mock(BackupService::class, function ($mock) {
            $mock->shouldReceive('generate')->once()->andThrow(new RuntimeException('ZIP support is unavailable.'));
        });

        $this->actingAs($admin)->post(route('admin.backups.store'), [
            'name' => 'Failed backup',
            'type' => 'database',
        ])->assertRedirect();

        $this->assertDatabaseHas('mail_logs', [
            'recipient' => 'admin@example.com',
            'template_slug' => 'backup-failed',
            'mail_type' => 'backup_failed_alert',
            'status' => 'sent',
        ]);

        MaintenanceSetting::create(MaintenanceSetting::defaults());
        $this->actingAs($admin)->put(route('admin.website.maintenance.update'), [
            'status' => '1',
            'mode' => 'frontend_only',
            'retry_after_minutes' => 60,
            'meta_robots' => 'noindex',
        ])->assertSessionHasNoErrors();
        $this->actingAs($admin)->put(route('admin.website.maintenance.update'), [
            'status' => '0',
            'mode' => 'frontend_only',
            'retry_after_minutes' => 60,
            'meta_robots' => 'noindex',
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, MailLog::where('mail_type', 'maintenance_alert')->where('status', 'sent')->count());
    }

    public function test_automation_failures_are_logged_and_do_not_break_original_flow(): void
    {
        WebsiteSetting::create([
            'site_name' => 'BillSoft CMS',
            'email' => 'admin@example.com',
            'status' => true,
        ]);
        EmailAutomationSetting::create([
            ...EmailAutomationSetting::defaults(),
            'admin_email' => 'admin@example.com',
        ]);

        $this->postJson(route('frontend.contact-messages.store'), [
            'name' => 'Priya Shah',
            'email' => 'priya@example.com',
            'subject' => 'Website enquiry',
            'message' => 'Please share more information.',
        ])->assertCreated();

        $this->assertDatabaseHas('mail_logs', [
            'recipient' => 'admin@example.com',
            'template_slug' => 'contact-admin-alert',
            'mail_type' => 'contact_admin_alert',
            'status' => 'failed',
            'error_message' => 'Required email template is missing or inactive.',
        ]);
        $this->assertDatabaseHas('admin_notifications', [
            'title' => 'Email Template Missing',
            'module' => 'email_automation',
        ]);

        $this->seed(EmailTemplateSeeder::class);
        MailLog::query()->delete();
        AdminNotification::query()->delete();

        $this->postJson(route('frontend.leads.store'), [
            'name' => 'Lead Customer',
            'email' => 'lead@example.com',
            'message' => 'Please send a quote.',
            'source' => 'quote_request',
        ])->assertCreated();

        $this->assertDatabaseHas('mail_logs', [
            'recipient' => 'admin@example.com',
            'template_slug' => 'lead-admin-alert',
            'mail_type' => 'lead_admin_alert',
            'status' => 'failed',
            'error_message' => 'Mail settings are inactive or not configured.',
        ]);
        $this->assertDatabaseHas('admin_notifications', [
            'title' => 'Automated Email Failed',
            'module' => 'email_automation',
        ]);
    }

    public function test_permissions_are_seeded_and_sidebar_respects_access(): void
    {
        User::factory()->create();
        $staff = User::factory()->create();
        $dashboard = Permission::create(['name' => 'Dashboard View', 'slug' => 'dashboard.view', 'module' => 'Dashboard', 'status' => true]);
        $role = Role::create(['name' => 'Dashboard Reader', 'slug' => 'dashboard-reader', 'status' => true]);
        $role->permissions()->attach($dashboard);
        $staff->roles()->attach($role);

        $this->actingAs($staff)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Email Automation');
        $this->actingAs($staff)->get(route('admin.email-automation.edit'))->assertForbidden();

        $this->seed(RolePermissionSeeder::class);

        $this->assertDatabaseHas('permissions', ['slug' => 'email_automation.view', 'module' => 'Email Automation']);
        $this->assertDatabaseHas('permissions', ['slug' => 'email_automation.edit', 'module' => 'Email Automation']);
        $this->assertTrue(Role::where('slug', 'admin')->firstOrFail()->permissions()->where('slug', 'email_automation.edit')->exists());
        $this->assertFalse(Role::where('slug', 'editor')->firstOrFail()->permissions()->where('slug', 'email_automation.edit')->exists());
    }

    private function prepareAutomation(): void
    {
        $this->seed(EmailTemplateSeeder::class);
        WebsiteSetting::create([
            'site_name' => 'BillSoft CMS',
            'email' => 'admin@example.com',
            'phone' => '+91 90000 00000',
            'status' => true,
        ]);
        MailSetting::create([
            'mailer' => 'array',
            'from_address' => 'admin@example.com',
            'from_name' => 'BillSoft CMS',
            'status' => true,
        ]);
        EmailAutomationSetting::create([
            ...EmailAutomationSetting::defaults(),
            'admin_email' => 'admin@example.com',
        ]);
    }

    /** @return array<string, mixed> */
    private function settingsData(array $overrides = []): array
    {
        return array_merge([
            'contact_auto_reply' => '1',
            'contact_admin_alert' => '1',
            'lead_auto_reply' => '1',
            'lead_admin_alert' => '1',
            'newsletter_welcome' => '1',
            'backup_success_alert' => '1',
            'backup_failed_alert' => '1',
            'maintenance_alert' => '1',
            'admin_email' => 'admin@example.com',
            'cc_email' => '',
            'bcc_email' => '',
            'queue_emails' => '0',
            'status' => '1',
        ], $overrides);
    }
}
