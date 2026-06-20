<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HeroSliderRequest;
use App\Models\HeroSlider;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HeroSliderController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $heroSliders = HeroSlider::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.website.hero-sliders.index', compact('heroSliders', 'search'));
    }

    public function create(): View
    {
        return view('admin.website.hero-sliders.create', [
            'heroSlider' => new HeroSlider([
                'status' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(HeroSliderRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image']);

        if ($request->hasFile('image')) {
            $data['image'] = PublicImage::store($request->file('image'), 'hero-sliders');
        }

        HeroSlider::create($data);

        return to_route('admin.hero-sliders.index')
            ->with('success', 'Hero slider created successfully.');
    }

    public function edit(HeroSlider $heroSlider): View
    {
        return view('admin.website.hero-sliders.edit', compact('heroSlider'));
    }

    public function update(HeroSliderRequest $request, HeroSlider $heroSlider): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $oldImage = null;

        if ($request->hasFile('image')) {
            $data['image'] = PublicImage::store($request->file('image'), 'hero-sliders');
            $oldImage = $heroSlider->image;
        }

        $heroSlider->update($data);

        PublicImage::delete($oldImage);

        return to_route('admin.hero-sliders.index')
            ->with('success', 'Hero slider updated successfully.');
    }

    public function destroy(HeroSlider $heroSlider): RedirectResponse
    {
        $image = $heroSlider->image;

        $heroSlider->delete();
        PublicImage::delete($image);

        return to_route('admin.hero-sliders.index')
            ->with('success', 'Hero slider deleted successfully.');
    }
}
