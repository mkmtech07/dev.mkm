<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Page;
use App\Models\SchemaMarkup;
use App\Models\SeoPage;
use App\Services\SeoManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PublicSeoController extends Controller
{
    public function show(Request $request, SeoManager $seoManager): JsonResponse
    {
        $validated = $request->validate([
            'path' => ['nullable', 'string', 'max:2000'],
        ]);

        return response()->json($seoManager->resolve($validated['path'] ?? '/'));
    }

    public function schema(SeoManager $seoManager): JsonResponse
    {
        $settings = $seoManager->settings();
        if (! $settings->status || ! $settings->schema_status) {
            return response()->json(['data' => []]);
        }

        $data = SchemaMarkup::query()
            ->active()
            ->ordered()
            ->get(['name', 'type', 'schema_json'])
            ->map(function (SchemaMarkup $markup) {
                $schema = json_decode((string) $markup->schema_json, true);

                return is_array($schema) ? [
                    'name' => $markup->name,
                    'type' => $markup->type,
                    'schema' => $schema,
                ] : null;
            })
            ->filter()
            ->values();

        return response()->json(['data' => $data]);
    }

    public function sitemap(SeoManager $seoManager): Response
    {
        $settings = $seoManager->settings();
        abort_unless($settings->status && $settings->sitemap_status, 404);

        $xml = Cache::remember(
            SeoManager::SITEMAP_CACHE_KEY,
            max(1, $settings->sitemap_cache_minutes) * 60,
            fn () => $this->buildSitemap($seoManager, $settings->default_robots_index)
        );

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(SeoManager $seoManager): Response
    {
        $settings = $seoManager->settings();
        abort_unless($settings->status && $settings->robots_status, 404);

        $content = trim((string) $settings->robots_content);
        if ($content === '') {
            $content = implode("\n", [
                'User-agent: *',
                'Allow: /',
                'Disallow: /admin',
                'Disallow: /login',
                'Disallow: /register',
                'Disallow: /dashboard',
                '',
                'Sitemap: '.$seoManager->absoluteUrl('/sitemap.xml'),
            ]);
        }

        return response($content."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    private function buildSitemap(SeoManager $seoManager, bool $defaultIndex): string
    {
        $seoPages = SeoPage::query()->active()->get();
        $entries = collect();

        foreach ([
            ['/', 'static', null, 'home'],
            ['/about', 'static', null, 'about'],
            ['/services', 'static', null, 'services'],
            ['/gallery', 'static', null, 'gallery'],
            ['/blog', 'static', null, 'blog'],
            ['/contact', 'static', null, 'contact'],
        ] as [$path, $type, $relatedId, $pageKey]) {
            $this->addEntry($entries, $seoPages, $seoManager, $path, $type, $relatedId, $pageKey, null, $defaultIndex);
        }

        Page::query()->where('status', true)->get()->each(function (Page $page) use ($entries, $seoPages, $seoManager, $defaultIndex) {
            $this->addEntry($entries, $seoPages, $seoManager, '/'.$page->slug, 'page', $page->id, null, $page->updated_at, $defaultIndex);
        });

        Blog::query()->active()->published()->get()->each(function (Blog $blog) use ($entries, $seoPages, $seoManager, $defaultIndex) {
            $this->addEntry($entries, $seoPages, $seoManager, '/blog/'.$blog->slug, 'blog', $blog->id, null, $blog->updated_at, $defaultIndex);
        });

        BlogCategory::query()->active()->get()->each(function (BlogCategory $category) use ($entries, $seoPages, $seoManager, $defaultIndex) {
            $this->addEntry($entries, $seoPages, $seoManager, '/blog?category='.$category->slug, 'blog_category', $category->id, null, $category->updated_at, $defaultIndex);
        });

        foreach ($seoPages as $seoPage) {
            if ($seoPage->route_path && $this->isPublicPath($seoPage->route_path)) {
                $this->addEntry(
                    $entries, $seoPages, $seoManager, $seoPage->route_path, $seoPage->page_type,
                    $seoPage->related_id, $seoPage->page_key, $seoPage->updated_at, $defaultIndex
                );
            }
        }

        $urls = $entries->values()->map(function (array $entry) {
            $parts = ['  <url>', '    <loc>'.$this->xml($entry['loc']).'</loc>'];
            if ($entry['lastmod']) {
                $parts[] = '    <lastmod>'.$entry['lastmod'].'</lastmod>';
            }
            $parts[] = '    <changefreq>'.$entry['changefreq'].'</changefreq>';
            $parts[] = '    <priority>'.$entry['priority'].'</priority>';
            $parts[] = '  </url>';

            return implode("\n", $parts);
        })->implode("\n");

        return '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n"
            .$urls."\n</urlset>";
    }

    private function addEntry(
        Collection $entries,
        Collection $seoPages,
        SeoManager $seoManager,
        string $path,
        string $type,
        ?int $relatedId,
        ?string $pageKey,
        mixed $lastModified,
        bool $defaultIndex
    ): void {
        if (! $this->isPublicPath($path)) {
            return;
        }

        $normalizedPath = $seoManager->parseLocation($path)[2];
        $seoPage = $seoPages->first(fn (SeoPage $page) => $page->route_path === $normalizedPath)
            ?? $seoPages->first(fn (SeoPage $page) => $relatedId && $page->page_type === $type && $page->related_id === $relatedId)
            ?? $seoPages->first(fn (SeoPage $page) => $pageKey && $page->page_key === $pageKey);

        if (($seoPage && ! $seoPage->robots_index) || (! $seoPage && ! $defaultIndex)) {
            return;
        }

        $entries->put($normalizedPath, [
            'loc' => $seoPage?->canonical_url ?: $seoManager->absoluteUrl($normalizedPath),
            'lastmod' => ($lastModified ?? $seoPage?->updated_at)?->toAtomString(),
            'changefreq' => $seoPage?->change_frequency ?: 'weekly',
            'priority' => number_format((float) ($seoPage?->priority ?? ($normalizedPath === '/' ? 1.0 : 0.8)), 1, '.', ''),
        ]);
    }

    private function isPublicPath(string $path): bool
    {
        $path = '/'.ltrim(strtolower(trim($path)), '/');

        foreach (['/admin', '/api', '/login', '/register', '/dashboard', '/profile', '/logout', '/password'] as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return false;
            }
        }

        return ! str_contains($path, '://');
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
