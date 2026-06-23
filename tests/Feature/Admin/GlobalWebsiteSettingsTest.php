<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class GlobalWebsiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_settings_routes_require_authentication(): void
    {
        $this->get(route('admin.website.settings.edit'))->assertRedirect(route('login'));
        $this->put(route('admin.website.settings.update'))->assertRedirect(route('login'));
    }

    public function test_settings_page_automatically_uses_one_row(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->get(route('admin.website.settings.edit'))
            ->assertOk()
            ->assertViewIs('admin.website.settings.edit');
        $this->actingAs($admin)->get(route('admin.website.settings.edit'))->assertOk();

        $this->assertDatabaseCount('website_settings', 1);
    }

    public function test_admin_can_update_global_branding_contact_seo_and_code(): void
    {
        $this->useTemporaryPublicPath();
        $admin = User::factory()->create();
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true
        );

        $response = $this->actingAs($admin)->put(route('admin.website.settings.update'), [
            'site_name' => 'Example CMS',
            'site_tagline' => 'A better website',
            'logo' => UploadedFile::fake()->createWithContent('logo.png', $png),
            'white_logo' => UploadedFile::fake()->createWithContent('white-logo.png', $png),
            'favicon' => UploadedFile::fake()->createWithContent('favicon.png', $png),
            'og_image' => UploadedFile::fake()->createWithContent('og-image.png', $png),
            'primary_color' => '#123abc',
            'secondary_color' => '#654321',
            'phone' => '+91 98765 43210',
            'email' => 'hello@example.com',
            'whatsapp' => '919876543210',
            'address' => 'Pune, India',
            'google_map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=example" loading="lazy"></iframe>',
            'facebook_url' => 'https://facebook.com/example',
            'instagram_url' => 'https://instagram.com/example',
            'linkedin_url' => 'https://linkedin.com/company/example',
            'youtube_url' => 'https://youtube.com/@example',
            'twitter_url' => 'https://x.com/example',
            'meta_title' => 'Example Meta Title',
            'meta_description' => 'Example global description.',
            'meta_keywords' => 'example, cms, website',
            'custom_css' => '.site-banner { color: #123abc; }',
            'custom_js' => 'window.exampleCmsLoaded = true;',
            'status' => '1',
        ]);

        $response->assertRedirect(route('admin.website.settings.edit'))->assertSessionHasNoErrors();
        $settings = WebsiteSetting::firstOrFail();

        $this->assertSame('A better website', $settings->site_tagline);
        $this->assertSame('919876543210', $settings->whatsapp);
        $this->assertTrue($settings->status);

        foreach (['logo', 'white_logo', 'favicon', 'og_image'] as $field) {
            $this->assertFileExists(public_path($settings->{$field}));
        }
    }

    public function test_expanded_settings_validation_rejects_unsafe_values(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->from(route('admin.website.settings.edit'))
            ->put(route('admin.website.settings.update'), [
                'primary_color' => 'red',
                'secondary_color' => '#12',
                'facebook_url' => 'javascript:alert(1)',
                'google_map_embed' => '<iframe src="https://example.com" onload="alert(1)"></iframe>',
                'custom_css' => '@import url(https://example.com/style.css);',
                'custom_js' => '<script>alert(1)</script>',
                'status' => '1',
            ])
            ->assertRedirect(route('admin.website.settings.edit'))
            ->assertSessionHasErrors([
                'primary_color',
                'secondary_color',
                'facebook_url',
                'google_map_embed',
                'custom_css',
                'custom_js',
            ]);
    }

    public function test_public_api_returns_only_active_safe_settings_without_internal_fields(): void
    {
        WebsiteSetting::create([
            'site_name' => 'Public CMS',
            'site_tagline' => 'Public tagline',
            'logo' => 'assets/images/settings/logo.png',
            'primary_color' => '#123456',
            'meta_title' => 'Public Meta',
            'custom_css' => 'body { letter-spacing: 0; }',
            'status' => true,
        ]);

        $response = $this->getJson(route('frontend.website-settings.show'))
            ->assertOk()
            ->assertJsonPath('site_name', 'Public CMS')
            ->assertJsonPath('site_tagline', 'Public tagline')
            ->assertJsonPath('primary_color', '#123456')
            ->assertJsonPath('meta_title', 'Public Meta')
            ->assertJsonPath('logo', asset('assets/images/settings/logo.png'));

        $this->assertArrayNotHasKey('id', $response->json());
        $this->assertArrayNotHasKey('status', $response->json());
        $this->assertArrayNotHasKey('created_at', $response->json());
    }

    public function test_public_api_hides_inactive_settings_and_shell_uses_seo_fallbacks(): void
    {
        WebsiteSetting::create(['site_name' => 'Hidden CMS', 'status' => false]);

        $this->getJson(route('frontend.website-settings.show'))
            ->assertOk()
            ->assertContent('{}');

        $this->get('/')
            ->assertOk()
            ->assertSee('<title>CMS Website</title>', false)
            ->assertSee('Professional website powered by Laravel and Vue');
    }

    private function useTemporaryPublicPath(): void
    {
        $publicPath = storage_path('framework/testing/public/'.Str::uuid());
        File::ensureDirectoryExists($publicPath);
        $this->app->usePublicPath($publicPath);
        $this->beforeApplicationDestroyed(fn () => File::deleteDirectory($publicPath));
    }
}
