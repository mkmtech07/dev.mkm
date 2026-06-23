<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebsiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_website_settings(): void
    {
        $this->get(route('admin.settings.edit'))
            ->assertRedirect(route('login'));

        $this->put(route('admin.settings.update'), ['site_name' => 'Example'])
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_website_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertViewIs('admin.settings.edit');

        $this->assertDatabaseHas('website_settings', [
            'id' => 1,
            'site_name' => config('app.name'),
        ]);
    }

    public function test_saved_branding_is_rendered_in_the_admin_and_frontend_shells(): void
    {
        $user = User::factory()->create();
        $logoPath = 'assets/images/settings/example-logo.png';

        WebsiteSetting::create([
            'site_name' => 'Example Business',
            'logo' => $logoPath,
            'favicon' => 'assets/images/settings/example-icon.png',
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Example Business')
            ->assertSee(asset($logoPath), false);

        $this->get('/')
            ->assertOk()
            ->assertSee('Example Business')
            ->assertSee('example-logo.png');
    }

    public function test_authenticated_users_can_update_settings_and_upload_images(): void
    {
        $this->useTemporaryPublicPath();

        $user = User::factory()->create();
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true
        );
        $logo = UploadedFile::fake()->createWithContent('logo.png', $png);
        $favicon = UploadedFile::fake()->createWithContent('favicon.png', $png);

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Example Business',
                'tagline' => 'Built for growth',
                'logo' => $logo,
                'favicon' => $favicon,
                'phone' => '+1 555 0100',
                'email' => 'hello@example.com',
                'address' => '123 Main Street',
                'whatsapp_number' => '+1 555 0100',
                'facebook_url' => 'https://facebook.com/example',
                'instagram_url' => 'https://instagram.com/example',
                'youtube_url' => 'https://youtube.com/@example',
                'meta_title' => 'Example Business',
                'meta_description' => 'Example business website.',
            ])
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('success');

        $settings = WebsiteSetting::firstOrFail();

        $this->assertSame('Example Business', $settings->site_name);
        $this->assertSame('hello@example.com', $settings->email);
        $this->assertFileExists(public_path($settings->logo));
        $this->assertFileExists(public_path($settings->favicon));

        $oldLogo = $settings->logo;

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Example Business',
                'logo' => UploadedFile::fake()->createWithContent('new-logo.png', $png),
            ])
            ->assertRedirect(route('admin.settings.edit'));

        $settings->refresh();

        $this->assertFileDoesNotExist(public_path($oldLogo));
        $this->assertFileExists(public_path($settings->logo));
    }

    public function test_settings_update_is_validated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.settings.edit'))
            ->put(route('admin.settings.update'), [
                'site_name' => '',
                'email' => 'not-an-email',
                'facebook_url' => 'not-a-url',
            ])
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasErrors(['site_name', 'email', 'facebook_url']);
    }

    private function useTemporaryPublicPath(): void
    {
        $publicPath = storage_path('framework/testing/public/'.Str::uuid());

        File::ensureDirectoryExists($publicPath);
        $this->app->usePublicPath($publicPath);
        $this->beforeApplicationDestroyed(fn () => File::deleteDirectory($publicPath));
    }
}
