@extends('layouts.admin')
@section('title', 'Footer Social Links')
@section('page-title', 'Footer Social Links')
@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4"><div><h2 class="h5 mb-1">Footer social links</h2><p class="text-secondary mb-0">Manage social profiles displayed in the footer.</p></div><a class="btn btn-primary" href="{{ route('admin.website.footer.social.create') }}">Add social link</a></div>
    <div class="card content-card">
        <div class="card-header"><form class="row g-2" method="GET" action="{{ route('admin.website.footer.social.index') }}"><div class="col-sm-8 col-lg-5"><label class="visually-hidden" for="search">Search social links</label><input class="form-control" id="search" name="search" type="search" value="{{ $search }}" placeholder="Search platform, URL, or icon"></div><div class="col-auto"><button class="btn btn-outline-primary">Search</button></div>@if ($search !== '')<div class="col-auto"><a class="btn btn-light" href="{{ route('admin.website.footer.social.index') }}">Clear</a></div>@endif</form></div>
        <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Platform</th><th>URL</th><th>Target</th><th>Order</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
            @forelse ($socialLinks as $socialLink)
                <tr><td><div class="fw-semibold">@if ($socialLink->icon)<i class="{{ $socialLink->icon }} me-1" aria-hidden="true"></i>@endif {{ $socialLink->platform }}</div></td><td><div class="text-truncate" style="max-width: 24rem;">{{ $socialLink->url }}</div></td><td>{{ $socialLink->target === '_blank' ? 'New tab' : 'Same tab' }}</td><td>{{ $socialLink->sort_order }}</td>
                <td><form method="POST" action="{{ route('admin.website.footer.social.toggle-status', $socialLink) }}">@csrf @method('PATCH')<button class="btn btn-sm {{ $socialLink->status ? 'btn-success' : 'btn-outline-secondary' }}">{{ $socialLink->status ? 'Active' : 'Inactive' }}</button></form></td>
                <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.website.footer.social.edit', $socialLink) }}">Edit</a> <form class="d-inline" method="POST" action="{{ route('admin.website.footer.social.destroy', $socialLink) }}" onsubmit="return confirm('Delete this social link?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
            @empty <tr><td class="py-5 text-center text-secondary" colspan="6">{{ $search !== '' ? 'No social links match your search.' : 'No footer social links have been created yet.' }}</td></tr>
            @endforelse
        </tbody></table></div>
        @if ($socialLinks->hasPages())<div class="card-footer py-3">{{ $socialLinks->onEachSide(1)->links('pagination::bootstrap-5') }}</div>@endif
    </div>
@endsection
