<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use App\Support\MediaPicker;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $services = Service::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.website.services.index', compact('services', 'search'));
    }

    public function create(): View
    {
        return view('admin.website.services.create', [
            'service' => new Service([
                'status' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image', ...MediaPicker::fieldInputs(['image'])]);

        if ($request->hasFile('image')) {
            $data['image'] = PublicImage::store($request->file('image'), 'services');
        } elseif ($selectedPath = MediaPicker::selectedPath($request, 'image')) {
            $data['image'] = $selectedPath;
        }

        Service::create($data);

        return to_route('admin.services.index')
            ->with('success', 'Service created successfully.');
    }

    public function edit(Service $service): View
    {
        return view('admin.website.services.edit', compact('service'));
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $data = $request->safe()->except(['image', ...MediaPicker::fieldInputs(['image'])]);
        $oldImage = null;

        if ($request->hasFile('image')) {
            $data['image'] = PublicImage::store($request->file('image'), 'services');
            $oldImage = $service->image;
        } elseif ($selectedPath = MediaPicker::selectedPath($request, 'image')) {
            $data['image'] = $selectedPath;
            $oldImage = $service->image !== $selectedPath ? $service->image : null;
        } elseif (MediaPicker::shouldClear($request, 'image')) {
            $data['image'] = null;
            $oldImage = $service->image;
        }

        $service->update($data);
        PublicImage::delete($oldImage);

        return to_route('admin.services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $image = $service->image;

        $service->delete();
        PublicImage::delete($image);

        return to_route('admin.services.index')
            ->with('success', 'Service deleted successfully.');
    }
}
