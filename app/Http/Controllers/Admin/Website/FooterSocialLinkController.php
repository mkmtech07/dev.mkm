<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\FooterSocialLinkRequest;
use App\Models\FooterSocialLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FooterSocialLinkController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $socialLinks = FooterSocialLink::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('platform', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%")
                        ->orWhere('icon', 'like', "%{$search}%");
                });
            })
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return view('admin.website.footer.social.index', compact('socialLinks', 'search'));
    }

    public function create(): View
    {
        return view('admin.website.footer.social.create', [
            'footerSocialLink' => new FooterSocialLink(['target' => '_blank', 'status' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(FooterSocialLinkRequest $request): RedirectResponse
    {
        FooterSocialLink::create($request->validated());

        return to_route('admin.website.footer.social.index')->with('success', 'Social link created successfully.');
    }

    public function edit(FooterSocialLink $footerSocialLink): View
    {
        return view('admin.website.footer.social.edit', compact('footerSocialLink'));
    }

    public function update(
        FooterSocialLinkRequest $request,
        FooterSocialLink $footerSocialLink
    ): RedirectResponse {
        $footerSocialLink->update($request->validated());

        return to_route('admin.website.footer.social.index')->with('success', 'Social link updated successfully.');
    }

    public function destroy(FooterSocialLink $footerSocialLink): RedirectResponse
    {
        $footerSocialLink->delete();

        return back()->with('success', 'Social link deleted successfully.');
    }

    public function toggleStatus(FooterSocialLink $footerSocialLink): RedirectResponse
    {
        $footerSocialLink->update(['status' => ! $footerSocialLink->status]);

        return back()->with('success', 'Social link status updated successfully.');
    }
}
