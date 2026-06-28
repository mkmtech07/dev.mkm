<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeoPageRequest;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Gallery;
use App\Models\Page;
use App\Models\SeoPage;
use App\Models\Service;
use App\Services\SeoManager;
use App\Support\MediaPicker;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class SeoPageController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $type = in_array($request->query('type'), SeoPage::TYPES, true) ? $request->query('type') : '';

        $seoPages = SeoPage::query()
            ->when($type !== '', fn ($query) => $query->where('page_type', $type))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('page_key', 'like', "%{$search}%")
                    ->orWhere('route_path', 'like', "%{$search}%")
                    ->orWhere('meta_title', 'like', "%{$search}%");
            }))
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.website.seo.pages.index', compact('seoPages', 'search', 'type'));
    }

    public function create(): View
    {
        return view('admin.website.seo.pages.create', $this->formData(new SeoPage([
            'page_type' => 'static', 'robots_index' => true, 'robots_follow' => true,
            'priority' => 0.8, 'change_frequency' => 'weekly', 'status' => true,
        ])));
    }

    public function store(SeoPageRequest $request): RedirectResponse
    {
        $fields = ['og_image', 'twitter_image'];
        $data = $request->safe()->except([...$fields, ...MediaPicker::fieldInputs($fields)]);
        $uploads = [];

        try {
            foreach ($fields as $field) {
                if ($request->hasFile($field)) {
                    $data[$field] = PublicImage::store($request->file($field), 'seo');
                    $uploads[] = $data[$field];
                } elseif ($selectedPath = MediaPicker::selectedPath($request, $field)) {
                    $data[$field] = $selectedPath;
                }
            }
            SeoPage::create($data);
        } catch (Throwable $exception) {
            foreach ($uploads as $upload) {
                PublicImage::delete($upload);
            }
            throw $exception;
        }

        app(SeoManager::class)->forgetSitemapCache();

        return to_route('admin.website.seo.pages.index')->with('success', 'SEO page created successfully.');
    }

    public function edit(SeoPage $seoPage): View
    {
        return view('admin.website.seo.pages.edit', $this->formData($seoPage));
    }

    public function update(SeoPageRequest $request, SeoPage $seoPage): RedirectResponse
    {
        $fields = ['og_image', 'twitter_image'];
        $data = $request->safe()->except([...$fields, ...MediaPicker::fieldInputs($fields)]);
        $newImages = [];
        $oldImages = [];

        try {
            foreach ($fields as $field) {
                if ($request->hasFile($field)) {
                    $data[$field] = PublicImage::store($request->file($field), 'seo');
                    $newImages[] = $data[$field];
                    $oldImages[] = $seoPage->{$field};
                } elseif ($selectedPath = MediaPicker::selectedPath($request, $field)) {
                    $data[$field] = $selectedPath;
                    if ($seoPage->{$field} && $seoPage->{$field} !== $selectedPath) {
                        $oldImages[] = $seoPage->{$field};
                    }
                } elseif (MediaPicker::shouldClear($request, $field)) {
                    $data[$field] = null;
                    $oldImages[] = $seoPage->{$field};
                }
            }
            $seoPage->update($data);
        } catch (Throwable $exception) {
            foreach ($newImages as $image) {
                PublicImage::delete($image);
            }
            throw $exception;
        }

        foreach ($oldImages as $image) {
            PublicImage::delete($image);
        }
        app(SeoManager::class)->forgetSitemapCache();

        return to_route('admin.website.seo.pages.index')->with('success', 'SEO page updated successfully.');
    }

    public function destroy(SeoPage $seoPage): RedirectResponse
    {
        $images = [$seoPage->og_image, $seoPage->twitter_image];
        $seoPage->delete();
        foreach ($images as $image) {
            PublicImage::delete($image);
        }
        app(SeoManager::class)->forgetSitemapCache();

        return back()->with('success', 'SEO page deleted successfully.');
    }

    public function toggleStatus(SeoPage $seoPage): RedirectResponse
    {
        $seoPage->update(['status' => ! $seoPage->status]);
        app(SeoManager::class)->forgetSitemapCache();

        return back()->with('success', 'SEO page status updated successfully.');
    }

    /** @return array<string, mixed> */
    private function formData(SeoPage $seoPage): array
    {
        return [
            'seoPage' => $seoPage,
            'pages' => Page::query()->orderBy('title')->get(['id', 'title']),
            'blogs' => Blog::query()->orderBy('title')->get(['id', 'title']),
            'blogCategories' => BlogCategory::query()->orderBy('name')->get(['id', 'name']),
            'services' => Service::query()->orderBy('title')->get(['id', 'title']),
            'galleries' => Gallery::query()->orderBy('title')->get(['id', 'title']),
        ];
    }
}
