<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlogCategoryRequest;
use App\Http\Requests\BulkDeleteBlogCategoryRequest;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $view = in_array($request->query('view'), ['all', 'active', 'inactive', 'trashed'], true)
            ? $request->query('view')
            : 'all';

        $categories = BlogCategory::query()
            ->when($view === 'trashed', fn ($query) => $query->onlyTrashed())
            ->when($view === 'active', fn ($query) => $query->active())
            ->when($view === 'inactive', fn ($query) => $query->where('status', false))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return view('admin.website.blog-categories.index', compact('categories', 'search', 'view'));
    }

    public function create(): View
    {
        return view('admin.website.blog-categories.create', [
            'blogCategory' => new BlogCategory([
                'status' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(BlogCategoryRequest $request): RedirectResponse
    {
        BlogCategory::create($request->validated());

        return to_route('admin.blog-categories.index')
            ->with('success', 'Blog category created successfully.');
    }

    public function edit(BlogCategory $blogCategory): View
    {
        return view('admin.website.blog-categories.edit', compact('blogCategory'));
    }

    public function update(BlogCategoryRequest $request, BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->update($request->validated());

        return to_route('admin.blog-categories.index')
            ->with('success', 'Blog category updated successfully.');
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->delete();

        return to_route('admin.blog-categories.index')
            ->with('success', 'Blog category moved to trash.');
    }

    public function toggleStatus(BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->update(['status' => ! $blogCategory->status]);

        return back()->with('success', 'Blog category status updated successfully.');
    }

    public function restore(BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->restore();

        return to_route('admin.blog-categories.index', ['view' => 'trashed'])
            ->with('success', 'Blog category restored successfully.');
    }

    public function bulkDelete(BulkDeleteBlogCategoryRequest $request): RedirectResponse
    {
        $deleted = BlogCategory::query()
            ->whereKey($request->validated('categories'))
            ->delete();

        return back()->with('success', "{$deleted} blog ".($deleted === 1 ? 'category' : 'categories').' moved to trash.');
    }
}
