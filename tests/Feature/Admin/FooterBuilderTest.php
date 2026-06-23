<?php

namespace Tests\Feature\Admin;

use App\Models\FooterLink;
use App\Models\FooterSection;
use App\Models\FooterSetting;
use App\Models\FooterSocialLink;
use App\Models\User;
use App\Support\PublicImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FooterBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_footer_administration(): void
    {
        $this->get(route('admin.website.footer.settings.edit'))->assertRedirect(route('login'));
        $this->get(route('admin.website.footer.sections.index'))->assertRedirect(route('login'));
        $this->get(route('admin.website.footer.links.index'))->assertRedirect(route('login'));
        $this->get(route('admin.website.footer.social.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_update_footer_settings_and_logo(): void
    {
        $admin = User::factory()->create();
        $logoPath = null;

        try {
            $this->actingAs($admin)
                ->put(route('admin.website.footer.settings.update'), [
                    'footer_logo' => UploadedFile::fake()->createWithContent(
                        'footer.png',
                        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
                    ),
                    'footer_description' => 'A useful footer description.',
                    'phone' => '+91 98765 43210',
                    'email' => 'hello@example.com',
                    'whatsapp' => '919876543210',
                    'address' => 'Pune, India',
                    'copyright_text' => 'Example Company. All rights reserved.',
                    'newsletter_status' => '1',
                    'status' => '1',
                ])
                ->assertSessionHasNoErrors();

            $settings = FooterSetting::firstOrFail();
            $logoPath = $settings->footer_logo;
            $this->assertNotNull($logoPath);
            $this->assertFileExists(public_path($logoPath));
            $this->assertTrue($settings->newsletter_status);

            $this->actingAs($admin)
                ->get(route('admin.website.footer.settings.edit'))
                ->assertOk()
                ->assertSee('A useful footer description.');
        } finally {
            PublicImage::delete($logoPath);
        }
    }

    public function test_admin_can_manage_footer_sections_links_and_social_links(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.website.footer.sections.store'), [
            'title' => 'Quick Links',
            'type' => 'links',
            'content' => 'Useful destinations',
            'sort_order' => 2,
            'status' => '1',
        ])->assertSessionHasNoErrors();
        $section = FooterSection::firstOrFail();

        $this->actingAs($admin)->post(route('admin.website.footer.links.store'), [
            'footer_section_id' => $section->id,
            'title' => 'About',
            'url' => '/about',
            'target' => '_self',
            'sort_order' => 1,
            'status' => '1',
        ])->assertSessionHasNoErrors();
        $link = FooterLink::firstOrFail();

        $this->actingAs($admin)->post(route('admin.website.footer.social.store'), [
            'platform' => 'LinkedIn',
            'url' => 'https://linkedin.com/company/example',
            'icon' => 'bi bi-linkedin',
            'target' => '_blank',
            'sort_order' => 1,
            'status' => '1',
        ])->assertSessionHasNoErrors();
        $social = FooterSocialLink::firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.website.footer.sections.edit', $section))
            ->assertOk();
        $this->actingAs($admin)
            ->get(route('admin.website.footer.links.edit', $link))
            ->assertOk();
        $this->actingAs($admin)
            ->get(route('admin.website.footer.social.edit', $social))
            ->assertOk();

        $this->actingAs($admin)
            ->patch(route('admin.website.footer.sections.toggle-status', $section))
            ->assertSessionHasNoErrors();
        $this->actingAs($admin)
            ->patch(route('admin.website.footer.links.toggle-status', $link))
            ->assertSessionHasNoErrors();
        $this->actingAs($admin)
            ->patch(route('admin.website.footer.social.toggle-status', $social))
            ->assertSessionHasNoErrors();

        $this->assertFalse($section->refresh()->status);
        $this->assertFalse($link->refresh()->status);
        $this->assertFalse($social->refresh()->status);

        $this->actingAs($admin)->delete(route('admin.website.footer.links.destroy', $link));
        $this->actingAs($admin)->delete(route('admin.website.footer.social.destroy', $social));
        $this->actingAs($admin)->delete(route('admin.website.footer.sections.destroy', $section));

        $this->assertSoftDeleted($link);
        $this->assertSoftDeleted($social);
        $this->assertSoftDeleted($section);
    }

    public function test_footer_inputs_are_validated(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.website.footer.sections.store'), [
            'title' => '', 'type' => 'invalid', 'sort_order' => -1,
        ])->assertSessionHasErrors(['title', 'type', 'sort_order']);

        $this->actingAs($admin)->post(route('admin.website.footer.links.store'), [
            'title' => 'Unsafe', 'url' => 'javascript:alert(1)', 'target' => 'popup',
            'sort_order' => -1,
        ])->assertSessionHasErrors(['url', 'target', 'sort_order']);

        $this->actingAs($admin)->post(route('admin.website.footer.social.store'), [
            'platform' => 'Unsafe', 'url' => 'ftp://example.com', 'target' => '_blank',
            'sort_order' => 0,
        ])->assertSessionHasErrors('url');
    }

    public function test_footer_lists_support_search_and_pagination(): void
    {
        $admin = User::factory()->create();
        foreach (range(1, 11) as $number) {
            FooterSection::create([
                'title' => $number === 11 ? 'Special Footer' : "Section {$number}",
                'type' => 'custom',
                'sort_order' => $number,
            ]);
        }
        $section = FooterSection::firstOrFail();
        foreach (range(1, 11) as $number) {
            FooterLink::create([
                'footer_section_id' => $section->id,
                'title' => "Footer Link {$number}",
                'url' => "/footer-link-{$number}",
                'sort_order' => $number,
            ]);
            FooterSocialLink::create([
                'platform' => "Social {$number}",
                'url' => "https://example.com/social-{$number}",
                'sort_order' => $number,
            ]);
        }

        $this->actingAs($admin)->get(route('admin.website.footer.sections.index'))
            ->assertOk()
            ->assertViewHas('sections', fn ($sections) => $sections->total() === 11 && $sections->lastPage() === 2);
        $this->actingAs($admin)->get(route('admin.website.footer.sections.index', ['search' => 'Special']))
            ->assertOk()->assertSee('Special Footer')
            ->assertViewHas('sections', fn ($sections) => $sections->total() === 1);
        $this->actingAs($admin)->get(route('admin.website.footer.links.index'))
            ->assertOk()
            ->assertViewHas('links', fn ($links) => $links->total() === 11 && $links->lastPage() === 2);
        $this->actingAs($admin)->get(route('admin.website.footer.social.index'))
            ->assertOk()
            ->assertViewHas('socialLinks', fn ($links) => $links->total() === 11 && $links->lastPage() === 2);
    }

    public function test_footer_api_returns_only_active_nested_data_without_ids(): void
    {
        FooterSetting::create([
            'footer_description' => 'Public footer',
            'email' => 'public@example.com',
            'newsletter_status' => true,
            'status' => true,
        ]);
        $section = FooterSection::create([
            'title' => 'Quick Links', 'type' => 'links', 'status' => true, 'sort_order' => 1,
        ]);
        FooterSection::create([
            'title' => 'Hidden Section', 'type' => 'custom', 'status' => false, 'sort_order' => 2,
        ]);
        $section->links()->create([
            'title' => 'Home', 'url' => '/', 'status' => true, 'sort_order' => 1,
        ]);
        $section->links()->create([
            'title' => 'Hidden Link', 'url' => '/hidden', 'status' => false, 'sort_order' => 2,
        ]);
        FooterSocialLink::create([
            'platform' => 'Facebook', 'url' => 'https://facebook.com/example',
            'status' => true, 'sort_order' => 1,
        ]);
        FooterSocialLink::create([
            'platform' => 'Hidden Social', 'url' => 'https://example.com/hidden',
            'status' => false, 'sort_order' => 2,
        ]);

        $response = $this->getJson(route('frontend.footer.show'))
            ->assertOk()
            ->assertJsonPath('settings.footer_description', 'Public footer')
            ->assertJsonPath('sections.0.title', 'Quick Links')
            ->assertJsonPath('sections.0.links.0.title', 'Home')
            ->assertJsonPath('social_links.0.platform', 'Facebook')
            ->assertJsonCount(1, 'sections')
            ->assertJsonCount(1, 'sections.0.links')
            ->assertJsonCount(1, 'social_links');

        $this->assertArrayNotHasKey('id', $response->json('sections.0'));
        $this->assertArrayNotHasKey('id', $response->json('sections.0.links.0'));
    }

    public function test_footer_api_returns_no_dynamic_data_without_active_settings(): void
    {
        FooterSetting::create(['status' => false]);
        FooterSection::create(['title' => 'Links', 'type' => 'links', 'status' => true]);

        $this->getJson(route('frontend.footer.show'))
            ->assertOk()
            ->assertJsonPath('settings', null)
            ->assertJsonCount(0, 'sections')
            ->assertJsonCount(0, 'social_links');
    }
}
