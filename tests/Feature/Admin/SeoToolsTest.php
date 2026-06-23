<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\SchemaMarkup;
use App\Models\SeoPage;
use App\Models\SeoSetting;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seo_admin_routes_are_protected_and_crud_works(): void
    {
        $this->get(route('admin.website.seo.pages.index'))->assertRedirect(route('login'));
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.website.seo.pages.store'), $this->seoData())
            ->assertRedirect(route('admin.website.seo.pages.index'));
        $seoPage = SeoPage::firstOrFail();

        $this->actingAs($admin)->get(route('admin.website.seo.pages.edit', $seoPage))
            ->assertOk()->assertSee('About SEO');
        $this->actingAs($admin)->put(route('admin.website.seo.pages.update', $seoPage), $this->seoData([
            'meta_title' => 'Updated About SEO',
        ]))->assertRedirect(route('admin.website.seo.pages.index'));
        $this->assertSame('Updated About SEO', $seoPage->refresh()->meta_title);

        $this->actingAs($admin)->delete(route('admin.website.seo.pages.destroy', $seoPage));
        $this->assertSoftDeleted($seoPage);
    }

    public function test_settings_and_schema_admin_pages_work(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin)->get(route('admin.website.seo.settings.edit'))->assertOk();
        $this->actingAs($admin)->put(route('admin.website.seo.settings.update'), [
            ...SeoSetting::defaults(), 'sitemap_cache_minutes' => 30, 'robots_content' => 'User-agent: *',
        ])->assertRedirect(route('admin.website.seo.settings.edit'));
        $this->assertDatabaseHas('seo_settings', ['sitemap_cache_minutes' => 30]);

        $this->actingAs($admin)->post(route('admin.website.seo.schema.store'), [
            'name' => 'Website schema', 'type' => 'Website',
            'schema_json' => '{"@context":"https://schema.org","@type":"WebSite"}',
            'status' => true, 'sort_order' => 1,
        ])->assertRedirect(route('admin.website.seo.schema.index'));
        $this->assertDatabaseHas('schema_markups', ['name' => 'Website schema']);
    }

    public function test_public_seo_uses_page_override_then_global_fallback(): void
    {
        WebsiteSetting::create([
            'site_name' => 'Billsoft', 'meta_title' => 'Global SEO',
            'meta_description' => 'Global description', 'status' => true,
        ]);
        SeoSetting::create(SeoSetting::defaults());

        $this->getJson(route('seo.show', ['path' => '/contact']))
            ->assertOk()->assertJsonPath('meta_title', 'Global SEO');

        SeoPage::create($this->seoData());
        $this->getJson(route('seo.show', ['path' => '/about']))
            ->assertOk()
            ->assertJsonPath('meta_title', 'About SEO')
            ->assertJsonPath('robots_index', true)
            ->assertJsonMissingPath('id');
    }

    public function test_sitemap_robots_and_schema_endpoints_return_safe_content(): void
    {
        SeoSetting::create(SeoSetting::defaults());
        $page = Page::create([
            'title' => 'Public Page', 'slug' => 'public-page', 'content' => 'Page body',
            'page_type' => 'default', 'template' => 'default', 'status' => true,
            'show_in_menu' => false, 'sort_order' => 0,
        ]);
        SeoPage::create($this->seoData([
            'page_type' => 'page', 'page_key' => null, 'related_id' => $page->id,
            'route_path' => '/public-page', 'robots_index' => false,
        ]));
        SchemaMarkup::create([
            'name' => 'Organization', 'type' => 'Organization',
            'schema_json' => '{"@context":"https://schema.org","@type":"Organization"}',
            'status' => true, 'sort_order' => 0,
        ]);

        $this->get(route('seo.sitemap'))->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertDontSee('/public-page');
        $this->get(route('seo.robots'))->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /admin')->assertSee('sitemap.xml');
        $this->getJson(route('seo.schema'))->assertOk()
            ->assertJsonPath('data.0.type', 'Organization')
            ->assertJsonMissingPath('data.0.id');
    }

    /** @return array<string, mixed> */
    private function seoData(array $overrides = []): array
    {
        return array_merge([
            'page_key' => 'about', 'page_type' => 'static', 'route_path' => '/about',
            'title' => 'About page', 'meta_title' => 'About SEO',
            'meta_description' => 'About page description', 'robots_index' => true,
            'robots_follow' => true, 'priority' => 0.8, 'change_frequency' => 'weekly', 'status' => true,
        ], $overrides);
    }
}
