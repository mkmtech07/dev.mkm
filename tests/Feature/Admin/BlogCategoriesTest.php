<?php

namespace Tests\Feature\Admin;

use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_blog_category_management(): void
    {
        $this->get(route('admin.blog-categories.index'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.blog-categories.store'), $this->validData())
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_manage_delete_and_restore_categories(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.blog-categories.store'), $this->validData(['slug' => '']))
            ->assertRedirect(route('admin.blog-categories.index'))
            ->assertSessionHas('success');

        $category = BlogCategory::firstOrFail();

        $this->assertSame('business-tips', $category->slug);

        $this->actingAs($user)
            ->patch(route('admin.blog-categories.toggle-status', $category))
            ->assertSessionHas('success');

        $this->assertFalse($category->fresh()->status);

        $this->actingAs($user)
            ->put(route('admin.blog-categories.update', $category), $this->validData([
                'name' => 'Growth Strategies',
                'slug' => 'growth-strategies',
                'sort_order' => 5,
            ]))
            ->assertRedirect(route('admin.blog-categories.index'));

        $category->refresh();

        $this->assertSame('Growth Strategies', $category->name);
        $this->assertSame(5, $category->sort_order);

        $this->actingAs($user)
            ->delete(route('admin.blog-categories.destroy', $category))
            ->assertRedirect(route('admin.blog-categories.index'));

        $this->assertSoftDeleted($category);

        $this->actingAs($user)
            ->patch(route('admin.blog-categories.restore', $category))
            ->assertRedirect(route('admin.blog-categories.index', ['view' => 'trashed']))
            ->assertSessionHas('success');

        $this->assertNotSoftDeleted($category);
    }

    public function test_category_validation_enforces_unique_slug_and_valid_sort_order(): void
    {
        $user = User::factory()->create();
        BlogCategory::create($this->validData(['slug' => 'business-tips']));

        $this->actingAs($user)
            ->from(route('admin.blog-categories.create'))
            ->post(route('admin.blog-categories.store'), $this->validData([
                'name' => 'Another Category',
                'slug' => 'business-tips',
                'sort_order' => -1,
            ]))
            ->assertRedirect(route('admin.blog-categories.create'))
            ->assertSessionHasErrors(['slug', 'sort_order']);
    }

    public function test_categories_can_be_searched_filtered_and_paginated(): void
    {
        $user = User::factory()->create();
        BlogCategory::create($this->validData([
            'name' => 'Accounting Guides',
            'slug' => 'accounting-guides',
            'status' => false,
        ]));

        for ($index = 1; $index <= 10; $index++) {
            BlogCategory::create($this->validData([
                'name' => "Category {$index}",
                'slug' => "category-{$index}",
                'sort_order' => $index,
            ]));
        }

        $this->actingAs($user)
            ->get(route('admin.blog-categories.index', ['search' => 'Accounting']))
            ->assertOk()
            ->assertSee('Accounting Guides')
            ->assertDontSee('Category 1');

        $this->actingAs($user)
            ->get(route('admin.blog-categories.index', ['view' => 'inactive']))
            ->assertOk()
            ->assertSee('Accounting Guides')
            ->assertDontSee('Category 1');

        $this->actingAs($user)
            ->get(route('admin.blog-categories.index'))
            ->assertOk()
            ->assertViewHas('categories', fn ($categories) => $categories->count() === 10 && $categories->hasPages());
    }

    public function test_authenticated_users_can_bulk_delete_selected_categories(): void
    {
        $user = User::factory()->create();
        $first = BlogCategory::create($this->validData(['name' => 'First', 'slug' => 'first']));
        $second = BlogCategory::create($this->validData(['name' => 'Second', 'slug' => 'second']));
        $untouched = BlogCategory::create($this->validData(['name' => 'Untouched', 'slug' => 'untouched']));

        $this->actingAs($user)
            ->delete(route('admin.blog-categories.bulk-delete'), [
                'categories' => [$first->id, $second->id],
            ])
            ->assertSessionHas('success');

        $this->assertSoftDeleted($first);
        $this->assertSoftDeleted($second);
        $this->assertNotSoftDeleted($untouched);
    }

    public function test_bulk_delete_requires_at_least_one_valid_category(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.blog-categories.index'))
            ->delete(route('admin.blog-categories.bulk-delete'), ['categories' => []])
            ->assertRedirect(route('admin.blog-categories.index'))
            ->assertSessionHasErrors('categories');
    }

    public function test_active_and_ordered_scopes_filter_and_sort_categories(): void
    {
        BlogCategory::create($this->validData([
            'name' => 'Second',
            'slug' => 'second',
            'sort_order' => 20,
        ]));
        BlogCategory::create($this->validData([
            'name' => 'Hidden',
            'slug' => 'hidden',
            'status' => false,
            'sort_order' => 0,
        ]));
        BlogCategory::create($this->validData([
            'name' => 'First',
            'slug' => 'first',
            'sort_order' => 10,
        ]));

        $this->assertSame(
            ['First', 'Second'],
            BlogCategory::query()->active()->ordered()->pluck('name')->all()
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Business Tips',
            'slug' => 'business-tips',
            'description' => 'Practical advice for growing businesses.',
            'meta_title' => 'Business Tips and Guides',
            'meta_description' => 'Read practical business tips and guides.',
            'status' => true,
            'sort_order' => 10,
        ], $overrides);
    }
}
