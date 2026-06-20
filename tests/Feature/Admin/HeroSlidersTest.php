<?php

namespace Tests\Feature\Admin;

use App\Models\HeroSlider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class HeroSlidersTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_hero_slider_management(): void
    {
        $this->get(route('admin.hero-sliders.index'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.hero-sliders.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_manage_hero_sliders_and_images(): void
    {
        $this->useTemporaryPublicPath();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.hero-sliders.store'), $this->validData([
                'image' => $this->fakeImage('first.png'),
            ]))
            ->assertRedirect(route('admin.hero-sliders.index'))
            ->assertSessionHas('success');

        $heroSlider = HeroSlider::firstOrFail();
        $oldImage = $heroSlider->image;

        $this->assertFileExists(public_path($oldImage));

        $this->actingAs($user)
            ->put(route('admin.hero-sliders.update', $heroSlider), $this->validData([
                'title' => 'Updated headline',
                'image' => $this->fakeImage('second.png'),
                'status' => '0',
            ]))
            ->assertRedirect(route('admin.hero-sliders.index'));

        $heroSlider->refresh();

        $this->assertSame('Updated headline', $heroSlider->title);
        $this->assertFalse($heroSlider->status);
        $this->assertFileDoesNotExist(public_path($oldImage));
        $this->assertFileExists(public_path($heroSlider->image));

        $currentImage = $heroSlider->image;

        $this->actingAs($user)
            ->delete(route('admin.hero-sliders.destroy', $heroSlider))
            ->assertRedirect(route('admin.hero-sliders.index'));

        $this->assertSoftDeleted($heroSlider);
        $this->assertFileDoesNotExist(public_path($currentImage));
    }

    public function test_public_endpoint_returns_only_active_sliders_in_sort_order(): void
    {
        HeroSlider::create($this->validData([
            'title' => 'Second slide',
            'sort_order' => 20,
        ]));
        HeroSlider::create($this->validData([
            'title' => 'Hidden slide',
            'status' => false,
            'sort_order' => 0,
        ]));
        HeroSlider::create($this->validData([
            'title' => 'First slide',
            'image' => 'assets/images/hero-sliders/first-slide.jpg',
            'sort_order' => 10,
        ]));

        $this->getJson(route('frontend.hero-sliders.index'))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'First slide')
            ->assertJsonPath('data.0.image_url', asset('assets/images/hero-sliders/first-slide.jpg'))
            ->assertJsonPath('data.1.title', 'Second slide');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Simple tools for growing businesses',
            'subtitle' => 'Work faster with a modern business platform.',
            'button_text' => 'Learn more',
            'button_url' => '/services',
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
