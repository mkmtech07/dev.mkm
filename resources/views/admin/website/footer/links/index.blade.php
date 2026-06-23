@extends('layouts.admin')
@section('title', 'Footer Links')
@section('page-title', 'Footer Links')
@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4"><div><h2 class="h5 mb-1">Footer links</h2><p class="text-secondary mb-0">Manage ordered links within footer sections.</p></div><a class="btn btn-primary" href="{{ route('admin.website.footer.links.create') }}">Add link</a></div>
    <div class="card content-card">
        <div class="card-header"><form class="row g-2" method="GET" action="{{ route('admin.website.footer.links.index') }}">
            <div class="col-sm-6 col-lg-5"><label class="visually-hidden" for="search">Search links</label><input class="form-control" id="search" name="search" type="search" value="{{ $search }}" placeholder="Search title, URL, or icon"></div>
            <div class="col-sm-4 col-lg-3"><label class="visually-hidden" for="section">Section</label><select class="form-select" id="section" name="section"><option value="">All sections</option>@foreach ($sections as $section)<option value="{{ $section->id }}" @selected($sectionId === $section->id)>{{ $section->title }}</option>@endforeach</select></div>
            <div class="col-auto"><button class="btn btn-outline-primary">Filter</button></div>@if ($search !== '' || $sectionId > 0)<div class="col-auto"><a class="btn btn-light" href="{{ route('admin.website.footer.links.index') }}">Clear</a></div>@endif
        </form></div>
        <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Link</th><th>Section</th><th>Target</th><th>Order</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
            @forelse ($links as $link)
                <tr><td><div class="fw-semibold">{{ $link->title }}</div><div class="small text-secondary text-truncate" style="max-width: 28rem;">{{ $link->url }}</div></td><td>{{ $link->section?->title ?: 'No section' }}</td><td>{{ $link->target === '_blank' ? 'New tab' : 'Same tab' }}</td><td>{{ $link->sort_order }}</td>
                <td><form method="POST" action="{{ route('admin.website.footer.links.toggle-status', $link) }}">@csrf @method('PATCH')<button class="btn btn-sm {{ $link->status ? 'btn-success' : 'btn-outline-secondary' }}">{{ $link->status ? 'Active' : 'Inactive' }}</button></form></td>
                <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.website.footer.links.edit', $link) }}">Edit</a> <form class="d-inline" method="POST" action="{{ route('admin.website.footer.links.destroy', $link) }}" onsubmit="return confirm('Delete this footer link?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
            @empty <tr><td class="py-5 text-center text-secondary" colspan="6">{{ $search !== '' || $sectionId > 0 ? 'No links match these filters.' : 'No footer links have been created yet.' }}</td></tr>
            @endforelse
        </tbody></table></div>
        @if ($links->hasPages())<div class="card-footer py-3">{{ $links->onEachSide(1)->links('pagination::bootstrap-5') }}</div>@endif
    </div>
@endsection
