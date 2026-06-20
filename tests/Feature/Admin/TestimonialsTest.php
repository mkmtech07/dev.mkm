<?php

namespace Tests\Feature\Admin;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class TestimonialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_testimonial_management(): void
    {
        $this->get(route('admin.testimonials.index'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.testimonials.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_manage_testimonials_and_images(): void
    {
        $this->useTemporaryPublicPath();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.testimonials.store'), $this->validData([
                'image' => $this->fakeImage('client.png'),
            ]))
            ->assertRedirect(route('admin.testimonials.index'))
            ->assertSessionHas('success');

        $testimonial = Testimonial::firstOrFail();
        $oldImage = $testimonial->image;

        $this->assertStringStartsWith('assets/images/testimonials/', $oldImage);
        $this->assertFileExists(public_path($oldImage));

        $this->actingAs($user)
            ->patch(route('admin.testimonials.toggle-status', $testimonial))
            ->assertSessionHas('success');

        $this->assertFalse($testimonial->fresh()->status);

        $this->actingAs($user)
            ->put(route('admin.testimonials.update', $testimonial), $this->validData([
                'client_name' => 'Updated Client',
                'image' => $this->fakeImage('updated.png'),
            ]))
            ->assertRedirect(route('admin.testimonials.index'));

        $testimonial->refresh();

        $this->assertSame('Updated Client', $testimonial->client_name);
        $this->assertFileDoesNotExist(public_path($oldImage));
        $this->assertFileExists(public_path($testimonial->image));

        $currentImage = $testimonial->image;

        $this->actingAs($user)
            ->delete(route('admin.testimonials.destroy', $testimonial))
            ->assertRedirect(route('admin.testimonials.index'));

        $this->assertSoftDeleted($testimonial);
        $this->assertFileDoesNotExist(public_path($currentImage));
    }

    public function test_testimonials_can_be_searched_and_paginated(): void
    {
        $user = User::factory()->create();

        Testimonial::create($this->validData(['client_name' => 'Aarav Mehta']));

        for ($index = 1; $index <= 10; $index++) {
            Testimonial::create($this->validData([
                'client_name' => "Client {$index}",
                'company' => "Company {$index}",
            ]));
        }

        $this->actingAs($user)
            ->get(route('admin.testimonials.index', ['search' => 'Aarav']))
            ->assertOk()
            ->assertSee('Aarav Mehta')
            ->assertDontSee('Client 1');

        $this->actingAs($user)
            ->get(route('admin.testimonials.index'))
            ->assertOk()
            ->assertViewHas('testimonials', fn ($testimonials) => $testimonials->count() === 10 && $testimonials->hasPages());
    }

    public function test_testimonial_validation_rejects_invalid_rating(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.testimonials.create'))
            ->post(route('admin.testimonials.store'), $this->validData(['rating' => 6]))
            ->assertRedirect(route('admin.testimonials.create'))
            ->assertSessionHasErrors('rating');
    }

    public function test_public_endpoint_returns_only_active_testimonials_in_featured_order(): void
    {
        Testimonial::create($this->validData([
            'client_name' => 'Regular first by sort',
            'featured' => false,
            'sort_order' => 0,
        ]));
        Testimonial::create($this->validData([
            'client_name' => 'Featured second',
            'featured' => true,
            'sort_order' => 20,
        ]));
        Testimonial::create($this->validData([
            'client_name' => 'Hidden featured',
            'featured' => true,
            'status' => false,
            'sort_order' => 0,
        ]));
        Testimonial::create($this->validData([
            'client_name' => 'Featured first',
            'featured' => true,
            'image' => 'assets/images/testimonials/featured.jpg',
            'sort_order' => 10,
        ]));

        $this->getJson(route('frontend.testimonials.index'))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.client_name', 'Featured first')
            ->assertJsonPath('data.0.image_url', asset('assets/images/testimonials/featured.jpg'))
            ->assertJsonPath('data.1.client_name', 'Featured second')
            ->assertJsonPath('data.2.client_name', 'Regular first by sort');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'client_name' => 'Aarav Mehta',
            'company' => 'Mehta Retail',
            'designation' => 'Owner',
            'review' => 'Billsoft made our daily billing process faster and easier.',
            'rating' => 5,
            'status' => true,
            'featured' => false,
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
