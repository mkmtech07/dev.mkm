@extends('layouts.admin')

@section('title', $tenant->name)
@section('page-title', 'Tenant Details')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">{{ $tenant->name }}</h2>
            <p class="text-secondary mb-0">{{ $tenant->is_demo ? 'Client demo tenant' : 'Tenant' }} for {{ $tenant->publicUrl() }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(auth()->user()->hasPermission('tenants.switch'))
                <form method="POST" action="{{ route('admin.tenants.switch') }}">
                    @csrf
                    <input name="tenant_id" type="hidden" value="{{ $tenant->id }}">
                    <button class="btn btn-outline-secondary" type="submit">Switch to tenant</button>
                </form>
            @endif
            @if(auth()->user()->hasPermission('tenants.settings'))
                <a class="btn btn-outline-primary" href="{{ route('admin.tenants.settings.edit', $tenant) }}">Settings</a>
            @endif
            @if(auth()->user()->hasPermission('tenants.edit'))
                <a class="btn btn-primary" href="{{ route('admin.tenants.edit', $tenant) }}">Edit</a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card content-card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-1">Overview</h2>
                    <p class="text-secondary small mb-0">Identity and public routing.</p>
                </div>
                <div class="card-body p-4">
                    <dl class="row mb-0">
                        <dt class="col-5">Status</dt>
                        <dd class="col-7"><span class="badge {{ $tenant->statusBadgeClass() }}">{{ $tenant->statusLabel() }}</span></dd>
                        <dt class="col-5">Slug</dt>
                        <dd class="col-7">{{ $tenant->slug }}</dd>
                        <dt class="col-5">Subdomain</dt>
                        <dd class="col-7">{{ $tenant->subdomain ?: 'Not set' }}</dd>
                        <dt class="col-5">Custom domain</dt>
                        <dd class="col-7">{{ $tenant->custom_domain ?: 'Not set' }}</dd>
                        <dt class="col-5">Demo</dt>
                        <dd class="col-7">{{ $tenant->is_demo ? 'Yes' : 'No' }}</dd>
                        <dt class="col-5">Expires</dt>
                        <dd class="col-7">{{ $tenant->demo_expires_at?->toDayDateTimeString() ?: 'No expiry' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card content-card">
                <div class="card-header">
                    <h2 class="h5 mb-1">Client</h2>
                    <p class="text-secondary small mb-0">Contact attached to this demo.</p>
                </div>
                <div class="card-body p-4">
                    <div class="mb-2"><strong>{{ $tenant->client_name ?: 'Not assigned' }}</strong></div>
                    @if($tenant->client_email)
                        <div><a href="mailto:{{ $tenant->client_email }}">{{ $tenant->client_email }}</a></div>
                    @endif
                    @if($tenant->client_phone)
                        <div class="text-secondary">{{ $tenant->client_phone }}</div>
                    @endif
                    @if($tenant->notes)
                        <hr>
                        <div class="text-secondary small">{{ $tenant->notes }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card content-card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-1">Content counts</h2>
                    <p class="text-secondary small mb-0">Tenant-owned rows in tenant-aware modules.</p>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        @foreach($counts as $count)
                            <div class="col-sm-6 col-lg-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-secondary small">{{ $count['label'] }}</div>
                                    <div class="fs-4 fw-semibold">{{ number_format($count['count']) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card content-card">
                <div class="card-header">
                    <h2 class="h5 mb-1">Launch checks</h2>
                    <p class="text-secondary small mb-0">Useful URLs and DNS reminders.</p>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Public URL</label>
                        <div class="input-group">
                            <input class="form-control" type="text" value="{{ $tenant->publicUrl() }}" readonly>
                            <a class="btn btn-outline-primary" href="{{ $tenant->publicUrl() }}" target="_blank" rel="noopener">Open</a>
                        </div>
                    </div>
                    <div class="alert alert-info mb-0">
                        For subdomains, create a wildcard DNS record or a record for this subdomain pointing to the app server. For custom domains, point the domain to the same server and add it above.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
