@extends('layouts.admin')

@section('title', 'Gallery')
@section('page-title', 'Gallery')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Gallery images</h2>
            <p class="text-secondary mb-0">Manage the images displayed on the public gallery page.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.gallery.create') }}">Add gallery image</a>
    </div>

    <div class="card content-card mb-4">
        <div class="card-body p-3">
            <form class="row g-2 align-items-end" method="GET" action="{{ route('admin.gallery.index') }}">
                <div class="col-sm-6 col-lg-5">
                    <label class="form-label" for="search">Search</label>
                    <input
                        class="form-control"
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        placeholder="Search title or alt text"
                    >
                </div>
                <div class="col-sm-6 col-lg-3">
                    <label class="form-label" for="category">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All categories</option>
                        @foreach ($categories as $categoryOption)
                            <option value="{{ $categoryOption }}" @selected($category === $categoryOption)>
                                {{ $categoryOption }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                </div>
                @if ($search !== '' || $category !== '')
                    <div class="col-auto">
                        <a class="btn btn-light" href="{{ route('admin.gallery.index') }}">Clear</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    @if ($galleries->isEmpty())
        <div class="card content-card">
            <div class="card-body py-5 text-center text-secondary">
                {{ $search !== '' || $category !== '' ? 'No gallery images match these filters.' : 'No gallery images have been added yet.' }}
            </div>
        </div>
    @else
        <div class="row g-4">
            @foreach ($galleries as $gallery)
                <div class="col-sm-6 col-xl-4">
                    <article class="card content-card h-100 overflow-hidden">
                        <img
                            class="card-img-top object-fit-cover"
                            src="{{ asset($gallery->image) }}"
                            alt="{{ $gallery->alt_text ?: $gallery->title }}"
                            style="height: 220px;"
                        >
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                <div>
                                    @if ($gallery->category)
                                        <div class="text-primary text-uppercase fw-semibold small mb-1">{{ $gallery->category }}</div>
                                    @endif
                                    <h2 class="h5 mb-0">{{ $gallery->title }}</h2>
                                </div>
                                <span class="badge text-bg-light">#{{ $gallery->sort_order }}</span>
                            </div>

                            @if ($gallery->alt_text)
                                <p class="small text-secondary mb-3">Alt: {{ $gallery->alt_text }}</p>
                            @endif

                            <div class="d-flex flex-wrap align-items-center gap-2 mt-auto">
                                <form method="POST" action="{{ route('admin.gallery.toggle-status', $gallery) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        class="btn btn-sm {{ $gallery->status ? 'btn-success' : 'btn-outline-secondary' }}"
                                        type="submit"
                                        title="Toggle status"
                                    >
                                        {{ $gallery->status ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>

                                <a class="btn btn-sm btn-outline-primary ms-auto" href="{{ route('admin.gallery.edit', $gallery) }}">
                                    Edit
                                </a>
                                <form
                                    method="POST"
                                    action="{{ route('admin.gallery.destroy', $gallery) }}"
                                    onsubmit="return confirm('Delete this gallery image?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>

        @if ($galleries->hasPages())
            <div class="mt-4">
                {{ $galleries->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @endif
@endsection
