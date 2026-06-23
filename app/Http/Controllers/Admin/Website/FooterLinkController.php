<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\FooterLinkRequest;
use App\Models\FooterLink;
use App\Models\FooterSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class FooterLinkController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $sectionId = max(0, $request->integer('section'));
        $links = FooterLink::query()
            ->with('section:id,title')
            ->when($sectionId > 0, fn ($query) => $query->where('footer_section_id', $sectionId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%")
                        ->orWhere('icon', 'like', "%{$search}%");
                });
            })
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return view('admin.website.footer.links.index', [
            'links' => $links,
            'sections' => $this->sections(),
            'search' => $search,
            'sectionId' => $sectionId,
        ]);
    }

    public function create(): View
    {
        return view('admin.website.footer.links.create', [
            'footerLink' => new FooterLink(['target' => '_self', 'status' => true, 'sort_order' => 0]),
            'sections' => $this->sections(),
        ]);
    }

    public function store(FooterLinkRequest $request): RedirectResponse
    {
        FooterLink::create($request->validated());

        return to_route('admin.website.footer.links.index')->with('success', 'Footer link created successfully.');
    }

    public function edit(FooterLink $footerLink): View
    {
        return view('admin.website.footer.links.edit', [
            'footerLink' => $footerLink,
            'sections' => $this->sections(),
        ]);
    }

    public function update(FooterLinkRequest $request, FooterLink $footerLink): RedirectResponse
    {
        $footerLink->update($request->validated());

        return to_route('admin.website.footer.links.index')->with('success', 'Footer link updated successfully.');
    }

    public function destroy(FooterLink $footerLink): RedirectResponse
    {
        $footerLink->delete();

        return back()->with('success', 'Footer link deleted successfully.');
    }

    public function toggleStatus(FooterLink $footerLink): RedirectResponse
    {
        $footerLink->update(['status' => ! $footerLink->status]);

        return back()->with('success', 'Footer link status updated successfully.');
    }

    private function sections(): Collection
    {
        return FooterSection::query()->ordered()->get(['id', 'title']);
    }
}
