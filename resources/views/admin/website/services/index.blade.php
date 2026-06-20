@extends('layouts.admin')

@section('title', 'Services')
@section('page-title', 'Services')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Website services</h2>
            <p class="text-secondary mb-0">Manage the services displayed on the public website.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.services.create') }}">Add service</a>
    </div>

    <div class="card content-card">
        <div class="card-header">
            <form class="row g-2" method="GET" action="{{ route('admin.services.index') }}">
                <div class="col-sm-8 col-lg-5">
                    <label class="visually-hidden" for="search">Search services</label>
                    <input
                        class="form-control"
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        placeholder="Search title, slug, or description"
                    >
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                </div>
                @if ($search !== '')
                    <div class="col-auto">
                        <a class="btn btn-light" href="{{ route('admin.services.index') }}">Clear</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Service</th>
                        <th scope="col">Status</th>
                        <th scope="col">Order</th>
                        <th class="text-end" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if ($service->image)
                                        <img
                                            class="rounded object-fit-cover"
                                            src="{{ asset($service->image) }}"
                                            alt=""
                                            width="72"
                                            height="56"
                                        >
                                    @else
                                        <div
                                            class="d-grid rounded bg-light text-primary fw-semibold"
                                            style="width: 72px; height: 56px; place-items: center;"
                                            aria-hidden="true"
                                        >
                                            {{ $service->icon ?: '—' }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $service->title }}</div>
                                        <div class="text-secondary small">/{{ $service->slug }}</div>
                                        @if ($service->short_description)
                                            <div class="text-secondary small text-truncate" style="max-width: 30rem;">
                                                {{ $service->short_description }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($service->status)
                                    <span class="badge text-bg-success">Active</span>
                                @else
                                    <span class="badge text-bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $service->sort_order }}</td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.services.edit', $service) }}">
                                    Edit
                                </a>
                                <form
                                    class="d-inline"
                                    method="POST"
                                    action="{{ route('admin.services.destroy', $service) }}"
                                    onsubmit="return confirm('Delete this service?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-5 text-center text-secondary" colspan="4">
                                {{ $search !== '' ? 'No services match your search.' : 'No services have been created yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($services->hasPages())
            <div class="card-footer py-3">
                {{ $services->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
