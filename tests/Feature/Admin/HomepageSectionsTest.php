<?php

namespace Tests\Feature\Admin;

use App\Models\HomepageSection;
use App\Models\User;
use App\Support\PublicImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class HomepageSectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_homepage_section_administration(): void
    {
        $this->get(route('admin.website.homepage-sections.index'))->assertRedirect(route('login'));
        $this->get(route('admin.website.homepage-sections.create'))->assertRedirect(route('login'));
        $this->post(route('admin.website.homepage-sections.store'), [])->assertRedirect(route('login'));
    }

    public function test_admin_can_create_update_toggle_and_soft_delete_a_section_with_images(): void
    {
        $admin = User::factory()->create();
        $paths = [];

        try {
            $this->actingAs($admin)->post(route('admin.website.homepage-sections.store'), [
                'title' => 'Welcome home',
                'subtitle' => 'Built for growing teams',
                'section_key' => 'main-hero',
                'type' => 'hero',
                'content' => 'A useful introduction.',
                'button_text' => 'Contact us',
                'button_url' => '/contact',
                'image' => $this->image('section.png'),
                'background_image' => $this->image('background.webp'),
                'background_color' => '#102030',
                'text_color' => '#ffffff',
                'status' => '1',
                'sort_order' => 2,
                'settings' => '{"image_position":"right"}',
            ])->assertRedirect(route('admin.website.homepage-sections.index'))
                ->assertSessionHasNoErrors();

            $section = HomepageSection::firstOrFail();
            $paths = [$section->image, $section->background_image];
            $this->assertSame(['image_position' => 'right'], $section->settings);
            $this->assertTrue($section->status);
            $this->assertFileExists(public_path($section->image));
            $this->assertFileExists(public_path($section->background_image));

            $this->actingAs($admin)->put(route('admin.website.homepage-sections.update', $section), [
                'title' => 'Updated welcome',
                'section_key' => 'main-hero',
                'type' => 'hero',
                'button_url' => 'https://example.com/contact',
                'status' => '1',
                'sort_order' => 1,
                'settings' => '{}',
            ])->assertRedirect(route('admin.website.homepage-sections.index'))
                ->assertSessionHasNoErrors();

            $this->assertSame('Updated welcome', $section->refresh()->title);

            $this->actingAs($admin)
                ->patch(route('admin.website.homepage-sections.toggle-status', $section))
                ->assertSessionHasNoErrors();
            $this->assertFalse($section->refresh()->status);

            $this->actingAs($admin)
                ->delete(route('admin.website.homepage-sections.destroy', $section))
                ->assertSessionHasNoErrors();
            $this->assertSoftDeleted($section);
        } finally {
            foreach ($paths as $path) {
                PublicImage::delete($path);
            }
        }
    }

    public function test_homepage_section_inputs_are_validated(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.website.homepage-sections.store'), [
            'section_key' => 'not a safe key',
            'type' => 'unsupported',
            'button_url' => 'javascript:alert(1)',
            'background_color' => 'red;display:none',
            'text_color' => 'not-a-color',
            'sort_order' => -1,
            'settings' => '{invalid json}',
            'image' => UploadedFile::fake()->create('image.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors([
            'section_key',
            'type',
            'button_url',
            'background_color',
            'text_color',
            'sort_order',
            'settings',
            'image',
        ]);
    }

    public function test_index_supports_search_type_filter_and_pagination(): void
    {
        $admin = User::factory()->create();

        foreach (range(1, 11) as $number) {
            HomepageSection::create([
                'title' => $number === 11 ? 'Special Homepage Block' : "Section {$number}",
                'type' => $number === 11 ? 'cta' : 'custom',
                'sort_order' => $number,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.website.homepage-sections.index'))
            ->assertOk()
            ->assertViewHas('sections', fn ($sections) => $sections->total() === 11 && $sections->lastPage() === 2);

        $this->actingAs($admin)
            ->get(route('admin.website.homepage-sections.index', ['search' => 'Special', 'type' => 'cta']))
            ->assertOk()
            ->assertSee('Special Homepage Block')
            ->assertViewHas('sections', fn ($sections) => $sections->total() === 1);
    }

    public function test_public_api_returns_only_active_ordered_frontend_safe_sections(): void
    {
        HomepageSection::create([
            'title' => 'Second',
            'type' => 'custom',
            'content' => '<strong>Safe content</strong>',
            'status' => true,
            'sort_order' => 2,
            'settings' => ['layout' => 'wide'],
        ]);
        HomepageSection::create([
            'title' => 'First',
            'type' => 'hero',
            'status' => true,
            'sort_order' => 1,
        ]);
        HomepageSection::create([
            'title' => 'Hidden',
            'type' => 'cta',
            'status' => false,
            'sort_order' => 0,
        ]);
        $deleted = HomepageSection::create([
            'title' => 'Deleted',
            'type' => 'about',
            'status' => true,
            'sort_order' => 0,
        ]);
        $deleted->delete();

        $response = $this->getJson(route('frontend.homepage-sections.index'))
            ->assertOk()
            ->assertJsonCount(2, 'sections')
            ->assertJsonPath('sections.0.title', 'First')
            ->assertJsonPath('sections.1.title', 'Second')
            ->assertJsonPath('sections.1.content', 'Safe content')
            ->assertJsonPath('sections.1.settings.layout', 'wide');

        $this->assertArrayNotHasKey('id', $response->json('sections.0'));
        $this->assertArrayNotHasKey('status', $response->json('sections.0'));
        $this->assertArrayNotHasKey('sort_order', $response->json('sections.0'));
        $this->assertArrayNotHasKey('deleted_at', $response->json('sections.0'));
    }

    public function test_public_api_returns_an_empty_collection_for_the_static_homepage_fallback(): void
    {
        $this->getJson(route('frontend.homepage-sections.index'))
            ->assertOk()
            ->assertExactJson(['sections' => []]);
    }

    private function image(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
        );
    }
}
