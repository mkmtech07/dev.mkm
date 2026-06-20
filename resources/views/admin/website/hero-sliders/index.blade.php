@extends('layouts.admin')

@section('title', 'Hero Sliders')
@section('page-title', 'Hero Sliders')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Website hero slides</h2>
            <p class="text-secondary mb-0">Manage the slides shown at the top of the public homepage.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.hero-sliders.create') }}">Add hero slider</a>
    </div>

    <div class="card content-card">
        <div class="card-header">
            <form class="row g-2" method="GET" action="{{ route('admin.hero-sliders.index') }}">
                <div class="col-sm-8 col-lg-5">
                    <label class="visually-hidden" for="search">Search sliders</label>
                    <input
                        class="form-control"
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        placeholder="Search title or subtitle"
                    >
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                </div>
                @if ($search !== '')
                    <div class="col-auto">
                        <a class="btn btn-light" href="{{ route('admin.hero-sliders.index') }}">Clear</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Slider</th>
                        <th scope="col">Status</th>
                        <th scope="col">Order</th>
                        <th class="text-end" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($heroSliders as $heroSlider)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if ($heroSlider->image)
                                        <img
                                            class="rounded object-fit-cover"
                                            src="{{ asset($heroSlider->image) }}"
                                            alt=""
                                            width="88"
                                            height="56"
                                        >
                                    @else
                                        <div class="d-grid place-items-center rounded bg-light text-secondary" style="width: 88px; height: 56px; place-items: center;">
                                            No image
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $heroSlider->title }}</div>
                                        <div class="text-secondary small text-truncate" style="max-width: 32rem;">
                                            {{ $heroSlider->subtitle }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($heroSlider->status)
                                    <span class="badge text-bg-success">Active</span>
                                @else
                                    <span class="badge text-bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $heroSlider->sort_order }}</td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.hero-sliders.edit', $heroSlider) }}">
                                    Edit
                                </a>
                                <form
                                    class="d-inline"
                                    method="POST"
                                    action="{{ route('admin.hero-sliders.destroy', $heroSlider) }}"
                                    onsubmit="return confirm('Delete this hero slider?')"
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
                                {{ $search !== '' ? 'No hero sliders match your search.' : 'No hero sliders have been created yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($heroSliders->hasPages())
            <div class="card-footer py-3">
                {{ $heroSliders->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
