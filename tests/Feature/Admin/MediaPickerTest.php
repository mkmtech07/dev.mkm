<?php

namespace Tests\Feature\Admin;

use App\Models\Gallery;
use App\Models\MediaFile;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class MediaPickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_picker_api_requires_authentication_and_permission(): void
    {
        $this->get(route('admin.api.media-picker.index'))
            ->assertRedirect(route('login'));

        User::factory()->create(); // Permanent main super admin.
        $staff = User::factory()->create();

        $this->actingAs($staff)
            ->getJson(route('admin.api.media-picker.index'))
            ->assertForbidden();
    }

    public function test_media_picker_api_returns_only_active_safe_media_with_search_filter_and_pagination(): void
    {
        $admin = User::factory()->create();

        $hero = $this->media([
            'title' => 'Hero Banner',
            'file_name' => 'hero-banner.jpg',
            'original_name' => 'hero-banner.jpg',
            'file_path' => 'media-library/hero-banner.jpg',
        ]);
        $inactive = $this->media([
            'title' => 'Hero Inactive',
            'file_name' => 'hero-inactive.jpg',
            'original_name' => 'hero-inactive.jpg',
            'file_path' => 'media-library/hero-inactive.jpg',
            'status' => false,
        ]);
        $deleted = $this->media([
            'title' => 'Hero Deleted',
            'file_name' => 'hero-deleted.jpg',
            'original_name' => 'hero-deleted.jpg',
            'file_path' => 'media-library/hero-deleted.jpg',
        ]);
        $deleted->delete();
        $document = $this->media([
            'title' => 'Product Sheet',
            'file_name' => 'product-sheet.pdf',
            'original_name' => 'product-sheet.pdf',
            'file_path' => 'media-library/product-sheet.pdf',
            'mime_type' => 'application/pdf',
            'file_type' => 'document',
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.api.media-picker.index', [
                'accept_type' => 'image',
                'search' => 'Hero',
                'per_page' => 1,
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $hero->id)
            ->assertJsonPath('data.0.path', 'storage/media-library/hero-banner.jpg')
            ->assertJsonMissing(['id' => $inactive->id])
            ->assertJsonMissing(['id' => $deleted->id])
            ->assertJsonMissing(['id' => $document->id])
            ->assertJsonStructure([
                'data' => [[
                    'id', 'title', 'original_name', 'file_type', 'mime_type',
                    'file_size', 'formatted_size', 'url', 'path', 'is_image',
                ]],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        $this->actingAs($admin)
            ->getJson(route('admin.api.media-picker.index', [
                'accept_type' => 'any',
                'file_type' => 'document',
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $document->id)
            ->assertJsonPath('data.0.file_type', 'document');

        $this->actingAs($admin)
            ->getJson(route('admin.api.media-picker.show', $inactive))
            ->assertNotFound();
    }

    public function test_gallery_can_create_an_image_from_selected_media(): void
    {
        $admin = User::factory()->create();
        $media = $this->media([
            'title' => 'Gallery Pick',
            'file_name' => 'gallery-pick.jpg',
            'original_name' => 'gallery-pick.jpg',
            'file_path' => 'media-library/gallery-pick.jpg',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.gallery.store'), [
                'title' => 'Selected gallery image',
                'category' => 'Projects',
                'alt_text' => 'Selected image alt text',
                'image_media_id' => $media->id,
                'image_media_action' => 'select',
                'status' => '1',
                'sort_order' => 10,
            ])
            ->assertRedirect(route('admin.gallery.index'))
            ->assertSessionHasNoErrors();

        $gallery = Gallery::firstOrFail();
        $this->assertSame('storage/media-library/gallery-pick.jpg', $gallery->image);
    }

    public function test_upload_takes_priority_over_selected_media(): void
    {
        $this->useTemporaryPublicPath();
        $admin = User::factory()->create();
        $media = $this->media([
            'file_name' => 'reused-service.jpg',
            'original_name' => 'reused-service.jpg',
            'file_path' => 'media-library/reused-service.jpg',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.services.store'), [
                'title' => 'Billing Solutions',
                'slug' => 'billing-solutions',
                'short_description' => 'Fast, accurate invoicing.',
                'description' => 'A complete billing solution.',
                'icon' => '01',
                'image' => $this->fakeImage('fresh-upload.png'),
                'image_media_id' => $media->id,
                'image_media_action' => 'select',
                'status' => '1',
                'sort_order' => 10,
            ])
            ->assertRedirect(route('admin.services.index'))
            ->assertSessionHasNoErrors();

        $service = Service::firstOrFail();
        $this->assertStringStartsWith('assets/images/services/', $service->image);
        $this->assertNotSame('storage/media-library/reused-service.jpg', $service->image);
        $this->assertFileExists(public_path($service->image));
    }

    public function test_module_edit_permission_alone_cannot_select_media(): void
    {
        User::factory()->create(); // Permanent main super admin.
        $staff = User::factory()->create();
        $permission = Permission::create([
            'name' => 'Gallery Create',
            'slug' => 'gallery.create',
            'module' => 'Gallery',
            'status' => true,
        ]);
        $role = Role::create([
            'name' => 'Gallery Creator',
            'slug' => 'gallery-creator',
            'status' => true,
        ]);
        $role->permissions()->attach($permission);
        $staff->roles()->attach($role);
        $media = $this->media();

        $this->actingAs($staff)
            ->post(route('admin.gallery.store'), [
                'title' => 'Forbidden selected image',
                'category' => 'Projects',
                'image_media_id' => $media->id,
                'image_media_action' => 'select',
                'status' => '1',
                'sort_order' => 10,
            ])
            ->assertForbidden();
    }

    /** @param array<string, mixed> $overrides */
    private function media(array $overrides = []): MediaFile
    {
        return MediaFile::create(array_merge([
            'title' => 'Selected Media',
            'file_name' => 'selected-media.jpg',
            'original_name' => 'selected-media.jpg',
            'file_path' => 'media-library/selected-media.jpg',
            'file_url' => '/storage/media-library/selected-media.jpg',
            'mime_type' => 'image/jpeg',
            'file_type' => 'image',
            'file_size' => 2048,
            'status' => true,
        ], $overrides));
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
