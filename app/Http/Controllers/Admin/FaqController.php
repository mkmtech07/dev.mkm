<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FaqRequest;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $faqs = Faq::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('question', 'like', "%{$search}%")
                        ->orWhere('answer', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.website.faqs.index', compact('faqs', 'search'));
    }

    public function create(): View
    {
        return view('admin.website.faqs.create', [
            'faq' => new Faq([
                'status' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(FaqRequest $request): RedirectResponse
    {
        Faq::create($request->validated());

        return to_route('admin.faqs.index')
            ->with('success', 'FAQ created successfully.');
    }

    public function edit(Faq $faq): View
    {
        return view('admin.website.faqs.edit', compact('faq'));
    }

    public function update(FaqRequest $request, Faq $faq): RedirectResponse
    {
        $faq->update($request->validated());

        return to_route('admin.faqs.index')
            ->with('success', 'FAQ updated successfully.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return to_route('admin.faqs.index')
            ->with('success', 'FAQ deleted successfully.');
    }

    public function toggleStatus(Faq $faq): RedirectResponse
    {
        $faq->update(['status' => ! $faq->status]);

        return back()->with('success', 'FAQ status updated successfully.');
    }
}
