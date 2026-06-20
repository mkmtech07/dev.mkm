<?php

namespace Tests\Feature\Admin;

use App\Models\Gallery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_gallery_management(): void
    {
        $this->get(route('admin.gallery.index'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.gallery.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_manage_gallery_images(): void
    {
        $this->useTemporaryPublicPath();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.gallery.store'), $this->validData([
                'image' => $this->fakeImage('first.png'),
            ]))
            ->assertRedirect(route('admin.gallery.index'))
            ->assertSessionHas('success');

        $gallery = Gallery::firstOrFail();
        $oldImage = $gallery->image;

        $this->assertStringStartsWith('assets/images/gallery/', $oldImage);
        $this->assertFileExists(public_path($oldImage));

        $this->actingAs($user)
            ->patch(route('admin.gallery.toggle-status', $gallery))
            ->assertSessionHas('success');

        $this->assertFalse($gallery->fresh()->status);

        $this->actingAs($user)
            ->put(route('admin.gallery.update', $gallery), $this->validData([
                'title' => 'Updated retail workspace',
                'image' => $this->fakeImage('updated.png'),
            ]))
            ->assertRedirect(route('admin.gallery.index'));

        $gallery->refresh();

        $this->assertSame('Updated retail workspace', $gallery->title);
        $this->assertFileDoesNotExist(public_path($oldImage));
        $this->assertFileExists(public_path($gallery->image));

        $currentImage = $gallery->image;

        $this->actingAs($user)
            ->delete(route('admin.gallery.destroy', $gallery))
            ->assertRedirect(route('admin.gallery.index'));

        $this->assertSoftDeleted($gallery);
        $this->assertFileDoesNotExist(public_path($currentImage));
    }

    public function test_gallery_can_be_searched_filtered_and_paginated(): void
    {
        $user = User::factory()->create();

        Gallery::create($this->validData([
            'title' => 'Retail workspace',
            'category' => 'Retail',
            'image' => 'assets/images/gallery/retail.jpg',
        ]));
        Gallery::create($this->validData([
            'title' => 'Office workspace',
            'category' => 'Office',
            'image' => 'assets/images/gallery/office.jpg',
        ]));

        for ($index = 1; $index <= 11; $index++) {
            Gallery::create($this->validData([
                'title' => "Project {$index}",
                'category' => 'Projects',
                'image' => "assets/images/gallery/project-{$index}.jpg",
            ]));
        }

        $this->actingAs($user)
            ->get(route('admin.gallery.index', [
                'search' => 'Retail',
                'category' => 'Retail',
            ]))
            ->assertOk()
            ->assertSee('Retail workspace')
            ->assertDontSee('Office workspace');

        $this->actingAs($user)
            ->get(route('admin.gallery.index'))
            ->assertOk()
            ->assertViewHas('galleries', fn ($galleries) => $galleries->count() === 12 && $galleries->hasPages());
    }

    public function test_public_endpoint_returns_only_active_gallery_images_in_sort_order(): void
    {
        Gallery::create($this->validData([
            'title' => 'Second image',
            'image' => 'assets/images/gallery/second.jpg',
            'sort_order' => 20,
        ]));
        Gallery::create($this->validData([
            'title' => 'Hidden image',
            'image' => 'assets/images/gallery/hidden.jpg',
            'status' => false,
            'sort_order' => 0,
        ]));
        Gallery::create($this->validData([
            'title' => 'First image',
            'image' => 'assets/images/gallery/first.jpg',
            'sort_order' => 10,
        ]));

        $this->getJson(route('frontend.gallery.index'))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'First image')
            ->assertJsonPath('data.0.image_url', asset('assets/images/gallery/first.jpg'))
            ->assertJsonPath('data.1.title', 'Second image');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Retail workspace',
            'category' => 'Retail',
            'alt_text' => 'A modern retail workspace',
            'status' => true,
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
