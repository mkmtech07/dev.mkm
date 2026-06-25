<?php

namespace Tests\Feature\Admin;

use App\Models\MaintenanceSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_maintenance_settings(): void
    {
        $this->get(route('admin.website.maintenance.edit'))->assertRedirect(route('login'));
        $this->put(route('admin.website.maintenance.update'), [])->assertRedirect(route('login'));
    }

    public function test_admin_can_view_update_upload_and_disable_maintenance_settings(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.website.maintenance.edit'))
            ->assertOk()
            ->assertViewIs('admin.website.maintenance.edit')
            ->assertSee('Maintenance Mode Manager');

        $this->assertDatabaseHas('maintenance_settings', [
            'id' => 1,
            'status' => false,
            'mode' => 'frontend_only',
        ]);

        $this->useTemporaryPublicPath();
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true
        );

        $response = $this->actingAs($admin)->put(route('admin.website.maintenance.update'), [
            'status' => '1',
            'mode' => 'full_site',
            'title' => 'Planned Maintenance',
            'message' => 'We will be back soon.',
            'image' => UploadedFile::fake()->createWithContent('maintenance.png', $png),
            'button_text' => 'Contact Us',
            'button_url' => '/contact',
            'start_at' => now()->subMinute()->format('Y-m-d H:i:s'),
            'end_at' => now()->addHour()->format('Y-m-d H:i:s'),
            'allowed_ips' => "127.0.0.1\n203.0.113.10",
            'excluded_paths' => "/contact\n/api/services",
            'retry_after_minutes' => 15,
            'meta_robots' => 'noindex',
            'custom_css' => '.maintenance-page { background: #fff; }',
        ]);

        $response->assertRedirect(route('admin.website.maintenance.edit'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $settings = MaintenanceSetting::firstOrFail();
        $this->assertTrue($settings->status);
        $this->assertSame('full_site', $settings->mode);
        $this->assertSame($admin->id, $settings->updated_by);
        $this->assertFileExists(public_path($settings->image));
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'maintenance',
            'description' => 'Maintenance mode enabled.',
        ]);

        $this->actingAs($admin)->put(route('admin.website.maintenance.update'), [
            'status' => '0',
            'mode' => 'frontend_only',
            'retry_after_minutes' => 60,
            'meta_robots' => 'index',
        ])->assertRedirect(route('admin.website.maintenance.edit'))
            ->assertSessionHasNoErrors();

        $this->assertFalse($settings->fresh()->status);
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'maintenance',
            'description' => 'Maintenance mode disabled.',
        ]);
    }

    public function test_maintenance_middleware_blocks_frontend_and_full_site_without_blocking_admin_or_login(): void
    {
        MaintenanceSetting::create([
            'status' => true,
            'mode' => 'frontend_only',
            'title' => 'Short Break',
            'retry_after_minutes' => 5,
            'meta_robots' => 'noindex',
        ]);

        $this->get('/services')
            ->assertStatus(503)
            ->assertHeader('Retry-After', '300')
            ->assertSee('frontend-app');

        $this->getJson('/api/services')->assertOk();

        $this->getJson('/api/maintenance-status?path=/services')
            ->assertOk()
            ->assertJsonPath('enabled', true)
            ->assertJsonMissing(['id' => 1]);

        MaintenanceSetting::firstOrFail()->update(['mode' => 'full_site']);

        $this->getJson('/api/services')
            ->assertStatus(503)
            ->assertJsonPath('maintenance.enabled', true);

        $this->get(route('login'))->assertOk();

        $admin = User::factory()->create();
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_allowed_ips_and_excluded_paths_bypass_maintenance_mode(): void
    {
        MaintenanceSetting::create([
            'status' => true,
            'mode' => 'full_site',
            'allowed_ips' => '10.10.10.10',
            'excluded_paths' => "/contact\n/api/services",
            'retry_after_minutes' => 60,
            'meta_robots' => 'noindex',
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '10.10.10.10'])
            ->get('/services')
            ->assertOk();

        $this->get('/contact')->assertOk();
        $this->getJson('/api/services')->assertOk();

        $this->getJson('/api/maintenance-status?path=/contact')
            ->assertOk()
            ->assertJsonPath('enabled', false);
    }

    public function test_scheduled_window_controls_when_maintenance_is_active(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-25 12:00:00'));

        try {
            $settings = MaintenanceSetting::create([
                'status' => true,
                'mode' => 'frontend_only',
                'start_at' => now()->addHour(),
                'retry_after_minutes' => 60,
                'meta_robots' => 'noindex',
            ]);

            $this->get('/')->assertOk();
            $this->getJson('/api/maintenance-status?path=/')
                ->assertOk()
                ->assertJsonPath('enabled', false);

            $settings->update([
                'start_at' => null,
                'end_at' => now()->subMinute(),
            ]);

            $this->get('/')->assertOk();

            $settings->update(['end_at' => null]);

            $this->get('/')->assertStatus(503);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_maintenance_settings_are_validated(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->from(route('admin.website.maintenance.edit'))
            ->put(route('admin.website.maintenance.update'), [
                'status' => '1',
                'mode' => 'invalid',
                'button_url' => 'javascript:alert(1)',
                'end_at' => now()->subDay()->format('Y-m-d H:i:s'),
                'start_at' => now()->format('Y-m-d H:i:s'),
                'allowed_ips' => 'not-an-ip',
                'excluded_paths' => 'admin',
                'retry_after_minutes' => 0,
                'meta_robots' => 'crawl',
                'custom_css' => '@import url("https://example.com/x.css");',
            ])
            ->assertRedirect(route('admin.website.maintenance.edit'))
            ->assertSessionHasErrors([
                'mode',
                'button_url',
                'end_at',
                'allowed_ips',
                'excluded_paths',
                'retry_after_minutes',
                'meta_robots',
                'custom_css',
            ]);
    }

    private function useTemporaryPublicPath(): void
    {
        $publicPath = storage_path('framework/testing/public/'.Str::uuid());

        File::ensureDirectoryExists($publicPath);
        $this->app->usePublicPath($publicPath);
        $this->beforeApplicationDestroyed(fn () => File::deleteDirectory($publicPath));
    }
}
