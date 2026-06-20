<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $categorySlug = trim((string) ($validated['category'] ?? ''));

        $query = Blog::query()
            ->with('category:id,name,slug')
            ->active()
            ->published()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->when($categorySlug !== '', function ($query) use ($categorySlug) {
                $query->whereHas('category', fn ($query) => $query
                    ->active()
                    ->where('slug', $categorySlug));
            });

        $featured = (clone $query)
            ->featured()
            ->ordered()
            ->limit(3)
            ->get()
            ->map(fn (Blog $blog) => $this->summary($blog));

        $blogs = $query
            ->ordered()
            ->paginate(9)
            ->withQueryString();

        $categories = BlogCategory::query()
            ->active()
            ->ordered()
            ->get(['id', 'name', 'slug']);

        return response()->json([
            'data' => $blogs->getCollection()->map(fn (Blog $blog) => $this->summary($blog)),
            'featured' => $featured,
            'categories' => $categories,
            'meta' => [
                'current_page' => $blogs->currentPage(),
                'last_page' => $blogs->lastPage(),
                'per_page' => $blogs->perPage(),
                'total' => $blogs->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $blog = Blog::query()
            ->with('category:id,name,slug')
            ->active()
            ->published()
            ->where('slug', $slug)
            ->first();

        if (! $blog) {
            return response()->json(['message' => 'Blog post not found.'], 404);
        }

        $blog->increment('views');

        $related = Blog::query()
            ->with('category:id,name,slug')
            ->active()
            ->published()
            ->whereKeyNot($blog->id)
            ->when(
                $blog->blog_category_id,
                fn ($query) => $query->where('blog_category_id', $blog->blog_category_id)
            )
            ->ordered()
            ->limit(3)
            ->get()
            ->map(fn (Blog $relatedBlog) => $this->summary($relatedBlog));

        return response()->json([
            'data' => [
                ...$this->summary($blog),
                'content' => $blog->content,
                'meta_title' => $blog->meta_title,
                'meta_description' => $blog->meta_description,
                'canonical_url' => $blog->canonical_url,
                'og_image' => $blog->og_image ? asset($blog->og_image) : null,
            ],
            'related' => $related,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(Blog $blog): array
    {
        $publishedAt = $blog->publish_at ?? $blog->created_at;

        return [
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'excerpt' => $blog->excerpt,
            'featured_image' => $blog->featured_image ? asset($blog->featured_image) : null,
            'author' => $blog->author,
            'published_at' => $publishedAt?->toIso8601String(),
            'is_featured' => $blog->is_featured,
            'views' => $blog->views,
            'category' => $blog->category ? [
                'name' => $blog->category->name,
                'slug' => $blog->category->slug,
            ] : null,
        ];
    }
}
