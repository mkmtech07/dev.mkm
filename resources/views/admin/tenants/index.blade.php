@extends('layouts.admin')

@section('title', 'Tenants')
@section('page-title', 'Client Demos')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Client demo tenants</h2>
            <p class="text-secondary mb-0">Manage lightweight demo sites by subdomain or custom domain.</p>
        </div>
        @if(auth()->user()->hasPermission('tenants.create'))
            <a class="btn btn-primary" href="{{ route('admin.tenants.create') }}">Add tenant</a>
        @endif
    </div>

    <div class="card content-card">
        <div class="card-header">
            <form class="row g-2" method="GET" action="{{ route('admin.tenants.index') }}">
                <div class="col-md-5">
                    <label class="visually-hidden" for="search">Search tenants</label>
                    <input class="form-control" id="search" name="search" type="search" value="{{ $search }}" placeholder="Search name, slug, domain, or email">
                </div>
                <div class="col-sm-4 col-md-2">
                    <label class="visually-hidden" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All statuses</option>
                        @foreach(\App\Models\Tenant::STATUSES as $option)
                            <option value="{{ $option }}" @selected($status === $option)>{{ str($option)->title() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4 col-md-2">
                    <label class="visually-hidden" for="demo">Demo</label>
                    <select class="form-select" id="demo" name="demo">
                        <option value="">All tenants</option>
                        <option value="1" @selected($demo === '1')>Demo only</option>
                        <option value="0" @selected($demo === '0')>Production only</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                </div>
                @if($search !== '' || $status !== '' || $demo !== '')
                    <div class="col-auto">
                        <a class="btn btn-light" href="{{ route('admin.tenants.index') }}">Clear</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Tenant</th>
                        <th scope="col">Domain</th>
                        <th scope="col">Client</th>
                        <th scope="col">Status</th>
                        <th class="text-end" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $tenant->name }}</div>
                                <div class="text-secondary small">{{ $tenant->slug }}</div>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @if($tenant->isDefault())
                                        <span class="badge text-bg-primary">Default</span>
                                    @endif
                                    @if($tenant->is_demo)
                                        <span class="badge text-bg-info">Demo</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <a class="small" href="{{ $tenant->publicUrl() }}" target="_blank" rel="noopener">{{ $tenant->publicUrl() }}</a>
                                @if($tenant->custom_domain)
                                    <div class="text-secondary small">{{ $tenant->custom_domain }}</div>
                                @elseif($tenant->subdomain)
                                    <div class="text-secondary small">{{ $tenant->subdomain }}</div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $tenant->client_name ?: 'Not assigned' }}</div>
                                @if($tenant->client_email)
                                    <a class="small" href="mailto:{{ $tenant->client_email }}">{{ $tenant->client_email }}</a>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $tenant->statusBadgeClass() }}">{{ $tenant->statusLabel() }}</span>
                                @if($tenant->demo_expires_at)
                                    <div class="small text-secondary">Expires {{ $tenant->demo_expires_at->toFormattedDateString() }}</div>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                @if(auth()->user()->hasPermission('tenants.switch'))
                                    <form class="d-inline" method="POST" action="{{ route('admin.tenants.switch') }}">
                                        @csrf
                                        <input name="tenant_id" type="hidden" value="{{ $tenant->id }}">
                                        <button class="btn btn-sm btn-outline-secondary" type="submit">Switch</button>
                                    </form>
                                @endif
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.tenants.show', $tenant) }}">View</a>
                                @if(auth()->user()->hasPermission('tenants.edit'))
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.tenants.edit', $tenant) }}">Edit</a>
                                @endif
                                @if(!$tenant->isDefault() && auth()->user()->hasPermission('tenants.delete'))
                                    <form class="d-inline" method="POST" action="{{ route('admin.tenants.destroy', $tenant) }}" onsubmit="return confirm('Delete this tenant? Content remains in the database but this tenant will be removed.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-5 text-center text-secondary" colspan="5">No tenants found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tenants->hasPages())
            <div class="card-footer py-3">
                {{ $tenants->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
