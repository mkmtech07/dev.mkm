<?php

namespace App\Services;

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
            ->with([
                'page:id,title,slug,status',
                'blog:id,title,slug,status,publish_at',
                'blogCategory:id,name,slug,status',
            ])
            ->ordered()
            ->get();

        $children = $items->whereNotNull('parent_id')->groupBy('parent_id');
        $tree = $items
            ->whereNull('parent_id')
            ->map(fn (MenuItem $item) => $this->transform($item, $children))
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

    /**
     * @param  Collection<int, Collection<int, MenuItem>>  $children
     * @param  array<int, true>  $visited
     */
    private function transform(MenuItem $item, Collection $children, array $visited = []): ?array
    {
        if (isset($visited[$item->id])) {
            return null;
        }

        $visited[$item->id] = true;
        $url = match ($item->type) {
            'page' => $item->page?->status ? '/'.$item->page->slug : null,
            'blog' => $item->blog?->status && ! $item->blog->publish_at?->isFuture()
                ? '/blog/'.$item->blog->slug
                : null,
            'blog_category' => $item->blogCategory?->status
                ? '/blog?category='.rawurlencode($item->blogCategory->slug)
                : null,
            'custom_url' => $this->safeCustomUrl($item->url),
            default => null,
        };

        if (! $url) {
            return null;
        }

        return [
            'id' => $item->id,
            'title' => $item->title,
            'type' => $item->type,
            'url' => $url,
            'icon' => $item->icon,
            'target' => $item->target,
            'children' => $children
                ->get($item->id, collect())
                ->map(fn (MenuItem $child) => $this->transform($child, $children, $visited))
                ->filter()
                ->values()
                ->all(),
        ];
    }

    private function key(string $location): string
    {
        $tenantKey = app(TenantManager::class)->cacheKeySuffix();

        return "frontend.menu.{$tenantKey}.{$location}";
    }

    private function safeCustomUrl(?string $url): ?string
    {
        return $url && preg_match('~^(?:/(?!/)|https?://|mailto:|tel:|#)~i', $url)
            ? $url
            : null;
    }
}
