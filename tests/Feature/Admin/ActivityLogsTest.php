<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_log_routes_are_read_only_and_require_authentication(): void
    {
        $log = ActivityLog::withoutEvents(fn () => ActivityLog::create($this->logData()));

        $this->get(route('admin.activity-logs.index'))->assertRedirect(route('login'));
        $this->get(route('admin.activity-logs.show', $log))->assertRedirect(route('login'));
        $this->assertFalse(app('router')->has('admin.activity-logs.store'));
        $this->assertFalse(app('router')->has('admin.activity-logs.destroy'));
    }

    public function test_index_search_filters_cards_and_details_render(): void
    {
        $admin = User::factory()->create();
        $matching = ActivityLog::withoutEvents(fn () => ActivityLog::create($this->logData([
            'user_name' => 'Asha Admin', 'user_email' => 'asha@example.com',
            'action' => 'settings', 'module' => 'website_settings',
            'description' => 'Updated website branding.', 'ip_address' => '127.0.0.1',
            'old_values' => ['site_name' => 'Old'], 'new_values' => ['site_name' => 'New'],
        ])));
        ActivityLog::withoutEvents(fn () => ActivityLog::create($this->logData([
            'user_name' => 'Other Admin', 'action' => 'delete', 'module' => 'pages',
            'description' => 'Deleted a page.',
        ])));

        $this->actingAs($admin)->get(route('admin.activity-logs.index', [
            'search' => 'Asha', 'module' => 'website_settings', 'action' => 'settings', 'status' => 'success',
        ]))->assertOk()->assertSee('Asha Admin')->assertDontSee('Other Admin')->assertSee('Total activities');

        $this->actingAs($admin)->get(route('admin.activity-logs.show', $matching))
            ->assertOk()->assertSee('Updated website branding.')->assertSee('Previous values')->assertSee('New values');
    }

    public function test_major_admin_model_events_are_logged_with_old_and_new_values(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin)->post(route('admin.newsletter-subscribers.store'), [
            'name' => 'Audit Reader', 'email' => 'audit@example.com', 'source' => 'manual',
            'status' => 'subscribed', 'notes' => 'Initial note', 'status_active' => true,
        ])->assertRedirect();
        $subscriber = NewsletterSubscriber::firstOrFail();

        $created = ActivityLog::query()->where('action', 'create')->where('module', 'newsletter')->firstOrFail();
        $this->assertSame($admin->id, $created->user_id);
        $this->assertSame($subscriber->id, $created->model_id);
        $this->assertSame('audit@example.com', $created->new_values['email']);
        $this->assertSame('[REDACTED]', $created->new_values['unsubscribe_token']);

        $this->actingAs($admin)->patch(route('admin.newsletter-subscribers.status.update', $subscriber), [
            'status' => 'unsubscribed',
        ])->assertSessionHasNoErrors();
        $status = ActivityLog::query()->where('action', 'status')->where('module', 'newsletter')->latest()->firstOrFail();
        $this->assertSame('subscribed', $status->old_values['status']);
        $this->assertSame('unsubscribed', $status->new_values['status']);

        $this->actingAs($admin)->delete(route('admin.newsletter-subscribers.destroy', $subscriber));
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'delete', 'module' => 'newsletter', 'model_id' => $subscriber->id,
        ]);
    }

    public function test_logger_redacts_sensitive_values_and_never_logs_activity_logs_recursively(): void
    {
        $admin = User::factory()->create();
        $logger = app(ActivityLogger::class);
        $log = $logger->log('update', 'security', 'Updated secure configuration.', null, [
            'password' => 'old-secret', 'api_key' => 'old-key',
        ], [
            'password' => 'new-secret', 'remember_token' => 'token', 'safe_value' => 'visible',
        ], user: $admin);

        $this->assertNotNull($log);
        $this->assertSame('[REDACTED]', $log->old_values['password']);
        $this->assertSame('[REDACTED]', $log->old_values['api_key']);
        $this->assertSame('[REDACTED]', $log->new_values['remember_token']);
        $this->assertSame('visible', $log->new_values['safe_value']);
        $this->assertSame(1, ActivityLog::count());
    }

    public function test_login_logout_and_failed_login_are_logged(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com', 'password' => 'password']);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect();
        $this->assertDatabaseHas('activity_logs', ['action' => 'login', 'user_id' => $user->id, 'status' => 'success']);

        $this->post(route('logout'))->assertRedirect('/');
        $this->assertDatabaseHas('activity_logs', ['action' => 'logout', 'user_id' => $user->id]);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong-password']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'failed_login', 'status' => 'failed']);
        $failed = ActivityLog::query()->where('action', 'failed_login')->firstOrFail();
        $this->assertNull($failed->old_values);
        $this->assertNull($failed->new_values);
    }

    /** @return array<string, mixed> */
    private function logData(array $overrides = []): array
    {
        return array_merge([
            'user_name' => 'Admin User', 'user_email' => 'admin@example.com',
            'action' => 'update', 'module' => 'pages', 'description' => 'Updated Page #1.',
            'old_values' => ['title' => 'Old title'], 'new_values' => ['title' => 'New title'],
            'ip_address' => '127.0.0.1', 'user_agent' => 'PHPUnit',
            'url' => 'http://localhost/admin/pages/1', 'method' => 'PUT', 'status' => 'success',
        ], $overrides);
    }
}
