<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchemaMarkupRequest;
use App\Models\SchemaMarkup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchemaMarkupController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $type = in_array($request->query('type'), SchemaMarkup::TYPES, true) ? $request->query('type') : '';
        $schemaMarkups = SchemaMarkup::query()
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")->orWhere('type', 'like', "%{$search}%");
            }))
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return view('admin.website.seo.schema.index', compact('schemaMarkups', 'search', 'type'));
    }

    public function create(): View
    {
        return view('admin.website.seo.schema.create', [
            'schemaMarkup' => new SchemaMarkup(['type' => 'Organization', 'status' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(SchemaMarkupRequest $request): RedirectResponse
    {
        SchemaMarkup::create($this->data($request));

        return to_route('admin.website.seo.schema.index')->with('success', 'Schema markup created successfully.');
    }

    public function edit(SchemaMarkup $schemaMarkup): View
    {
        return view('admin.website.seo.schema.edit', compact('schemaMarkup'));
    }

    public function update(SchemaMarkupRequest $request, SchemaMarkup $schemaMarkup): RedirectResponse
    {
        $schemaMarkup->update($this->data($request));

        return to_route('admin.website.seo.schema.index')->with('success', 'Schema markup updated successfully.');
    }

    public function destroy(SchemaMarkup $schemaMarkup): RedirectResponse
    {
        $schemaMarkup->delete();

        return back()->with('success', 'Schema markup deleted successfully.');
    }

    public function toggleStatus(SchemaMarkup $schemaMarkup): RedirectResponse
    {
        $schemaMarkup->update(['status' => ! $schemaMarkup->status]);

        return back()->with('success', 'Schema markup status updated successfully.');
    }

    /** @return array<string, mixed> */
    private function data(SchemaMarkupRequest $request): array
    {
        $data = $request->validated();
        $data['schema_json'] = json_encode(
            json_decode($data['schema_json'], true, 512, JSON_THROW_ON_ERROR),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
        $data['sort_order'] ??= 0;

        return $data;
    }
}
