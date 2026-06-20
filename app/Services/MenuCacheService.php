<?php

namespace App\Services;

use App\Models\BlogCategory;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuCacheService
{
    public function get(string $location): ?array
    {
        $cached = Cache::remember(
            $this->key($location),
            now()->addHours(6),
            fn () => ['data' => $this->build($location)]
        );

        return $cached['data'];
    }

    public function forget(string $location): void
    {
        Cache::forget($this->key($location));
    }

    public function forgetMenu(Menu $menu): void
    {
        $this->forget($menu->location);
    }

    private function build(string $location): ?array
    {
        $menu = Menu::query()
            ->active()
            ->where('location', $location)
            ->latest('updated_at')
            ->latest('id')
            ->first();

        if (! $menu) {
            return null;
        }

        $items = $menu->items()
            ->active()
            ->with('page:id,title,slug,status')
            ->ordered()
            ->get();

        $categorySlugs = $items
            ->where('type', 'blog_category')
            ->pluck('url')
            ->filter()
            ->unique();

        $categories = BlogCategory::query()
            ->active()
            ->whereIn('slug', $categorySlugs)
            ->get(['name', 'slug'])
            ->keyBy('slug');

        $children = $items->whereNotNull('parent_id')->groupBy('parent_id');

        $tree = $items
            ->whereNull('parent_id')
            ->map(fn (MenuItem $item) => $this->transform($item, $children->get($item->id, collect()), $categories))
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $menu->id,
            'name' => $menu->name,
            'location' => $menu->location,
            'items' => $tree,
        ];
    }

    private function transform(MenuItem $item, Collection $children, Collection $categories): ?array
    {
        $url = match ($item->type) {
            'page' => $item->page?->status ? '/'.$item->page->slug : null,
            'blog_category' => $categories->has($item->url)
                ? '/blog?category='.rawurlencode($item->url)
                : null,
            'custom_url' => $item->url,
            default => null,
        };

        if (! $url) {
            return null;
        }

        return [
            'id' => $item->id,
            'title' => $item->title,
            'url' => $url,
            'icon' => $item->icon,
            'target' => $item->target,
            'children' => $children
                ->map(fn (MenuItem $child) => $this->transform($child, collect(), $categories))
                ->filter()
                ->values()
                ->all(),
        ];
    }

    private function key(string $location): string
    {
        return "frontend.menu.{$location}";
    }
}
