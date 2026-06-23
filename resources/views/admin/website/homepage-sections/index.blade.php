@extends('layouts.admin')

@section('title', 'Homepage Sections')
@section('page-title', 'Homepage Sections')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Homepage section builder</h2>
            <p class="text-secondary mb-0">Arrange and control the content shown on the public homepage.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.website.homepage-sections.create') }}">Add section</a>
    </div>

    <div class="card content-card">
        <div class="card-header">
            <form class="row g-2" method="GET" action="{{ route('admin.website.homepage-sections.index') }}">
                <div class="col-sm-6 col-lg-5">
                    <label class="visually-hidden" for="search">Search sections</label>
                    <input class="form-control" id="search" name="search" type="search" value="{{ $search }}" placeholder="Search title, key, or content">
                </div>
                <div class="col-sm-4 col-lg-3">
                    <label class="visually-hidden" for="type">Section type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">All types</option>
                        @foreach (\App\Models\HomepageSection::TYPES as $sectionType)
                            <option value="{{ $sectionType }}" @selected($type === $sectionType)>{{ ucfirst($sectionType) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto"><button class="btn btn-outline-primary" type="submit">Filter</button></div>
                @if ($search !== '' || $type !== '')
                    <div class="col-auto"><a class="btn btn-light" href="{{ route('admin.website.homepage-sections.index') }}">Clear</a></div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr><th>Section</th><th>Type</th><th>Key</th><th>Order</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse ($sections as $section)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if ($section->image || $section->background_image)
                                        <img class="rounded border object-fit-cover" src="{{ asset($section->image ?: $section->background_image) }}" alt="" width="58" height="44">
                                    @else
                                        <div class="rounded bg-light border d-grid place-items-center text-secondary" style="width: 58px; height: 44px;">&mdash;</div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $section->title ?: 'Untitled section' }}</div>
                                        <div class="small text-secondary text-truncate" style="max-width: 24rem;">{{ $section->subtitle ?: ($section->content ?: 'No supporting content') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge text-bg-primary text-capitalize">{{ $section->type }}</span></td>
                            <td><code>{{ $section->section_key ?: '—' }}</code></td>
                            <td>{{ $section->sort_order }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.website.homepage-sections.toggle-status', $section) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm {{ $section->status ? 'btn-success' : 'btn-outline-secondary' }}" type="submit">
                                        {{ $section->status ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.website.homepage-sections.edit', $section) }}">Edit</a>
                                <form class="d-inline" method="POST" action="{{ route('admin.website.homepage-sections.destroy', $section) }}" onsubmit="return confirm('Delete this homepage section?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-5 text-center text-secondary" colspan="6">
                                {{ $search !== '' || $type !== '' ? 'No homepage sections match your filters.' : 'No homepage sections have been created yet. The existing homepage remains active.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sections->hasPages())
            <div class="card-footer py-3">{{ $sections->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
