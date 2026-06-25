<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\AdminNotification;
use App\Models\BackupRecord;
use App\Models\ContactMessage;
use App\Models\Lead;
use App\Models\MaintenanceSetting;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class AdminNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_routes_and_admin_api_require_authentication(): void
    {
        $notification = AdminNotification::create([
            'title' => 'System Alert',
            'type' => 'system',
        ]);

        $this->get(route('admin.notifications.index'))->assertRedirect(route('login'));
        $this->get(route('admin.notifications.show', $notification))->assertRedirect(route('login'));
        $this->post(route('admin.notifications.mark-read', $notification))->assertRedirect(route('login'));
        $this->delete(route('admin.notifications.destroy', $notification))->assertRedirect(route('login'));
        $this->getJson(route('admin.api.notifications.index'))->assertUnauthorized();
    }

    public function test_admin_can_filter_view_mark_read_mark_all_and_delete_notifications(): void
    {
        $admin = User::factory()->create();
        $matching = AdminNotification::create([
            'title' => 'Backup Failed',
            'message' => 'Backup generation failed.',
            'type' => 'danger',
            'module' => 'backups',
            'action_url' => '/admin/backups/1',
            'data' => ['backup_id' => 1],
        ]);
        $read = AdminNotification::create([
            'title' => 'Newsletter Subscriber',
            'message' => 'reader@example.com subscribed.',
            'type' => 'success',
            'module' => 'newsletter',
            'is_read' => true,
            'read_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('Backup Failed')
            ->assertSee('data-notification-count', false);

        $this->actingAs($admin)->get(route('admin.notifications.index', [
            'search' => 'Backup',
            'type' => 'danger',
            'module' => 'backups',
            'status' => 'unread',
        ]))->assertOk()
            ->assertSee('Backup Failed')
            ->assertDontSee('reader@example.com subscribed.');

        $this->actingAs($admin)->get(route('admin.notifications.show', $matching))
            ->assertOk()
            ->assertSee('Backup generation failed.')
            ->assertSee('"backup_id": 1');
        $this->assertTrue($matching->refresh()->is_read);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'mark_read',
            'module' => 'notifications',
            'model_id' => $matching->id,
        ]);

        $matching->update(['is_read' => false, 'read_at' => null]);
        $this->actingAs($admin)
            ->post(route('admin.notifications.mark-read', $matching))
            ->assertSessionHas('success');
        $this->assertTrue($matching->refresh()->is_read);

        $matching->update(['is_read' => false, 'read_at' => null]);
        $this->actingAs($admin)
            ->post(route('admin.notifications.mark-all-read'))
            ->assertSessionHas('success');
        $this->assertSame(0, AdminNotification::where('is_read', false)->count());

        $this->actingAs($admin)
            ->delete(route('admin.notifications.bulk-destroy'), ['notifications' => [$read->id]])
            ->assertSessionHas('success');
        $this->assertSoftDeleted($read);

        $this->actingAs($admin)
            ->delete(route('admin.notifications.destroy', $matching))
            ->assertRedirect(route('admin.notifications.index'));
        $this->assertSoftDeleted($matching);
    }

    public function test_admin_notification_api_returns_updates_and_deletes_dropdown_data(): void
    {
        $admin = User::factory()->create();
        $notification = AdminNotification::create([
            'title' => 'New Lead Received',
            'message' => 'Public Customer submitted a lead/enquiry.',
            'type' => 'success',
            'module' => 'leads',
            'action_url' => '/admin/leads/1',
        ]);

        $this->actingAs($admin)->getJson(route('admin.api.notifications.unread-count'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1);

        $this->actingAs($admin)->getJson(route('admin.api.notifications.index'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.title', 'New Lead Received')
            ->assertJsonPath('data.0.url', '/admin/leads/1');

        $this->actingAs($admin)->postJson(route('admin.api.notifications.mark-read', $notification))
            ->assertOk()
            ->assertJsonPath('unread_count', 0);
        $this->assertTrue($notification->refresh()->is_read);

        $notification->update(['is_read' => false, 'read_at' => null]);
        $this->actingAs($admin)->postJson(route('admin.api.notifications.mark-all-read'))
            ->assertOk()
            ->assertJsonPath('marked_count', 1);

        $this->actingAs($admin)->deleteJson(route('admin.api.notifications.destroy', $notification))
            ->assertOk();
        $this->assertSoftDeleted($notification);
    }

    public function test_public_contact_lead_and_newsletter_events_create_admin_notifications(): void
    {
        $this->postJson(route('frontend.contact-messages.store'), [
            'name' => 'Priya Shah',
            'email' => 'priya@example.com',
            'subject' => 'Website enquiry',
            'message' => 'Please share more information.',
        ])->assertCreated();

        $contactMessage = ContactMessage::firstOrFail();
        $this->assertDatabaseHas('admin_notifications', [
            'title' => 'New Contact Message',
            'module' => 'contact_messages',
            'action_url' => route('admin.contact-messages.show', $contactMessage, false),
        ]);

        $this->postJson(route('frontend.leads.store'), [
            'name' => 'Public Customer',
            'email' => 'customer@example.com',
            'message' => 'Please send a quote.',
            'source' => 'quote_request',
            'preferred_contact_method' => 'email',
        ])->assertCreated();

        $lead = Lead::firstOrFail();
        $this->assertDatabaseHas('admin_notifications', [
            'title' => 'New Lead Received',
            'module' => 'leads',
            'action_url' => route('admin.leads.show', $lead, false),
        ]);

        $this->postJson(route('frontend.newsletter.subscribe'), [
            'name' => 'Public Reader',
            'email' => 'reader@example.com',
            'source' => 'footer',
        ])->assertCreated();

        $subscriber = NewsletterSubscriber::firstOrFail();
        $this->assertDatabaseHas('admin_notifications', [
            'title' => 'New Newsletter Subscriber',
            'module' => 'newsletter',
            'action_url' => route('admin.newsletter-subscribers.show', $subscriber, false),
        ]);

        $this->postJson(route('frontend.newsletter.subscribe'), [
            'email' => 'reader@example.com',
            'source' => 'footer',
        ])->assertCreated();
        $this->assertSame(1, AdminNotification::where('title', 'New Newsletter Subscriber')->count());
    }

    public function test_backup_success_failure_and_maintenance_status_create_notifications(): void
    {
        $admin = User::factory()->create();

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

        $completed = BackupRecord::where('status', 'completed')->firstOrFail();
        $this->assertDatabaseHas('admin_notifications', [
            'title' => 'Backup Completed',
            'module' => 'backups',
            'action_url' => route('admin.backups.show', $completed, false),
        ]);

        $this->mock(BackupService::class, function ($mock) {
            $mock->shouldReceive('generate')->once()->andThrow(new RuntimeException('ZIP support is unavailable.'));
        });

        $this->actingAs($admin)->post(route('admin.backups.store'), [
            'name' => 'Failed backup',
            'type' => 'database',
        ])->assertRedirect();

        $failed = BackupRecord::where('status', 'failed')->firstOrFail();
        $this->assertDatabaseHas('admin_notifications', [
            'title' => 'Backup Failed',
            'type' => 'danger',
            'module' => 'backups',
            'action_url' => route('admin.backups.show', $failed, false),
        ]);

        MaintenanceSetting::create(MaintenanceSetting::defaults());
        $this->actingAs($admin)->put(route('admin.website.maintenance.update'), [
            'status' => '1',
            'mode' => 'frontend_only',
            'retry_after_minutes' => 60,
            'meta_robots' => 'noindex',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('admin_notifications', [
            'title' => 'Maintenance Mode Enabled',
            'type' => 'warning',
            'module' => 'maintenance',
        ]);

        $this->actingAs($admin)->put(route('admin.website.maintenance.update'), [
            'status' => '0',
            'mode' => 'frontend_only',
            'retry_after_minutes' => 60,
            'meta_robots' => 'noindex',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('admin_notifications', [
            'title' => 'Maintenance Mode Disabled',
            'type' => 'info',
            'module' => 'maintenance',
        ]);
    }
}
