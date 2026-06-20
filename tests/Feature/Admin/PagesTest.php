<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_page_management(): void
    {
        $this->get(route('admin.pages.index'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.pages.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_manage_pages_and_featured_images(): void
    {
        $this->useTemporaryPublicPath();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.pages.store'), $this->validData([
                'slug' => '',
                'featured_image' => $this->fakeImage('page.png'),
            ]))
            ->assertRedirect(route('admin.pages.index'))
            ->assertSessionHas('success');

        $page = Page::firstOrFail();
        $oldImage = $page->featured_image;

        $this->assertSame('privacy-policy', $page->slug);
        $this->assertStringStartsWith('assets/images/pages/', $oldImage);
        $this->assertFileExists(public_path($oldImage));

        $this->actingAs($user)
            ->patch(route('admin.pages.toggle-status', $page))
            ->assertSessionHas('success');

        $this->assertFalse($page->fresh()->status);

        $this->actingAs($user)
            ->put(route('admin.pages.update', $page), $this->validData([
                'title' => 'Updated Policy',
                'slug' => 'updated-policy',
                'featured_image' => $this->fakeImage('updated.png'),
            ]))
            ->assertRedirect(route('admin.pages.index'));

        $page->refresh();

        $this->assertSame('Updated Policy', $page->title);
        $this->assertFileDoesNotExist(public_path($oldImage));
        $this->assertFileExists(public_path($page->featured_image));

        $currentImage = $page->featured_image;

        $this->actingAs($user)
            ->delete(route('admin.pages.destroy', $page))
            ->assertRedirect(route('admin.pages.index'));

        $this->assertSoftDeleted($page);
        $this->assertFileDoesNotExist(public_path($currentImage));
    }

    public function test_page_slug_must_be_unique_and_page_type_is_validated(): void
    {
        $user = User::factory()->create();
        Page::create($this->validData(['slug' => 'privacy-policy']));

        $this->actingAs($user)
            ->from(route('admin.pages.create'))
            ->post(route('admin.pages.store'), $this->validData([
                'title' => 'Another Policy',
                'slug' => 'privacy-policy',
                'page_type' => 'invalid',
            ]))
            ->assertRedirect(route('admin.pages.create'))
            ->assertSessionHasErrors(['slug', 'page_type']);
    }

    public function test_pages_can_be_searched_and_paginated(): void
    {
        $user = User::factory()->create();
        Page::create($this->validData(['title' => 'Refund Policy', 'slug' => 'refund-policy']));

        for ($index = 1; $index <= 10; $index++) {
            Page::create($this->validData([
                'title' => "Content Page {$index}",
                'slug' => "content-page-{$index}",
            ]));
        }

        $this->actingAs($user)
            ->get(route('admin.pages.index', ['search' => 'Refund']))
            ->assertOk()
            ->assertSee('Refund Policy')
            ->assertDontSee('Content Page 1');

        $this->actingAs($user)
            ->get(route('admin.pages.index'))
            ->assertOk()
            ->assertViewHas('pages', fn ($pages) => $pages->count() === 10 && $pages->hasPages());
    }

    public function test_public_endpoint_returns_an_active_page_by_slug(): void
    {
        Page::create($this->validData([
            'title' => 'Terms and Conditions',
            'slug' => 'terms',
            'meta_title' => 'Website Terms',
            'meta_description' => 'Read our website terms.',
            'featured_image' => 'assets/images/pages/terms.jpg',
            'content' => '<h2>Using our service</h2><p>These terms apply.</p>',
        ]));

        $this->getJson(route('frontend.pages.show', ['slug' => 'terms']))
            ->assertOk()
            ->assertJsonPath('data.title', 'Terms and Conditions')
            ->assertJsonPath('data.meta_title', 'Website Terms')
            ->assertJsonPath('data.featured_image', asset('assets/images/pages/terms.jpg'))
            ->assertJsonPath('data.content', '<h2>Using our service</h2><p>These terms apply.</p>');
    }

    public function test_public_endpoint_returns_404_for_missing_or_inactive_pages(): void
    {
        Page::create($this->validData([
            'slug' => 'hidden-page',
            'status' => false,
        ]));

        $this->getJson(route('frontend.pages.show', ['slug' => 'hidden-page']))
            ->assertNotFound()
            ->assertJsonPath('message', 'Page not found.');

        $this->getJson(route('frontend.pages.show', ['slug' => 'does-not-exist']))
            ->assertNotFound();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'meta_title' => 'Privacy Policy',
            'meta_description' => 'How we collect and use information.',
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
