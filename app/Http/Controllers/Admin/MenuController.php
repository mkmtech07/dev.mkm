<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MenuRequest;
use App\Models\Menu;
use App\Services\MenuCacheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __construct(private readonly MenuCacheService $menuCache) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $location = in_array($request->query('location'), Menu::LOCATIONS, true)
            ? $request->query('location')
            : '';
        $status = in_array($request->query('status'), ['all', 'active', 'inactive'], true)
            ? $request->query('status')
            : 'all';

        $menus = Menu::query()
            ->withCount('items')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($location !== '', fn ($query) => $query->where('location', $location))
            ->when($status === 'active', fn ($query) => $query->where('status', true))
            ->when($status === 'inactive', fn ($query) => $query->where('status', false))
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.website.menus.index', compact('menus', 'search', 'location', 'status'));
    }

    public function create(): View
    {
        return view('admin.website.menus.create', [
            'menu' => new Menu(['location' => 'header', 'status' => true]),
        ]);
    }

    public function store(MenuRequest $request): RedirectResponse
    {
        $menu = Menu::create($request->validated());
        $this->menuCache->forgetMenu($menu);

        return to_route('admin.menus.items.index', $menu)
            ->with('success', 'Menu created successfully. You can now add its items.');
    }

    public function edit(Menu $menu): View
    {
        return view('admin.website.menus.edit', compact('menu'));
    }

    public function update(MenuRequest $request, Menu $menu): RedirectResponse
    {
        $oldLocation = $menu->location;
        $menu->update($request->validated());

        $this->menuCache->forget($oldLocation);
        $this->menuCache->forgetMenu($menu);

        return to_route('admin.menus.index')->with('success', 'Menu updated successfully.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        DB::transaction(function () use ($menu) {
            $menu->items()->delete();
            $menu->delete();
        });
        $this->menuCache->forgetMenu($menu);

        return to_route('admin.menus.index')->with('success', 'Menu deleted successfully.');
    }

    public function toggleStatus(Menu $menu): RedirectResponse
    {
        $menu->update(['status' => ! $menu->status]);
        $this->menuCache->forgetMenu($menu);

        return back()->with('success', 'Menu status updated successfully.');
    }
}
