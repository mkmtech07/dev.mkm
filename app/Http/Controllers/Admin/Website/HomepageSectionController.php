<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\HomepageSectionRequest;
use App\Models\HomepageSection;
use App\Support\MediaPicker;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomepageSectionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $type = trim((string) $request->query('type'));

        $sections = HomepageSection::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%")
                        ->orWhere('section_key', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->when(in_array($type, HomepageSection::TYPES, true), fn ($query) => $query->where('type', $type))
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return view('admin.website.homepage-sections.index', compact('sections', 'search', 'type'));
    }

    public function create(): View
    {
        return view('admin.website.homepage-sections.create', [
            'homepageSection' => new HomepageSection([
                'type' => 'custom',
                'status' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(HomepageSectionRequest $request): RedirectResponse
    {
        $fields = ['image', 'background_image'];
        $data = $request->safe()->except([...$fields, ...MediaPicker::fieldInputs($fields)]);

        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = PublicImage::store($request->file($field), 'homepage-sections');
            } elseif ($selectedPath = MediaPicker::selectedPath($request, $field)) {
                $data[$field] = $selectedPath;
            }
        }

        HomepageSection::create($data);

        return to_route('admin.website.homepage-sections.index')
            ->with('success', 'Homepage section created successfully.');
    }

    public function edit(HomepageSection $homepageSection): View
    {
        return view('admin.website.homepage-sections.edit', compact('homepageSection'));
    }

    public function update(HomepageSectionRequest $request, HomepageSection $homepageSection): RedirectResponse
    {
        $fields = ['image', 'background_image'];
        $data = $request->safe()->except([...$fields, ...MediaPicker::fieldInputs($fields)]);
        $oldImages = [];

        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = PublicImage::store($request->file($field), 'homepage-sections');
                $oldImages[] = $homepageSection->{$field};
            } elseif ($selectedPath = MediaPicker::selectedPath($request, $field)) {
                $data[$field] = $selectedPath;
                if ($homepageSection->{$field} && $homepageSection->{$field} !== $selectedPath) {
                    $oldImages[] = $homepageSection->{$field};
                }
            } elseif (MediaPicker::shouldClear($request, $field)) {
                $data[$field] = null;
                $oldImages[] = $homepageSection->{$field};
            }
        }

        $homepageSection->update($data);

        foreach ($oldImages as $oldImage) {
            PublicImage::delete($oldImage);
        }

        return to_route('admin.website.homepage-sections.index')
            ->with('success', 'Homepage section updated successfully.');
    }

    public function destroy(HomepageSection $homepageSection): RedirectResponse
    {
        $images = [$homepageSection->image, $homepageSection->background_image];
        $homepageSection->delete();

        foreach ($images as $image) {
            PublicImage::delete($image);
        }

        return back()->with('success', 'Homepage section deleted successfully.');
    }

    public function toggleStatus(HomepageSection $homepageSection): RedirectResponse
    {
        $homepageSection->update(['status' => ! $homepageSection->status]);

        return back()->with('success', 'Homepage section status updated successfully.');
    }
}
