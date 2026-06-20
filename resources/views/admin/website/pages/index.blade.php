@extends('layouts.admin')

@section('title', 'Pages')
@section('page-title', 'Pages')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Dynamic pages</h2>
            <p class="text-secondary mb-0">Create and manage content pages for the public website.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.pages.create') }}">Add page</a>
    </div>

    <div class="card content-card">
        <div class="card-header">
            <form class="row g-2" method="GET" action="{{ route('admin.pages.index') }}">
                <div class="col-sm-8 col-lg-5">
                    <label class="visually-hidden" for="search">Search pages</label>
                    <input
                        class="form-control"
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        placeholder="Search title, slug, or meta title"
                    >
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                </div>
                @if ($search !== '')
                    <div class="col-auto">
                        <a class="btn btn-light" href="{{ route('admin.pages.index') }}">Clear</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Page</th>
                        <th scope="col">Type</th>
                        <th scope="col">Menu</th>
                        <th scope="col">Status</th>
                        <th scope="col">Order</th>
                        <th class="text-end" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pages as $page)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if ($page->featured_image)
                                        <img
                                            class="rounded object-fit-cover"
                                            src="{{ asset($page->featured_image) }}"
                                            alt=""
                                            width="64"
                                            height="48"
                                        >
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $page->title }}</div>
                                        <a class="small text-secondary" href="{{ url($page->slug) }}" target="_blank" rel="noopener">
                                            /{{ $page->slug }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge text-bg-light text-capitalize">{{ $page->page_type }}</span>
                                <div class="small text-secondary mt-1">{{ $page->template }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $page->show_in_menu ? 'text-bg-primary' : 'text-bg-light' }}">
                                    {{ $page->show_in_menu ? 'Shown' : 'Hidden' }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.pages.toggle-status', $page) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        class="btn btn-sm {{ $page->status ? 'btn-success' : 'btn-outline-secondary' }}"
                                        type="submit"
                                        title="Toggle status"
                                    >
                                        {{ $page->status ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td>{{ $page->sort_order }}</td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.pages.edit', $page) }}">Edit</a>
                                <form
                                    class="d-inline"
                                    method="POST"
                                    action="{{ route('admin.pages.destroy', $page) }}"
                                    onsubmit="return confirm('Delete this page?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-5 text-center text-secondary" colspan="6">
                                {{ $search !== '' ? 'No pages match your search.' : 'No dynamic pages have been created yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pages->hasPages())
            <div class="card-footer py-3">
                {{ $pages->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
