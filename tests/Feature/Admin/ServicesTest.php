<?php

namespace Tests\Feature\Admin;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class ServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_service_management(): void
    {
        $this->get(route('admin.services.index'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.services.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_manage_services_and_images(): void
    {
        $this->useTemporaryPublicPath();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.services.store'), $this->validData([
                'slug' => '',
                'image' => $this->fakeImage('first.png'),
            ]))
            ->assertRedirect(route('admin.services.index'))
            ->assertSessionHas('success');

        $service = Service::firstOrFail();
        $oldImage = $service->image;

        $this->assertSame('billing-solutions', $service->slug);
        $this->assertStringStartsWith('assets/images/services/', $oldImage);
        $this->assertFileExists(public_path($oldImage));

        $this->actingAs($user)
            ->put(route('admin.services.update', $service), $this->validData([
                'title' => 'Updated billing solutions',
                'slug' => $service->slug,
                'image' => $this->fakeImage('second.png'),
                'status' => '0',
            ]))
            ->assertRedirect(route('admin.services.index'));

        $service->refresh();

        $this->assertSame('Updated billing solutions', $service->title);
        $this->assertFalse($service->status);
        $this->assertFileDoesNotExist(public_path($oldImage));
        $this->assertFileExists(public_path($service->image));

        $currentImage = $service->image;

        $this->actingAs($user)
            ->delete(route('admin.services.destroy', $service))
            ->assertRedirect(route('admin.services.index'));

        $this->assertSoftDeleted($service);
        $this->assertFileDoesNotExist(public_path($currentImage));
    }

    public function test_service_list_can_be_searched(): void
    {
        $user = User::factory()->create();
        Service::create($this->validData(['title' => 'Billing Solutions', 'slug' => 'billing-solutions']));
        Service::create($this->validData(['title' => 'Inventory Reports', 'slug' => 'inventory-reports']));

        $this->actingAs($user)
            ->get(route('admin.services.index', ['search' => 'Billing']))
            ->assertOk()
            ->assertSee('Billing Solutions')
            ->assertDontSee('Inventory Reports');
    }

    public function test_public_endpoint_returns_only_active_services_in_sort_order(): void
    {
        Service::create($this->validData([
            'title' => 'Second service',
            'slug' => 'second-service',
            'sort_order' => 20,
        ]));
        Service::create($this->validData([
            'title' => 'Hidden service',
            'slug' => 'hidden-service',
            'status' => false,
            'sort_order' => 0,
        ]));
        Service::create($this->validData([
            'title' => 'First service',
            'slug' => 'first-service',
            'image' => 'assets/images/services/first-service.jpg',
            'sort_order' => 10,
        ]));

        $this->getJson(route('frontend.services.index'))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'First service')
            ->assertJsonPath('data.0.image_url', asset('assets/images/services/first-service.jpg'))
            ->assertJsonPath('data.1.title', 'Second service');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Billing Solutions',
            'slug' => 'billing-solutions',
            'short_description' => 'Fast, accurate invoicing for everyday operations.',
            'description' => 'A complete billing solution for growing businesses.',
            'icon' => '01',
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
