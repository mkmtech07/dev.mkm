<?php

namespace Tests\Feature\Admin;

use App\Models\AboutSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class AboutSectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_about_section_management(): void
    {
        $this->get(route('admin.about.index'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.about.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_manage_about_sections_and_only_one_is_active(): void
    {
        $this->useTemporaryPublicPath();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.about.store'), $this->validData([
                'title' => 'First About section',
                'image' => $this->fakeImage('first.png'),
            ]))
            ->assertRedirect(route('admin.about.index'))
            ->assertSessionHas('success');

        $first = AboutSection::firstOrFail();
        $oldImage = $first->image;

        $this->assertStringStartsWith('assets/images/about/', $oldImage);
        $this->assertFileExists(public_path($oldImage));

        $this->actingAs($user)
            ->post(route('admin.about.store'), $this->validData([
                'title' => 'Second About section',
            ]))
            ->assertRedirect(route('admin.about.index'));

        $second = AboutSection::query()->where('title', 'Second About section')->firstOrFail();

        $this->assertFalse($first->fresh()->status);
        $this->assertTrue($second->status);

        $this->actingAs($user)
            ->put(route('admin.about.update', $first), $this->validData([
                'title' => 'Updated About section',
                'image' => $this->fakeImage('updated.png'),
            ]))
            ->assertRedirect(route('admin.about.index'));

        $first->refresh();
        $second->refresh();

        $this->assertTrue($first->status);
        $this->assertFalse($second->status);
        $this->assertFileDoesNotExist(public_path($oldImage));
        $this->assertFileExists(public_path($first->image));

        $currentImage = $first->image;

        $this->actingAs($user)
            ->delete(route('admin.about.destroy', $first))
            ->assertRedirect(route('admin.about.index'));

        $this->assertDatabaseMissing('about_sections', ['id' => $first->id]);
        $this->assertFileDoesNotExist(public_path($currentImage));
    }

    public function test_public_endpoint_returns_the_active_about_section(): void
    {
        AboutSection::create($this->validData([
            'title' => 'Inactive About section',
            'status' => false,
        ]));
        AboutSection::create($this->validData([
            'title' => 'Active About section',
            'image' => 'assets/images/about/about.jpg',
        ]));

        $this->getJson(route('frontend.about-section.show'))
            ->assertOk()
            ->assertJsonPath('data.title', 'Active About section')
            ->assertJsonPath('data.image_url', asset('assets/images/about/about.jpg'))
            ->assertJsonPath('data.projects_completed', 250);
    }

    public function test_public_endpoint_returns_null_when_no_active_section_exists(): void
    {
        AboutSection::create($this->validData(['status' => false]));

        $this->getJson(route('frontend.about-section.show'))
            ->assertOk()
            ->assertJsonPath('data', null);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Technology with a practical purpose',
            'subtitle' => 'Tools built around the way real teams work.',
            'description' => 'We build practical business software for growing teams.',
            'mission' => 'Make useful technology simple and dependable.',
            'vision' => 'Help every business use technology confidently.',
            'years_of_experience' => 10,
            'projects_completed' => 250,
            'clients_served' => 180,
            'team_members' => 24,
            'status' => true,
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
