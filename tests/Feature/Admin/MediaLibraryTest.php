<?php

namespace Tests\Feature\Admin;

use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_media_library_administration(): void
    {
        $this->get(route('admin.website.media-library.index'))->assertRedirect(route('login'));
        $this->get(route('admin.website.media-library.create'))->assertRedirect(route('login'));
        $this->post(route('admin.website.media-library.store'), [])->assertRedirect(route('login'));
    }

    public function test_admin_can_upload_view_update_toggle_and_soft_delete_media(): void
    {
        Storage::fake('media_library');
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.website.media-library.store'), [
            'title' => '<strong>Hero Image</strong>',
            'file' => $this->png('hero-image.png'),
            'alt_text' => '<em>Hero alt text</em>',
            'caption' => '<script>alert(1)</script>Useful caption',
            'status' => '1',
        ])->assertRedirect(route('admin.website.media-library.index'))
            ->assertSessionHasNoErrors();

        $mediaFile = MediaFile::firstOrFail();
        $this->assertSame('Hero Image', $mediaFile->title);
        $this->assertSame('Hero alt text', $mediaFile->alt_text);
        $this->assertSame('alert(1)Useful caption', $mediaFile->caption);
        $this->assertSame('image', $mediaFile->file_type);
        $this->assertTrue($mediaFile->status);
        $this->assertSame($admin->id, $mediaFile->uploaded_by);
        Storage::disk('media_library')->assertExists($mediaFile->file_path);

        $this->actingAs($admin)
            ->get(route('admin.website.media-library.show', $mediaFile))
            ->assertOk()
            ->assertSee('Hero Image')
            ->assertSee('Copy URL');

        $oldPath = $mediaFile->file_path;

        $this->actingAs($admin)->put(route('admin.website.media-library.update', $mediaFile), [
            'title' => 'Product Sheet',
            'file' => UploadedFile::fake()->create('product-sheet.pdf', 128, 'application/pdf'),
            'alt_text' => '',
            'caption' => 'Downloadable PDF',
            'status' => '1',
        ])->assertRedirect(route('admin.website.media-library.show', $mediaFile))
            ->assertSessionHasNoErrors();

        $mediaFile->refresh();
        $this->assertSame('Product Sheet', $mediaFile->title);
        $this->assertSame('document', $mediaFile->file_type);
        $this->assertSame('product-sheet.pdf', $mediaFile->original_name);
        Storage::disk('media_library')->assertMissing($oldPath);
        Storage::disk('media_library')->assertExists($mediaFile->file_path);

        $this->actingAs($admin)
            ->patch(route('admin.website.media-library.toggle-status', $mediaFile))
            ->assertSessionHasNoErrors();
        $this->assertFalse($mediaFile->refresh()->status);

        $path = $mediaFile->file_path;

        $this->actingAs($admin)
            ->delete(route('admin.website.media-library.destroy', $mediaFile))
            ->assertRedirect(route('admin.website.media-library.index'))
            ->assertSessionHasNoErrors();

        $this->assertSoftDeleted($mediaFile);
        Storage::disk('media_library')->assertExists($path);

        $mediaFile->forceDelete();
        Storage::disk('media_library')->assertMissing($path);
    }

    public function test_media_library_search_filter_pagination_and_table_view_work(): void
    {
        $admin = User::factory()->create();

        foreach (range(1, 13) as $number) {
            MediaFile::create([
                'title' => $number === 13 ? 'Special Contract' : "Media {$number}",
                'file_name' => "media-{$number}.jpg",
                'original_name' => "media-{$number}.jpg",
                'file_path' => "media-library/media-{$number}.jpg",
                'file_url' => "/storage/media-library/media-{$number}.jpg",
                'mime_type' => $number === 13 ? 'application/pdf' : 'image/jpeg',
                'file_type' => $number === 13 ? 'document' : 'image',
                'file_size' => 1024 * $number,
                'status' => $number !== 12,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.website.media-library.index'))
            ->assertOk()
            ->assertViewHas('mediaFiles', fn ($mediaFiles) => $mediaFiles->total() === 13 && $mediaFiles->lastPage() === 2);

        $this->actingAs($admin)
            ->get(route('admin.website.media-library.index', [
                'search' => 'Special',
                'file_type' => 'document',
                'view' => 'table',
            ]))
            ->assertOk()
            ->assertSee('Special Contract')
            ->assertViewHas('viewMode', 'table')
            ->assertViewHas('mediaFiles', fn ($mediaFiles) => $mediaFiles->total() === 1);

        $this->actingAs($admin)
            ->get(route('admin.website.media-library.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertViewHas('mediaFiles', fn ($mediaFiles) => $mediaFiles->total() === 1);
    }

    public function test_media_library_inputs_are_validated(): void
    {
        $admin = User::factory()->create();

        $unsafeSvg = UploadedFile::fake()->createWithContent(
            'unsafe.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'
        );

        $this->actingAs($admin)->post(route('admin.website.media-library.store'), [
            'title' => str_repeat('a', 256),
            'file' => $unsafeSvg,
            'alt_text' => str_repeat('b', 256),
            'caption' => str_repeat('c', 501),
        ])->assertSessionHasErrors(['title', 'file', 'alt_text', 'caption']);

        $this->actingAs($admin)->post(route('admin.website.media-library.store'), [
            'file' => UploadedFile::fake()->create('shell.php', 1, 'text/plain'),
        ])->assertSessionHasErrors('file');
    }

    private function png(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
        );
    }
}
