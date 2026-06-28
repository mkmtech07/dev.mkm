<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PageRequest;
use App\Models\Page;
use App\Support\MediaPicker;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $pages = Page::query()
            ->withCount('blocks')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('meta_title', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.website.pages.index', compact('pages', 'search'));
    }

    public function create(): View
    {
        return view('admin.website.pages.create', [
            'page' => new Page([
                'page_type' => 'default',
                'template' => 'default',
                'status' => true,
                'show_in_menu' => false,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(PageRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['featured_image', ...MediaPicker::fieldInputs(['featured_image'])]);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = PublicImage::store($request->file('featured_image'), 'pages');
        } elseif ($selectedPath = MediaPicker::selectedPath($request, 'featured_image')) {
            $data['featured_image'] = $selectedPath;
        }

        Page::create($data);

        return to_route('admin.pages.index')
            ->with('success', 'Page created successfully.');
    }

    public function edit(Page $page): View
    {
        $page->loadCount('blocks');

        return view('admin.website.pages.edit', compact('page'));
    }

    public function update(PageRequest $request, Page $page): RedirectResponse
    {
        $data = $request->safe()->except(['featured_image', ...MediaPicker::fieldInputs(['featured_image'])]);
        $oldImage = null;

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = PublicImage::store($request->file('featured_image'), 'pages');
            $oldImage = $page->featured_image;
        } elseif ($selectedPath = MediaPicker::selectedPath($request, 'featured_image')) {
            $data['featured_image'] = $selectedPath;
            $oldImage = $page->featured_image !== $selectedPath ? $page->featured_image : null;
        } elseif (MediaPicker::shouldClear($request, 'featured_image')) {
            $data['featured_image'] = null;
            $oldImage = $page->featured_image;
        }

        $page->update($data);
        PublicImage::delete($oldImage);

        return to_route('admin.pages.index')
            ->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $image = $page->featured_image;

        $page->delete();
        PublicImage::delete($image);

        return to_route('admin.pages.index')
            ->with('success', 'Page deleted successfully.');
    }

    public function toggleStatus(Page $page): RedirectResponse
    {
        $page->update(['status' => ! $page->status]);

        return back()->with('success', 'Page status updated successfully.');
    }
}
