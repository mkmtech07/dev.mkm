<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\PageBlockRequest;
use App\Models\Page;
use App\Models\PageBlock;
use App\Support\MediaPicker;
use App\Support\PublicImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageBlockController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $type = trim((string) $request->query('type'));
        $pageId = (int) $request->query('page_id');

        $blocks = PageBlock::query()
            ->with('page:id,title,slug')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%")
                        ->orWhere('block_key', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%")
                        ->orWhereHas('page', fn ($query) => $query
                            ->where('title', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%"));
                });
            })
            ->when($pageId > 0, fn ($query) => $query->where('page_id', $pageId))
            ->when(in_array($type, PageBlock::TYPES, true), fn ($query) => $query->where('type', $type))
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        $pages = $this->pages();

        return view('admin.website.page-blocks.index', compact('blocks', 'pages', 'search', 'type', 'pageId'));
    }

    public function create(Request $request): View
    {
        $pageId = (int) $request->query('page_id');
        $pageExists = $pageId > 0 && Page::query()->whereKey($pageId)->exists();

        return view('admin.website.page-blocks.create', [
            'pageBlock' => new PageBlock([
                'page_id' => $pageExists ? $pageId : null,
                'type' => 'text',
                'status' => true,
                'sort_order' => 0,
            ]),
            'pages' => $this->pages(),
        ]);
    }

    public function store(PageBlockRequest $request): RedirectResponse
    {
        $fields = ['image', 'background_image'];
        $data = $request->safe()->except([...$fields, ...MediaPicker::fieldInputs($fields)]);

        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = PublicImage::store($request->file($field), 'page-blocks');
            } elseif ($selectedPath = MediaPicker::selectedPath($request, $field)) {
                $data[$field] = $selectedPath;
            }
        }

        $pageBlock = PageBlock::create($data);

        return to_route('admin.website.page-blocks.index', ['page_id' => $pageBlock->page_id])
            ->with('success', 'Page block created successfully.');
    }

    public function show(PageBlock $pageBlock): View
    {
        $pageBlock->load('page:id,title,slug');

        return view('admin.website.page-blocks.show', compact('pageBlock'));
    }

    public function edit(PageBlock $pageBlock): View
    {
        return view('admin.website.page-blocks.edit', [
            'pageBlock' => $pageBlock,
            'pages' => $this->pages(),
        ]);
    }

    public function update(PageBlockRequest $request, PageBlock $pageBlock): RedirectResponse
    {
        $fields = ['image', 'background_image'];
        $data = $request->safe()->except([...$fields, ...MediaPicker::fieldInputs($fields)]);
        $oldImages = [];

        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = PublicImage::store($request->file($field), 'page-blocks');
                $oldImages[] = $pageBlock->{$field};
            } elseif ($selectedPath = MediaPicker::selectedPath($request, $field)) {
                $data[$field] = $selectedPath;
                if ($pageBlock->{$field} && $pageBlock->{$field} !== $selectedPath) {
                    $oldImages[] = $pageBlock->{$field};
                }
            } elseif (MediaPicker::shouldClear($request, $field)) {
                $data[$field] = null;
                $oldImages[] = $pageBlock->{$field};
            }
        }

        $pageBlock->update($data);

        foreach ($oldImages as $oldImage) {
            PublicImage::delete($oldImage);
        }

        return to_route('admin.website.page-blocks.index', ['page_id' => $pageBlock->page_id])
            ->with('success', 'Page block updated successfully.');
    }

    public function destroy(PageBlock $pageBlock): RedirectResponse
    {
        $pageId = $pageBlock->page_id;
        $images = [$pageBlock->image, $pageBlock->background_image];

        $pageBlock->delete();

        foreach ($images as $image) {
            PublicImage::delete($image);
        }

        return to_route('admin.website.page-blocks.index', ['page_id' => $pageId])
            ->with('success', 'Page block deleted successfully.');
    }

    public function toggleStatus(PageBlock $pageBlock): RedirectResponse
    {
        $pageBlock->update(['status' => ! $pageBlock->status]);

        return back()->with('success', 'Page block status updated successfully.');
    }

    private function pages()
    {
        return Page::query()
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);
    }
}
