<?php

namespace Tests\Feature\Admin;

use App\Models\MailLog;
use App\Models\MailSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\WebsiteSetting;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MailSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_routes_require_authentication(): void
    {
        $mailLog = MailLog::create([
            'recipient' => 'admin@example.com',
            'subject' => 'Test subject',
            'mail_type' => 'test',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->get(route('admin.mail-settings.edit'))->assertRedirect(route('login'));
        $this->put(route('admin.mail-settings.update'), $this->settingsData())->assertRedirect(route('login'));
        $this->post(route('admin.mail-settings.test'), ['test_recipient' => 'admin@example.com'])->assertRedirect(route('login'));
        $this->get(route('admin.mail-logs.index'))->assertRedirect(route('login'));
        $this->get(route('admin.mail-logs.show', $mailLog))->assertRedirect(route('login'));
        $this->delete(route('admin.mail-logs.destroy', $mailLog))->assertRedirect(route('login'));
    }

    public function test_admin_can_update_mail_settings_and_password_stays_hidden(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->get(route('admin.mail-settings.edit'))
            ->assertOk()
            ->assertSee('SMTP / Mail Settings');
        $this->assertDatabaseCount('mail_settings', 1);

        $this->actingAs($admin)->put(route('admin.mail-settings.update'), $this->settingsData([
            'password' => 'smtp-secret',
        ]))->assertRedirect(route('admin.mail-settings.edit'))
            ->assertSessionHasNoErrors();

        $setting = MailSetting::firstOrFail();
        $this->assertSame('smtp-secret', $setting->password);
        $rawPassword = DB::table('mail_settings')->where('id', $setting->id)->value('password');
        $this->assertIsString($rawPassword);
        $this->assertNotSame('smtp-secret', $rawPassword);
        $this->assertStringNotContainsString('smtp-secret', $rawPassword);

        $this->actingAs($admin)->get(route('admin.mail-settings.edit'))
            ->assertOk()
            ->assertDontSee('smtp-secret')
            ->assertSee('Password already saved. Leave blank to keep existing password.');

        $this->actingAs($admin)->put(route('admin.mail-settings.update'), $this->settingsData([
            'host' => 'smtp2.example.com',
            'password' => '',
        ]))->assertRedirect(route('admin.mail-settings.edit'))
            ->assertSessionHasNoErrors();

        $this->assertSame('smtp-secret', $setting->refresh()->password);
        $this->assertSame('smtp2.example.com', $setting->host);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'settings',
            'module' => 'mail_settings',
            'model_id' => $setting->id,
        ]);
        $this->assertDatabaseHas('admin_notifications', [
            'title' => 'Mail Settings Updated',
            'module' => 'mail_settings',
        ]);
    }

    public function test_mail_settings_validation_rules_are_applied(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->from(route('admin.mail-settings.edit'))
            ->put(route('admin.mail-settings.update'), [
                'mailer' => 'ses',
                'host' => str_repeat('a', 256),
                'port' => 70000,
                'password' => str_repeat('x', 256),
                'encryption' => 'starttls',
                'from_address' => 'not-an-email',
                'reply_to_address' => 'also-not-email',
                'timeout' => 3,
                'test_recipient' => 'bad-recipient',
                'status' => '1',
            ])
            ->assertRedirect(route('admin.mail-settings.edit'))
            ->assertSessionHasErrors([
                'mailer',
                'host',
                'port',
                'password',
                'encryption',
                'from_address',
                'reply_to_address',
                'timeout',
                'test_recipient',
            ]);
    }

    public function test_test_mail_success_and_failure_are_logged_safely(): void
    {
        $admin = User::factory()->create();
        WebsiteSetting::create([
            'site_name' => 'BillSoft CMS',
            'email' => 'hello@example.com',
            'status' => true,
        ]);
        MailSetting::create([
            'mailer' => 'array',
            'from_address' => 'hello@example.com',
            'from_name' => 'BillSoft CMS',
            'test_recipient' => 'owner@example.com',
            'status' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.mail-settings.test'), [
            'test_recipient' => 'owner@example.com',
        ])->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mail_logs', [
            'recipient' => 'owner@example.com',
            'subject' => 'SMTP Test Email from BillSoft CMS',
            'mail_type' => 'test',
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'test_mail_sent',
            'module' => 'mail_settings',
        ]);

        MailSetting::query()->update(['status' => false]);

        $this->actingAs($admin)->post(route('admin.mail-settings.test'), [
            'test_recipient' => 'owner@example.com',
        ])->assertSessionHas('error');

        $this->assertDatabaseHas('mail_logs', [
            'recipient' => 'owner@example.com',
            'mail_type' => 'test',
            'status' => 'failed',
            'error_message' => 'Mail settings are not configured.',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'test_mail_failed',
            'module' => 'mail_settings',
            'status' => 'failed',
        ]);
        $this->assertDatabaseHas('admin_notifications', [
            'title' => 'Test Mail Failed',
            'module' => 'mail_settings',
        ]);
    }

    public function test_mail_logs_can_be_filtered_viewed_and_deleted(): void
    {
        $admin = User::factory()->create();
        $sent = MailLog::create([
            'recipient' => 'priya@example.com',
            'subject' => 'Welcome Priya',
            'mail_type' => 'test',
            'status' => 'sent',
            'sent_at' => now(),
            'created_by' => $admin->id,
        ]);
        $failed = MailLog::create([
            'recipient' => 'backup@example.com',
            'subject' => 'Backup failed',
            'template_slug' => 'backup-failed',
            'mail_type' => 'backup_alert',
            'status' => 'failed',
            'error_message' => 'Connection refused.',
            'data' => ['backup_name' => 'Daily backup'],
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get(route('admin.mail-logs.index', [
            'search' => 'Priya',
            'status' => 'sent',
            'mail_type' => 'test',
        ]))->assertOk()
            ->assertSee('priya@example.com')
            ->assertDontSee('backup@example.com');

        $this->actingAs($admin)->get(route('admin.mail-logs.show', $failed))
            ->assertOk()
            ->assertSee('Connection refused.')
            ->assertSee('backup-failed');

        $this->actingAs($admin)->delete(route('admin.mail-logs.destroy', $failed))
            ->assertRedirect(route('admin.mail-logs.index'));
        $this->assertSoftDeleted($failed);
        $this->assertNotSoftDeleted($sent);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'delete',
            'module' => 'mail_logs',
            'model_id' => $failed->id,
        ]);
    }

    public function test_permissions_are_seeded_and_routes_respect_access(): void
    {
        User::factory()->create();
        $staff = User::factory()->create();
        $dashboard = Permission::create(['name' => 'Dashboard View', 'slug' => 'dashboard.view', 'module' => 'Dashboard', 'status' => true]);
        $role = Role::create(['name' => 'Dashboard Reader', 'slug' => 'dashboard-reader', 'status' => true]);
        $role->permissions()->attach($dashboard);
        $staff->roles()->attach($role);

        $this->actingAs($staff)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Mail Settings')
            ->assertDontSee('Mail Logs');
        $this->actingAs($staff)->get(route('admin.mail-settings.edit'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.mail-logs.index'))->assertForbidden();

        $this->seed(RolePermissionSeeder::class);
        $this->assertDatabaseHas('permissions', ['slug' => 'mail_settings.test', 'module' => 'Mail Settings']);
        $this->assertDatabaseHas('permissions', ['slug' => 'mail_logs.delete', 'module' => 'Mail Logs']);
        $this->assertTrue(Role::where('slug', 'admin')->firstOrFail()->permissions()->where('slug', 'mail_settings.edit')->exists());
        $this->assertFalse(Role::where('slug', 'editor')->firstOrFail()->permissions()->where('slug', 'mail_settings.edit')->exists());
    }

    /** @return array<string, mixed> */
    private function settingsData(array $overrides = []): array
    {
        return array_merge([
            'mailer' => 'smtp',
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'mailer@example.com',
            'password' => 'smtp-secret',
            'encryption' => 'tls',
            'from_address' => 'hello@example.com',
            'from_name' => 'BillSoft CMS',
            'reply_to_address' => 'support@example.com',
            'reply_to_name' => 'Support Team',
            'timeout' => 30,
            'test_recipient' => 'owner@example.com',
            'status' => '1',
        ], $overrides);
    }
}
