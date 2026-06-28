<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TestimonialRequest;
use App\Models\Testimonial;
use App\Support\MediaPicker;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $testimonials = Testimonial::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('client_name', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('designation', 'like', "%{$search}%")
                        ->orWhere('review', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('featured')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.website.testimonials.index', compact('testimonials', 'search'));
    }

    public function create(): View
    {
        return view('admin.website.testimonials.create', [
            'testimonial' => new Testimonial([
                'rating' => 5,
                'status' => true,
                'featured' => false,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(TestimonialRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image', ...MediaPicker::fieldInputs(['image'])]);

        if ($request->hasFile('image')) {
            $data['image'] = PublicImage::store($request->file('image'), 'testimonials');
        } elseif ($selectedPath = MediaPicker::selectedPath($request, 'image')) {
            $data['image'] = $selectedPath;
        }

        Testimonial::create($data);

        return to_route('admin.testimonials.index')
            ->with('success', 'Testimonial created successfully.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('admin.website.testimonials.edit', compact('testimonial'));
    }

    public function update(TestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $data = $request->safe()->except(['image', ...MediaPicker::fieldInputs(['image'])]);
        $oldImage = null;

        if ($request->hasFile('image')) {
            $data['image'] = PublicImage::store($request->file('image'), 'testimonials');
            $oldImage = $testimonial->image;
        } elseif ($selectedPath = MediaPicker::selectedPath($request, 'image')) {
            $data['image'] = $selectedPath;
            $oldImage = $testimonial->image !== $selectedPath ? $testimonial->image : null;
        } elseif (MediaPicker::shouldClear($request, 'image')) {
            $data['image'] = null;
            $oldImage = $testimonial->image;
        }

        $testimonial->update($data);
        PublicImage::delete($oldImage);

        return to_route('admin.testimonials.index')
            ->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $image = $testimonial->image;

        $testimonial->delete();
        PublicImage::delete($image);

        return to_route('admin.testimonials.index')
            ->with('success', 'Testimonial deleted successfully.');
    }

    public function toggleStatus(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update(['status' => ! $testimonial->status]);

        return back()->with('success', 'Testimonial status updated successfully.');
    }
}
