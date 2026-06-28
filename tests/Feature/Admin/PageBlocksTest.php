<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\PageBlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class PageBlocksTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_page_block_administration(): void
    {
        $this->get(route('admin.website.page-blocks.index'))->assertRedirect(route('login'));
        $this->get(route('admin.website.page-blocks.create'))->assertRedirect(route('login'));
        $this->post(route('admin.website.page-blocks.store'), [])->assertRedirect(route('login'));
    }

    public function test_admin_can_create_update_toggle_show_and_soft_delete_a_page_block_with_images(): void
    {
        $this->withoutVite();
        $this->useTemporaryPublicPath();
        $admin = User::factory()->create();
        $page = Page::create($this->pageData(['title' => 'Landing Page', 'slug' => 'landing-page']));

        $this->actingAs($admin)->post(route('admin.website.page-blocks.store'), [
            'page_id' => $page->id,
            'title' => 'Hero headline',
            'subtitle' => 'Welcome',
            'block_key' => 'landing-hero',
            'type' => 'hero',
            'content' => 'A strong landing page introduction.',
            'image' => $this->fakeImage('hero.png'),
            'background_image' => $this->fakeImage('background.webp'),
            'button_text' => 'Contact us',
            'button_url' => '/contact',
            'secondary_button_text' => 'Read more',
            'secondary_button_url' => '#details',
            'background_color' => '#102030',
            'text_color' => '#ffffff',
            'settings' => '{"image_position":"right"}',
            'status' => '1',
            'sort_order' => 2,
        ])->assertRedirect(route('admin.website.page-blocks.index', ['page_id' => $page->id]))
            ->assertSessionHasNoErrors();

        $block = PageBlock::firstOrFail();
        $oldImage = $block->image;
        $this->assertSame(['image_position' => 'right'], $block->settings);
        $this->assertTrue($block->status);
        $this->assertStringStartsWith('assets/images/page-blocks/', $block->image);
        $this->assertFileExists(public_path($block->image));
        $this->assertFileExists(public_path($block->background_image));

        $this->actingAs($admin)
            ->get(route('admin.website.page-blocks.show', $block))
            ->assertOk()
            ->assertSee('Hero headline');

        $this->actingAs($admin)->put(route('admin.website.page-blocks.update', $block), [
            'page_id' => $page->id,
            'title' => 'Updated hero',
            'block_key' => 'landing-hero',
            'type' => 'hero',
            'button_url' => 'https://example.com/contact',
            'image' => $this->fakeImage('updated.png'),
            'status' => '1',
            'sort_order' => 1,
            'settings' => '{}',
        ])->assertRedirect(route('admin.website.page-blocks.index', ['page_id' => $page->id]))
            ->assertSessionHasNoErrors();

        $block->refresh();
        $this->assertSame('Updated hero', $block->title);
        $this->assertFileDoesNotExist(public_path($oldImage));
        $this->assertFileExists(public_path($block->image));

        $this->actingAs($admin)
            ->patch(route('admin.website.page-blocks.toggle-status', $block))
            ->assertSessionHasNoErrors();
        $this->assertFalse($block->refresh()->status);

        $currentImages = [$block->image, $block->background_image];
        $this->actingAs($admin)
            ->delete(route('admin.website.page-blocks.destroy', $block))
            ->assertRedirect(route('admin.website.page-blocks.index', ['page_id' => $page->id]))
            ->assertSessionHasNoErrors();

        $this->assertSoftDeleted($block);
        foreach ($currentImages as $image) {
            $this->assertFileDoesNotExist(public_path($image));
        }
    }

    public function test_page_block_inputs_are_validated(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.website.page-blocks.store'), [
            'page_id' => 999,
            'block_key' => 'not a safe key',
            'type' => 'custom_html',
            'content' => '<script>alert(1)</script>',
            'button_url' => 'javascript:alert(1)',
            'secondary_button_url' => '//example.com',
            'background_color' => 'red',
            'text_color' => 'not-a-color',
            'sort_order' => -1,
            'settings' => '{invalid json}',
            'image' => UploadedFile::fake()->create('image.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors([
            'page_id',
            'block_key',
            'content',
            'button_url',
            'secondary_button_url',
            'background_color',
            'text_color',
            'sort_order',
            'settings',
            'image',
        ]);
    }

    public function test_index_supports_search_page_type_filter_and_pagination(): void
    {
        $admin = User::factory()->create();
        $page = Page::create($this->pageData(['title' => 'Main Landing', 'slug' => 'main-landing']));

        foreach (range(1, 11) as $number) {
            PageBlock::create([
                'page_id' => $page->id,
                'title' => $number === 11 ? 'Special Pricing Block' : "Block {$number}",
                'type' => $number === 11 ? 'pricing' : 'text',
                'status' => true,
                'sort_order' => $number,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.website.page-blocks.index'))
            ->assertOk()
            ->assertViewHas('blocks', fn ($blocks) => $blocks->total() === 11 && $blocks->lastPage() === 2);

        $this->actingAs($admin)
            ->get(route('admin.website.page-blocks.index', [
                'search' => 'Special',
                'type' => 'pricing',
                'page_id' => $page->id,
            ]))
            ->assertOk()
            ->assertSee('Special Pricing Block')
            ->assertViewHas('blocks', fn ($blocks) => $blocks->total() === 1);
    }

    public function test_public_api_returns_only_active_ordered_frontend_safe_blocks(): void
    {
        $page = Page::create($this->pageData(['title' => 'Sales Page', 'slug' => 'sales']));

        PageBlock::create([
            'page_id' => $page->id,
            'title' => 'Second',
            'type' => 'custom_html',
            'content' => '<div onclick="alert(1)"><script>alert(1)</script><a href="javascript:alert(1)">Unsafe</a><strong>Safe</strong></div>',
            'status' => true,
            'sort_order' => 2,
            'settings' => ['items' => [['title' => 'Fast']]],
        ]);
        PageBlock::create([
            'page_id' => $page->id,
            'title' => 'First',
            'type' => 'hero',
            'image' => 'assets/images/page-blocks/hero.jpg',
            'status' => true,
            'sort_order' => 1,
        ]);
        PageBlock::create([
            'page_id' => $page->id,
            'title' => 'Hidden',
            'type' => 'cta',
            'status' => false,
            'sort_order' => 0,
        ]);
        $deleted = PageBlock::create([
            'page_id' => $page->id,
            'title' => 'Deleted',
            'type' => 'text',
            'status' => true,
            'sort_order' => 0,
        ]);
        $deleted->delete();

        $response = $this->getJson(route('frontend.pages.blocks.index', ['slug' => 'sales']))
            ->assertOk()
            ->assertJsonCount(2, 'blocks')
            ->assertJsonPath('blocks.0.title', 'First')
            ->assertJsonPath('blocks.0.image', asset('assets/images/page-blocks/hero.jpg'))
            ->assertJsonPath('blocks.1.title', 'Second')
            ->assertJsonPath('blocks.1.settings.items.0.title', 'Fast');

        $this->assertStringNotContainsString('<script', $response->json('blocks.1.content'));
        $this->assertStringNotContainsString('onclick', $response->json('blocks.1.content'));
        $this->assertStringNotContainsString('javascript:', $response->json('blocks.1.content'));
        $this->assertArrayNotHasKey('id', $response->json('blocks.0'));
        $this->assertArrayNotHasKey('status', $response->json('blocks.0'));
        $this->assertArrayNotHasKey('sort_order', $response->json('blocks.0'));
        $this->assertArrayNotHasKey('deleted_at', $response->json('blocks.0'));
    }

    public function test_public_api_returns_empty_blocks_and_existing_page_content_still_loads(): void
    {
        Page::create($this->pageData([
            'title' => 'Plain Page',
            'slug' => 'plain',
            'content' => '<h2>Fallback content</h2>',
        ]));

        $this->getJson(route('frontend.pages.blocks.index', ['slug' => 'plain']))
            ->assertOk()
            ->assertExactJson(['blocks' => []]);

        $this->getJson(route('frontend.pages.show', ['slug' => 'plain']))
            ->assertOk()
            ->assertJsonPath('data.content', '<h2>Fallback content</h2>');
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function pageData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'meta_title' => 'Privacy Policy',
            'meta_description' => 'How we collect and use information.',
            'featured_image' => null,
            'content' => '<h2>Your privacy</h2><p>We respect your privacy.</p>',
            'page_type' => 'default',
            'template' => 'default',
            'status' => true,
            'show_in_menu' => false,
            'sort_order' => 10,
        ], $overrides);
    }

    private function fakeImage(string $name): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true
        );

        return UploadedFile::fake()->createWithContent($name, $png);
    }

    private function useTemporaryPublicPath(): void
    {
        $publicPath = storage_path('framework/testing/public/'.Str::uuid());

        File::ensureDirectoryExists($publicPath);
        $this->app->usePublicPath($publicPath);
        $this->beforeApplicationDestroyed(fn () => File::deleteDirectory($publicPath));
    }
}
