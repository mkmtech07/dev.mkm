@extends('layouts.admin')

@section('title', 'About Us')
@section('page-title', 'About Us')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">About sections</h2>
            <p class="text-secondary mb-0">Only the active section is displayed on the public About page.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.about.create') }}">Add about section</a>
    </div>

    <div class="card content-card">
        <div class="card-header">
            <form class="row g-2" method="GET" action="{{ route('admin.about.index') }}">
                <div class="col-sm-8 col-lg-5">
                    <label class="visually-hidden" for="search">Search about sections</label>
                    <input
                        class="form-control"
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        placeholder="Search title or content"
                    >
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                </div>
                @if ($search !== '')
                    <div class="col-auto">
                        <a class="btn btn-light" href="{{ route('admin.about.index') }}">Clear</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Section</th>
                        <th scope="col">Status</th>
                        <th scope="col">Last updated</th>
                        <th class="text-end" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($aboutSections as $aboutSection)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if ($aboutSection->image)
                                        <img
                                            class="rounded object-fit-cover"
                                            src="{{ asset($aboutSection->image) }}"
                                            alt=""
                                            width="80"
                                            height="58"
                                        >
                                    @else
                                        <div
                                            class="d-grid rounded bg-light text-primary fw-semibold"
                                            style="width: 80px; height: 58px; place-items: center;"
                                            aria-hidden="true"
                                        >
                                            About
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $aboutSection->title }}</div>
                                        @if ($aboutSection->subtitle)
                                            <div class="text-secondary small text-truncate" style="max-width: 32rem;">
                                                {{ $aboutSection->subtitle }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($aboutSection->status)
                                    <span class="badge text-bg-success">Active</span>
                                @else
                                    <span class="badge text-bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $aboutSection->updated_at->format('M j, Y') }}</td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.about.edit', $aboutSection) }}">
                                    Edit
                                </a>
                                <form
                                    class="d-inline"
                                    method="POST"
                                    action="{{ route('admin.about.destroy', $aboutSection) }}"
                                    onsubmit="return confirm('Delete this About section?')"
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
                                {{ $search !== '' ? 'No About sections match your search.' : 'No About sections have been created yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($aboutSections->hasPages())
            <div class="card-footer py-3">
                {{ $aboutSections->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
