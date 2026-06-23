<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MenuItemRequest;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Services\MenuCacheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function __construct(private readonly MenuCacheService $menuCache) {}

    public function index(Request $request, Menu $menu): View
    {
        return $this->itemsView($request, $menu, new MenuItem([
            'target' => '_self',
            'status' => true,
            'sort_order' => 0,
            'type' => 'custom_url',
        ]));
    }

    public function store(MenuItemRequest $request, Menu $menu): RedirectResponse
    {
        $menu->items()->create($this->itemData($request));
        $menu->touch();
        $this->menuCache->forgetMenu($menu);

        return to_route('admin.menus.items.index', $menu)
            ->with('success', 'Menu item added successfully.');
    }

    public function edit(Request $request, Menu $menu, MenuItem $menuItem): View
    {
        return $this->itemsView($request, $menu, $menuItem);
    }

    public function update(
        MenuItemRequest $request,
        Menu $menu,
        MenuItem $menuItem
    ): RedirectResponse {
        $menuItem->update($this->itemData($request));
        $menu->touch();
        $this->menuCache->forgetMenu($menu);

        return to_route('admin.menus.items.index', $menu)
            ->with('success', 'Menu item updated successfully.');
    }

    public function destroy(Menu $menu, MenuItem $menuItem): RedirectResponse
    {
        $menuItem->children()->update(['parent_id' => null]);
        $menuItem->delete();
        $menu->touch();
        $this->menuCache->forgetMenu($menu);

        return back()->with('success', 'Menu item deleted successfully.');
    }

    public function toggleStatus(Menu $menu, MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update(['status' => ! $menuItem->status]);
        $menu->touch();
        $this->menuCache->forgetMenu($menu);

        return back()->with('success', 'Menu item status updated successfully.');
    }

    private function itemsView(Request $request, Menu $menu, MenuItem $menuItem): View
    {
        $search = trim((string) $request->query('search'));
        $menuItems = $menu->items()
            ->with(['parent:id,title', 'page:id,title', 'blog:id,title', 'blogCategory:id,name'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%")
                        ->orWhere('icon', 'like', "%{$search}%");
                });
            })
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        $parentItems = $menu->items()
            ->whereKeyNot($menuItem->getKey())
            ->ordered()
            ->get(['id', 'title', 'parent_id']);

        return view('admin.website.menus.items', [
            'menu' => $menu,
            'menuItem' => $menuItem,
            'menuItems' => $menuItems,
            'parentItems' => $parentItems,
            'pages' => Page::query()->orderBy('title')->get(['id', 'title']),
            'blogs' => Blog::query()->latest()->get(['id', 'title']),
            'blogCategories' => BlogCategory::query()->ordered()->get(['id', 'name']),
            'search' => $search,
        ]);
    }

    private function itemData(MenuItemRequest $request): array
    {
        $data = $request->validated();
        $type = $data['type'];

        $data['page_id'] = $type === 'page' ? ($data['page_id'] ?? null) : null;
        $data['blog_id'] = $type === 'blog' ? ($data['blog_id'] ?? null) : null;
        $data['blog_category_id'] = $type === 'blog_category'
            ? ($data['blog_category_id'] ?? null)
            : null;
        $data['url'] = $type === 'custom_url' ? ($data['url'] ?? null) : null;

        return $data;
    }
}
