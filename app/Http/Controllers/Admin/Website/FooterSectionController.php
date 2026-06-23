<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\FooterSectionRequest;
use App\Models\FooterSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FooterSectionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $sections = FooterSection::query()
            ->withCount('links')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return view('admin.website.footer.sections.index', compact('sections', 'search'));
    }

    public function create(): View
    {
        return view('admin.website.footer.sections.create', [
            'footerSection' => new FooterSection(['type' => 'links', 'status' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(FooterSectionRequest $request): RedirectResponse
    {
        FooterSection::create($request->validated());

        return to_route('admin.website.footer.sections.index')->with('success', 'Footer section created successfully.');
    }

    public function edit(FooterSection $footerSection): View
    {
        return view('admin.website.footer.sections.edit', compact('footerSection'));
    }

    public function update(FooterSectionRequest $request, FooterSection $footerSection): RedirectResponse
    {
        $footerSection->update($request->validated());

        return to_route('admin.website.footer.sections.index')->with('success', 'Footer section updated successfully.');
    }

    public function destroy(FooterSection $footerSection): RedirectResponse
    {
        $footerSection->delete();

        return back()->with('success', 'Footer section deleted successfully.');
    }

    public function toggleStatus(FooterSection $footerSection): RedirectResponse
    {
        $footerSection->update(['status' => ! $footerSection->status]);

        return back()->with('success', 'Footer section status updated successfully.');
    }
}
