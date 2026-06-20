<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GalleryRequest;
use App\Models\Gallery;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $category = trim((string) $request->query('category'));

        $galleries = Gallery::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('alt_text', 'like', "%{$search}%");
                });
            })
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $categories = Gallery::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.website.gallery.index', compact(
            'galleries',
            'categories',
            'search',
            'category'
        ));
    }

    public function create(): View
    {
        return view('admin.website.gallery.create', [
            'gallery' => new Gallery([
                'status' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(GalleryRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $data['image'] = PublicImage::store($request->file('image'), 'gallery');

        Gallery::create($data);

        return to_route('admin.gallery.index')
            ->with('success', 'Gallery image created successfully.');
    }

    public function edit(Gallery $gallery): View
    {
        return view('admin.website.gallery.edit', compact('gallery'));
    }

    public function update(GalleryRequest $request, Gallery $gallery): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $oldImage = null;

        if ($request->hasFile('image')) {
            $data['image'] = PublicImage::store($request->file('image'), 'gallery');
            $oldImage = $gallery->image;
        }

        $gallery->update($data);
        PublicImage::delete($oldImage);

        return to_route('admin.gallery.index')
            ->with('success', 'Gallery image updated successfully.');
    }

    public function destroy(Gallery $gallery): RedirectResponse
    {
        $image = $gallery->image;

        $gallery->delete();
        PublicImage::delete($image);

        return to_route('admin.gallery.index')
            ->with('success', 'Gallery image deleted successfully.');
    }

    public function toggleStatus(Gallery $gallery): RedirectResponse
    {
        $gallery->update(['status' => ! $gallery->status]);

        return back()->with('success', 'Gallery status updated successfully.');
    }
}
