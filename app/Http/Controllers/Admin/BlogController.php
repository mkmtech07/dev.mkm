<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlogRequest;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $categoryId = $request->integer('category');

        $blogs = Blog::query()
            ->with('category:id,name')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%");
                });
            })
            ->when($categoryId > 0, fn ($query) => $query->where('blog_category_id', $categoryId))
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        $categories = BlogCategory::query()->ordered()->get(['id', 'name']);

        return view('admin.website.blogs.index', compact('blogs', 'categories', 'search', 'categoryId'));
    }

    public function create(): View
    {
        return view('admin.website.blogs.create', [
            'blog' => new Blog([
                'status' => true,
                'is_featured' => false,
            ]),
            'categories' => BlogCategory::query()->ordered()->get(['id', 'name']),
        ]);
    }

    public function store(BlogRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['featured_image', 'og_image']);
        $uploadedImages = [];

        try {
            foreach (['featured_image', 'og_image'] as $field) {
                if ($request->hasFile($field)) {
                    $data[$field] = PublicImage::store($request->file($field), 'blogs');
                    $uploadedImages[] = $data[$field];
                }
            }

            Blog::create($data);
        } catch (Throwable $exception) {
            foreach ($uploadedImages as $image) {
                PublicImage::delete($image);
            }

            throw $exception;
        }

        return to_route('admin.blogs.index')
            ->with('success', 'Blog post created successfully.');
    }

    public function edit(Blog $blog): View
    {
        return view('admin.website.blogs.edit', [
            'blog' => $blog,
            'categories' => BlogCategory::query()->ordered()->get(['id', 'name']),
        ]);
    }

    public function update(BlogRequest $request, Blog $blog): RedirectResponse
    {
        $data = $request->safe()->except(['featured_image', 'og_image']);
        $newImages = [];
        $oldImages = [];

        try {
            foreach (['featured_image', 'og_image'] as $field) {
                if ($request->hasFile($field)) {
                    $data[$field] = PublicImage::store($request->file($field), 'blogs');
                    $newImages[] = $data[$field];
                    $oldImages[] = $blog->{$field};
                }
            }

            $blog->update($data);
        } catch (Throwable $exception) {
            foreach ($newImages as $image) {
                PublicImage::delete($image);
            }

            throw $exception;
        }

        foreach ($oldImages as $image) {
            PublicImage::delete($image);
        }

        return to_route('admin.blogs.index')
            ->with('success', 'Blog post updated successfully.');
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        $images = [$blog->featured_image, $blog->og_image];

        $blog->delete();

        foreach ($images as $image) {
            PublicImage::delete($image);
        }

        return to_route('admin.blogs.index')
            ->with('success', 'Blog post deleted successfully.');
    }

    public function toggleStatus(Blog $blog): RedirectResponse
    {
        $blog->update(['status' => ! $blog->status]);

        return back()->with('success', 'Blog post status updated successfully.');
    }

    public function toggleFeatured(Blog $blog): RedirectResponse
    {
        $blog->update(['is_featured' => ! $blog->is_featured]);

        return back()->with('success', 'Featured status updated successfully.');
    }
}
