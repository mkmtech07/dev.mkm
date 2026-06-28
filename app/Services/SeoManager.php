<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Page;
use App\Models\SeoPage;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SeoManager
{
    public const SITEMAP_CACHE_KEY = 'seo.sitemap.xml';

    public function sitemapCacheKey(): string
    {
        return self::SITEMAP_CACHE_KEY.'.tenant.'.app(TenantManager::class)->cacheKeySuffix();
    }

    public function forgetSitemapCache(): void
    {
        Cache::forget($this->sitemapCacheKey());
    }

    public function settings(bool $create = false): SeoSetting
    {
        if (! Schema::hasTable('seo_settings')) {
            return new SeoSetting(SeoSetting::defaults());
        }

        $settings = SeoSetting::query()->oldest('id')->first();

        if (! $settings && $create) {
            return SeoSetting::create(SeoSetting::defaults());
        }

        return $settings ?? new SeoSetting(SeoSetting::defaults());
    }

    /**
     * Resolve safe, public metadata for a frontend location.
     *
     * @return array<string, mixed>
     */
    public function resolve(string $location): array
    {
        [$path, $query, $routeKey] = $this->parseLocation($location);
        [$relatedType, $relatedId, $pageKey, $related] = $this->relatedMetadata($path, $query);

        $seoPage = SeoPage::query()
            ->active()
            ->where(function ($builder) use ($routeKey, $path, $relatedType, $relatedId, $pageKey) {
                $builder->whereIn('route_path', array_values(array_unique([$routeKey, $path])));

                if ($relatedType && $relatedId) {
                    $builder->orWhere(function ($builder) use ($relatedType, $relatedId) {
                        $builder->where('page_type', $relatedType)->where('related_id', $relatedId);
                    });
                }

                if ($pageKey) {
                    $builder->orWhere('page_key', $pageKey);
                }
            })
            ->orderByRaw('CASE WHEN route_path = ? THEN 0 WHEN route_path = ? THEN 1 ELSE 2 END', [$routeKey, $path])
            ->latest('id')
            ->first();

        $global = WebsiteSetting::query()->where('status', true)->oldest('id')->first();
        $settings = $this->settings();
        $siteName = $global?->site_name ?: 'CMS Website';
        $defaultTitle = $related['title'] ?? ($pageKey ? ucfirst($pageKey) : $siteName);
        if ($pageKey && $pageKey !== 'home') {
            $defaultTitle .= ' | '.$siteName;
        }

        $metaTitle = $seoPage?->meta_title
            ?: $seoPage?->title
            ?: ($related['meta_title'] ?? null)
            ?: $global?->meta_title
            ?: $defaultTitle
            ?: $siteName;
        $metaDescription = $seoPage?->meta_description
            ?: ($related['meta_description'] ?? null)
            ?: $global?->meta_description
            ?: 'Professional website powered by Laravel and Vue';
        $metaKeywords = $seoPage?->meta_keywords ?: $global?->meta_keywords;
        $canonical = $seoPage?->canonical_url
            ?: ($related['canonical_url'] ?? null)
            ?: $this->absoluteUrl($routeKey);
        $fallbackImage = $this->publicImage($related['image'] ?? null) ?: $this->publicImage($global?->og_image);
        $ogImage = $this->publicImage($seoPage?->og_image) ?: $fallbackImage;
        $twitterImage = $this->publicImage($seoPage?->twitter_image) ?: $ogImage;

        return [
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'meta_keywords' => $metaKeywords,
            'canonical_url' => $canonical,
            'og_title' => $seoPage?->og_title ?: $metaTitle,
            'og_description' => $seoPage?->og_description ?: $metaDescription,
            'og_image' => $ogImage,
            'twitter_title' => $seoPage?->twitter_title ?: ($seoPage?->og_title ?: $metaTitle),
            'twitter_description' => $seoPage?->twitter_description ?: ($seoPage?->og_description ?: $metaDescription),
            'twitter_image' => $twitterImage,
            'robots_index' => $seoPage ? $seoPage->robots_index : (bool) $settings->default_robots_index,
            'robots_follow' => $seoPage ? $seoPage->robots_follow : (bool) $settings->default_robots_follow,
        ];
    }

    /**
     * @return array{0: string, 1: array<string, string>, 2: string}
     */
    public function parseLocation(string $location): array
    {
        $location = trim($location) ?: '/';
        $parts = parse_url(str_starts_with($location, '/') ? 'https://seo.local'.$location : $location);
        $path = '/'.ltrim((string) ($parts['path'] ?? '/'), '/');
        $path = $path === '/' ? '/' : rtrim($path, '/');
        parse_str((string) ($parts['query'] ?? ''), $query);
        $safeQuery = [];
        if ($path === '/blog' && isset($query['category']) && is_string($query['category'])) {
            $safeQuery['category'] = $query['category'];
        }
        $routeKey = $path.($safeQuery ? '?'.http_build_query($safeQuery) : '');

        return [$path, $safeQuery, $routeKey];
    }

    public function absoluteUrl(string $path): string
    {
        $tenant = app(TenantManager::class)->current();
        $base = $tenant ? $tenant->publicUrl() : (string) config('app.url', request()->root());

        return rtrim($base, '/').'/'.ltrim($path, '/');
    }

    public function publicImage(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return filter_var($path, FILTER_VALIDATE_URL) ? $path : asset(ltrim($path, '/'));
    }

    /**
     * @return array{0: ?string, 1: ?int, 2: ?string, 3: array<string, mixed>}
     */
    private function relatedMetadata(string $path, array $query): array
    {
        $staticKeys = [
            '/' => 'home', '/about' => 'about', '/services' => 'services', '/gallery' => 'gallery',
            '/blog' => 'blog', '/contact' => 'contact', '/faq' => 'faq',
        ];

        if ($path === '/blog' && isset($query['category'])) {
            $category = BlogCategory::query()->active()->where('slug', $query['category'])->first();
            if ($category) {
                return ['blog_category', $category->id, null, [
                    'title' => $category->name,
                    'meta_title' => $category->meta_title,
                    'meta_description' => $category->meta_description ?: $category->description,
                ]];
            }
        }

        if (preg_match('#^/blog/([A-Za-z0-9-]+)$#', $path, $matches)) {
            $blog = Blog::query()->active()->published()->where('slug', $matches[1])->first();
            if ($blog) {
                return ['blog', $blog->id, null, [
                    'title' => $blog->title,
                    'meta_title' => $blog->meta_title,
                    'meta_description' => $blog->meta_description ?: $blog->excerpt,
                    'canonical_url' => $blog->canonical_url,
                    'image' => $blog->og_image ?: $blog->featured_image,
                ]];
            }
        }

        if (preg_match('#^/services/([A-Za-z0-9-]+)$#', $path, $matches)) {
            $service = Service::query()->where('status', true)->where('slug', $matches[1])->first();
            if ($service) {
                return ['service', $service->id, null, [
                    'title' => $service->title,
                    'meta_description' => $service->short_description,
                    'image' => $service->image,
                ]];
            }
        }

        if (isset($staticKeys[$path])) {
            return ['static', null, $staticKeys[$path], ['title' => ucfirst($staticKeys[$path])]];
        }

        if (preg_match('#^/([A-Za-z0-9-]+)$#', $path, $matches)) {
            $page = Page::query()->where('status', true)->where('slug', $matches[1])->first();
            if ($page) {
                return ['page', $page->id, null, [
                    'title' => $page->title,
                    'meta_title' => $page->meta_title,
                    'meta_description' => $page->meta_description,
                    'image' => $page->featured_image,
                ]];
            }
        }

        return [null, null, null, []];
    }
}
