<?php

namespace Tests\Feature\Admin;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class BlogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_blog_management(): void
    {
        $this->get(route('admin.blogs.index'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.blogs.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_manage_blog_posts_and_images(): void
    {
        $this->useTemporaryPublicPath();
        $user = User::factory()->create();
        $category = $this->createCategory();

        $this->actingAs($user)
            ->post(route('admin.blogs.store'), $this->validData([
                'blog_category_id' => $category->id,
                'slug' => '',
                'featured_image' => $this->fakeImage('featured.png'),
                'og_image' => $this->fakeImage('social.png'),
            ]))
            ->assertRedirect(route('admin.blogs.index'))
            ->assertSessionHas('success');

        $blog = Blog::firstOrFail();
        $oldFeaturedImage = $blog->featured_image;
        $oldOgImage = $blog->og_image;

        $this->assertSame('simpler-business-reporting', $blog->slug);
        $this->assertSame($category->id, $blog->blog_category_id);
        $this->assertStringStartsWith('assets/images/blogs/', $oldFeaturedImage);
        $this->assertStringStartsWith('assets/images/blogs/', $oldOgImage);
        $this->assertFileExists(public_path($oldFeaturedImage));
        $this->assertFileExists(public_path($oldOgImage));

        $this->actingAs($user)
            ->patch(route('admin.blogs.toggle-status', $blog))
            ->assertSessionHas('success');
        $this->actingAs($user)
            ->patch(route('admin.blogs.toggle-featured', $blog))
            ->assertSessionHas('success');

        $blog->refresh();
        $this->assertFalse($blog->status);
        $this->assertTrue($blog->is_featured);

        $this->actingAs($user)
            ->put(route('admin.blogs.update', $blog), $this->validData([
                'blog_category_id' => $category->id,
                'title' => 'Updated Reporting Guide',
                'slug' => 'updated-reporting-guide',
                'featured_image' => $this->fakeImage('updated-featured.png'),
                'og_image' => $this->fakeImage('updated-social.png'),
            ]))
            ->assertRedirect(route('admin.blogs.index'));

        $blog->refresh();

        $this->assertSame('Updated Reporting Guide', $blog->title);
        $this->assertFileDoesNotExist(public_path($oldFeaturedImage));
        $this->assertFileDoesNotExist(public_path($oldOgImage));
        $this->assertFileExists(public_path($blog->featured_image));
        $this->assertFileExists(public_path($blog->og_image));

        $currentImages = [$blog->featured_image, $blog->og_image];

        $this->actingAs($user)
            ->delete(route('admin.blogs.destroy', $blog))
            ->assertRedirect(route('admin.blogs.index'));

        $this->assertSoftDeleted($blog);
        foreach ($currentImages as $image) {
            $this->assertFileDoesNotExist(public_path($image));
        }
    }

    public function test_blog_validation_enforces_slug_category_url_and_image_rules(): void
    {
        $user = User::factory()->create();
        Blog::create($this->validData(['slug' => 'simpler-business-reporting']));

        $this->actingAs($user)
            ->from(route('admin.blogs.create'))
            ->post(route('admin.blogs.store'), $this->validData([
                'slug' => 'simpler-business-reporting',
                'blog_category_id' => 999999,
                'canonical_url' => 'not-a-url',
                'featured_image' => UploadedFile::fake()->create('document.pdf', 10, 'application/pdf'),
            ]))
            ->assertRedirect(route('admin.blogs.create'))
            ->assertSessionHasErrors(['slug', 'blog_category_id', 'canonical_url', 'featured_image']);
    }

    public function test_blog_posts_can_be_searched_filtered_and_paginated(): void
    {
        $user = User::factory()->create();
        $guides = $this->createCategory(['name' => 'Guides', 'slug' => 'guides']);
        $news = $this->createCategory(['name' => 'News', 'slug' => 'news']);

        Blog::create($this->validData([
            'blog_category_id' => $guides->id,
            'title' => 'Inventory Guide',
            'slug' => 'inventory-guide',
        ]));

        for ($index = 1; $index <= 10; $index++) {
            Blog::create($this->validData([
                'blog_category_id' => $news->id,
                'title' => "News Post {$index}",
                'slug' => "news-post-{$index}",
            ]));
        }

        $this->actingAs($user)
            ->get(route('admin.blogs.index', ['search' => 'Inventory']))
            ->assertOk()
            ->assertSee('Inventory Guide')
            ->assertDontSee('News Post 1');

        $this->actingAs($user)
            ->get(route('admin.blogs.index', ['category' => $guides->id]))
            ->assertOk()
            ->assertSee('Inventory Guide')
            ->assertDontSee('News Post 1');

        $this->actingAs($user)
            ->get(route('admin.blogs.index'))
            ->assertOk()
            ->assertViewHas('blogs', fn ($blogs) => $blogs->count() === 10 && $blogs->hasPages());
    }

    public function test_blog_scopes_filter_featured_active_posts_and_order_them(): void
    {
        Blog::create($this->validData([
            'title' => 'Older Featured',
            'slug' => 'older-featured',
            'publish_at' => now()->subDays(2),
            'is_featured' => true,
        ]));
        Blog::create($this->validData([
            'title' => 'Hidden Featured',
            'slug' => 'hidden-featured',
            'publish_at' => now(),
            'is_featured' => true,
            'status' => false,
        ]));
        Blog::create($this->validData([
            'title' => 'Newer Featured',
            'slug' => 'newer-featured',
            'publish_at' => now()->subDay(),
            'is_featured' => true,
        ]));

        $this->assertSame(
            ['Newer Featured', 'Older Featured'],
            Blog::query()->active()->featured()->ordered()->pluck('title')->all()
        );
    }

    public function test_public_blog_index_returns_only_active_published_posts_with_filters(): void
    {
        $guides = $this->createCategory(['name' => 'Guides', 'slug' => 'guides']);
        $news = $this->createCategory(['name' => 'News', 'slug' => 'news']);

        Blog::create($this->validData([
            'blog_category_id' => $guides->id,
            'title' => 'Published Guide',
            'slug' => 'published-guide',
            'publish_at' => now()->subDay(),
            'is_featured' => true,
        ]));
        Blog::create($this->validData([
            'blog_category_id' => $news->id,
            'title' => 'News Article',
            'slug' => 'news-article',
            'publish_at' => now()->subDays(2),
        ]));
        Blog::create($this->validData([
            'title' => 'Future Article',
            'slug' => 'future-article',
            'publish_at' => now()->addDay(),
        ]));
        Blog::create($this->validData([
            'title' => 'Inactive Article',
            'slug' => 'inactive-article',
            'status' => false,
        ]));

        $this->getJson(route('frontend.blogs.index', ['category' => 'guides', 'search' => 'Published']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'featured')
            ->assertJsonPath('data.0.title', 'Published Guide')
            ->assertJsonPath('data.0.category.slug', 'guides')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonCount(2, 'categories');
    }

    public function test_public_blog_details_increment_views_return_related_posts_and_hide_unpublished_posts(): void
    {
        $category = $this->createCategory();
        $blog = Blog::create($this->validData([
            'blog_category_id' => $category->id,
            'slug' => 'main-article',
            'views' => 7,
            'featured_image' => 'assets/images/blogs/main.jpg',
            'og_image' => 'assets/images/blogs/main-og.jpg',
        ]));
        Blog::create($this->validData([
            'blog_category_id' => $category->id,
            'title' => 'Related Article',
            'slug' => 'related-article',
        ]));
        Blog::create($this->validData([
            'title' => 'Scheduled Article',
            'slug' => 'scheduled-article',
            'publish_at' => now()->addDay(),
        ]));

        $this->getJson(route('frontend.blogs.show', ['slug' => 'main-article']))
            ->assertOk()
            ->assertJsonPath('data.slug', 'main-article')
            ->assertJsonPath('data.views', 8)
            ->assertJsonPath('data.featured_image', asset('assets/images/blogs/main.jpg'))
            ->assertJsonPath('data.og_image', asset('assets/images/blogs/main-og.jpg'))
            ->assertJsonPath('related.0.slug', 'related-article');

        $this->assertSame(8, $blog->fresh()->views);

        $this->getJson(route('frontend.blogs.show', ['slug' => 'scheduled-article']))
            ->assertNotFound();
        $this->getJson(route('frontend.blogs.show', ['slug' => 'missing-article']))
            ->assertNotFound();
    }

    public function test_vue_blog_routes_mount_the_frontend_application(): void
    {
        $this->get('/blog')
            ->assertOk()
            ->assertSee('frontend-app');

        $this->get('/blog/example-article')
            ->assertOk()
            ->assertSee('frontend-app');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'blog_category_id' => null,
            'title' => 'Simpler Business Reporting',
            'slug' => 'simpler-business-reporting',
            'excerpt' => 'A practical approach to useful business reports.',
            'content' => '<h2>Start with the goal</h2><p>Useful reports answer clear questions.</p>',
            'author' => 'Billsoft Editorial',
            'publish_at' => now()->subHour(),
            'is_featured' => false,
            'status' => true,
            'views' => 0,
            'meta_title' => 'Simpler Business Reporting Guide',
            'meta_description' => 'Learn how to build simpler and more useful business reports.',
            'canonical_url' => 'https://example.com/blog/simpler-business-reporting',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createCategory(array $overrides = []): BlogCategory
    {
        return BlogCategory::create(array_merge([
            'name' => 'Business Tips',
            'slug' => 'business-tips',
            'description' => 'Practical business guidance.',
            'status' => true,
            'sort_order' => 10,
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
