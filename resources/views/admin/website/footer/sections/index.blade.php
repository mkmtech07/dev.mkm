@extends('layouts.admin')
@section('title', 'Footer Sections')
@section('page-title', 'Footer Sections')
@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div><h2 class="h5 mb-1">Footer sections</h2><p class="text-secondary mb-0">Manage the ordered columns shown in the public footer.</p></div>
        <a class="btn btn-primary" href="{{ route('admin.website.footer.sections.create') }}">Add section</a>
    </div>
    <div class="card content-card">
        <div class="card-header">
            <form class="row g-2" method="GET" action="{{ route('admin.website.footer.sections.index') }}">
                <div class="col-sm-8 col-lg-5"><label class="visually-hidden" for="search">Search sections</label><input class="form-control" id="search" name="search" type="search" value="{{ $search }}" placeholder="Search title, type, or content"></div>
                <div class="col-auto"><button class="btn btn-outline-primary" type="submit">Search</button></div>
                @if ($search !== '') <div class="col-auto"><a class="btn btn-light" href="{{ route('admin.website.footer.sections.index') }}">Clear</a></div> @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Section</th><th>Type</th><th>Links</th><th>Order</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse ($sections as $section)
                        <tr>
                            <td><div class="fw-semibold">{{ $section->title }}</div><div class="small text-secondary text-truncate" style="max-width: 28rem;">{{ $section->content ?: 'No content' }}</div></td>
                            <td><span class="badge text-bg-light text-capitalize">{{ $section->type }}</span></td>
                            <td>{{ $section->links_count }}</td><td>{{ $section->sort_order }}</td>
                            <td><form method="POST" action="{{ route('admin.website.footer.sections.toggle-status', $section) }}">@csrf @method('PATCH')<button class="btn btn-sm {{ $section->status ? 'btn-success' : 'btn-outline-secondary' }}" type="submit">{{ $section->status ? 'Active' : 'Inactive' }}</button></form></td>
                            <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.website.footer.sections.edit', $section) }}">Edit</a> <form class="d-inline" method="POST" action="{{ route('admin.website.footer.sections.destroy', $section) }}" onsubmit="return confirm('Delete this footer section?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form></td>
                        </tr>
                    @empty <tr><td class="py-5 text-center text-secondary" colspan="6">{{ $search !== '' ? 'No sections match your search.' : 'No footer sections have been created yet.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($sections->hasPages()) <div class="card-footer py-3">{{ $sections->onEachSide(1)->links('pagination::bootstrap-5') }}</div> @endif
    </div>
@endsection
