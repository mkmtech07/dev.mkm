<?php

namespace Tests\Feature\Admin;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Menu;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenusTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_menu_management(): void
    {
        $menu = Menu::create(['name' => 'Header', 'location' => 'header']);
        $item = $menu->items()->create([
            'title' => 'Home',
            'type' => 'custom_url',
            'url' => '/',
        ]);

        $this->get(route('admin.menus.index'))->assertRedirect(route('login'));
        $this->get(route('admin.menus.items.index', $menu))->assertRedirect(route('login'));
        $this->delete(route('admin.menus.items.destroy', [$menu, $item]))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_manage_menus_and_items(): void
    {
        $admin = User::factory()->create();
        $page = Page::create([
            'title' => 'About Company',
            'slug' => 'about-company',
            'content' => 'About content',
            'status' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.menus.store'), [
                'name' => 'Main Navigation',
                'location' => 'header',
                'status' => '1',
            ])
            ->assertSessionHasNoErrors();

        $menu = Menu::where('name', 'Main Navigation')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.menus.items.store', $menu), [
                'title' => 'About',
                'type' => 'page',
                'page_id' => $page->id,
                'target' => '_self',
                'sort_order' => 2,
                'status' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.menus.items.index', $menu));

        $item = $menu->items()->firstOrFail();
        $this->actingAs($admin)
            ->get(route('admin.menus.items.edit', [$menu, $item]))
            ->assertOk()
            ->assertSee('About Company');

        $this->actingAs($admin)
            ->put(route('admin.menus.items.update', [$menu, $item]), [
                'title' => 'Contact us',
                'type' => 'custom_url',
                'url' => '/contact',
                'target' => '_blank',
                'sort_order' => 1,
                'status' => '1',
            ])
            ->assertSessionHasNoErrors();

        $item->refresh();
        $this->assertSame('custom_url', $item->type);
        $this->assertSame('/contact', $item->url);
        $this->assertNull($item->page_id);

        $this->actingAs($admin)
            ->patch(route('admin.menus.items.toggle-status', [$menu, $item]))
            ->assertSessionHasNoErrors();
        $this->assertFalse($item->refresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.menus.toggle-status', $menu))
            ->assertSessionHasNoErrors();
        $this->assertFalse($menu->refresh()->status);

        $this->actingAs($admin)
            ->delete(route('admin.menus.destroy', $menu))
            ->assertRedirect(route('admin.menus.index'));

        $this->assertSoftDeleted($menu);
        $this->assertSoftDeleted($item);
    }

    public function test_menu_item_validation_enforces_types_safe_urls_and_parent_tree(): void
    {
        $admin = User::factory()->create();
        $menu = Menu::create(['name' => 'Header', 'location' => 'header']);
        $otherMenu = Menu::create(['name' => 'Footer', 'location' => 'footer']);
        $otherItem = $otherMenu->items()->create([
            'title' => 'Other',
            'type' => 'custom_url',
            'url' => '/',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.menus.items.store', $menu), [
                'title' => 'Unsafe',
                'type' => 'custom_url',
                'url' => 'javascript:alert(1)',
                'parent_id' => $otherItem->id,
                'target' => '_self',
                'sort_order' => 0,
                'status' => '1',
            ])
            ->assertSessionHasErrors(['url', 'parent_id']);

        $root = $menu->items()->create([
            'title' => 'Root',
            'type' => 'custom_url',
            'url' => '/',
        ]);
        $child = $menu->items()->create([
            'parent_id' => $root->id,
            'title' => 'Child',
            'type' => 'custom_url',
            'url' => '/child',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.menus.items.update', [$menu, $root]), [
                'title' => 'Root',
                'type' => 'custom_url',
                'url' => '/',
                'parent_id' => $child->id,
                'target' => '_self',
                'sort_order' => 0,
                'status' => '1',
            ])
            ->assertSessionHasErrors('parent_id');

        $this->actingAs($admin)
            ->get(route('admin.menus.items.edit', [$otherMenu, $root]))
            ->assertNotFound();
    }

    public function test_menus_and_items_support_search_and_pagination(): void
    {
        $admin = User::factory()->create();

        foreach (range(1, 12) as $number) {
            Menu::create([
                'name' => $number === 12 ? 'Special Navigation' : "Menu {$number}",
                'location' => 'header',
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.menus.index'))
            ->assertOk()
            ->assertViewHas('menus', fn ($menus) => $menus->total() === 12 && $menus->lastPage() === 2);

        $this->actingAs($admin)
            ->get(route('admin.menus.index', ['search' => 'Special']))
            ->assertOk()
            ->assertSee('Special Navigation')
            ->assertViewHas('menus', fn ($menus) => $menus->total() === 1);

        $menu = Menu::firstOrFail();
        foreach (range(1, 16) as $number) {
            $menu->items()->create([
                'title' => "Link {$number}",
                'type' => 'custom_url',
                'url' => "/link-{$number}",
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.menus.items.index', $menu))
            ->assertOk()
            ->assertViewHas('menuItems', fn ($items) => $items->total() === 16 && $items->lastPage() === 2);
    }

    public function test_header_api_returns_only_active_nested_items_and_destinations(): void
    {
        $category = BlogCategory::create([
            'name' => 'Updates',
            'slug' => 'updates',
            'status' => true,
        ]);
        $page = Page::create([
            'title' => 'Company',
            'slug' => 'company',
            'content' => 'Company content',
            'status' => true,
        ]);
        $blog = Blog::create([
            'title' => 'Launch',
            'slug' => 'launch',
            'content' => 'Launch content',
            'status' => true,
            'publish_at' => now()->subDay(),
        ]);
        $menu = Menu::create([
            'name' => 'Main Header',
            'location' => 'header',
            'status' => true,
        ]);
        $parent = $menu->items()->create([
            'title' => 'Company',
            'type' => 'page',
            'page_id' => $page->id,
            'sort_order' => 1,
        ]);
        $menu->items()->create([
            'parent_id' => $parent->id,
            'title' => 'Updates',
            'type' => 'blog_category',
            'blog_category_id' => $category->id,
            'sort_order' => 1,
        ]);
        $menu->items()->create([
            'title' => 'Launch article',
            'type' => 'blog',
            'blog_id' => $blog->id,
            'target' => '_blank',
            'sort_order' => 2,
        ]);
        $menu->items()->create([
            'title' => 'Hidden',
            'type' => 'custom_url',
            'url' => '/hidden',
            'status' => false,
            'sort_order' => 3,
        ]);
        Menu::create([
            'name' => 'Disabled Header',
            'location' => 'header',
            'status' => false,
        ]);

        $this->getJson(route('frontend.menus.header'))
            ->assertOk()
            ->assertJsonPath('data.name', 'Main Header')
            ->assertJsonPath('data.items.0.title', 'Company')
            ->assertJsonPath('data.items.0.url', '/company')
            ->assertJsonPath('data.items.0.children.0.url', '/blog?category=updates')
            ->assertJsonPath('data.items.1.url', '/blog/launch')
            ->assertJsonPath('data.items.1.target', '_blank')
            ->assertJsonCount(2, 'data.items');
    }

    public function test_header_api_returns_null_when_no_active_header_menu_exists(): void
    {
        Menu::create([
            'name' => 'Footer',
            'location' => 'footer',
            'status' => true,
        ]);

        $this->getJson(route('frontend.menus.header'))
            ->assertOk()
            ->assertJsonPath('data', null);
    }
}
