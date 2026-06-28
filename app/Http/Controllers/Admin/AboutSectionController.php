<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AboutSectionRequest;
use App\Models\AboutSection;
use App\Support\MediaPicker;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AboutSectionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $aboutSections = AboutSection::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.website.about.index', compact('aboutSections', 'search'));
    }

    public function create(): View
    {
        return view('admin.website.about.create', [
            'aboutSection' => new AboutSection(['status' => true]),
        ]);
    }

    public function store(AboutSectionRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image', ...MediaPicker::fieldInputs(['image'])]);

        if ($request->hasFile('image')) {
            $data['image'] = PublicImage::store($request->file('image'), 'about');
        } elseif ($selectedPath = MediaPicker::selectedPath($request, 'image')) {
            $data['image'] = $selectedPath;
        }

        DB::transaction(function () use ($data) {
            if ($data['status']) {
                AboutSection::query()->where('status', true)->update(['status' => false]);
            }

            AboutSection::create($data);
        });

        return to_route('admin.about.index')
            ->with('success', 'About section created successfully.');
    }

    public function edit(AboutSection $aboutSection): View
    {
        return view('admin.website.about.edit', compact('aboutSection'));
    }

    public function update(AboutSectionRequest $request, AboutSection $aboutSection): RedirectResponse
    {
        $data = $request->safe()->except(['image', ...MediaPicker::fieldInputs(['image'])]);
        $oldImage = null;

        if ($request->hasFile('image')) {
            $data['image'] = PublicImage::store($request->file('image'), 'about');
            $oldImage = $aboutSection->image;
        } elseif ($selectedPath = MediaPicker::selectedPath($request, 'image')) {
            $data['image'] = $selectedPath;
            $oldImage = $aboutSection->image !== $selectedPath ? $aboutSection->image : null;
        } elseif (MediaPicker::shouldClear($request, 'image')) {
            $data['image'] = null;
            $oldImage = $aboutSection->image;
        }

        DB::transaction(function () use ($data, $aboutSection) {
            if ($data['status']) {
                AboutSection::query()
                    ->where('id', '!=', $aboutSection->id)
                    ->where('status', true)
                    ->update(['status' => false]);
            }

            $aboutSection->update($data);
        });

        PublicImage::delete($oldImage);

        return to_route('admin.about.index')
            ->with('success', 'About section updated successfully.');
    }

    public function destroy(AboutSection $aboutSection): RedirectResponse
    {
        $image = $aboutSection->image;

        $aboutSection->delete();
        PublicImage::delete($image);

        return to_route('admin.about.index')
            ->with('success', 'About section deleted successfully.');
    }
}
