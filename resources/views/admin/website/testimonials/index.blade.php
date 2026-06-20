@extends('layouts.admin')

@section('title', 'Testimonials')
@section('page-title', 'Testimonials')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Client testimonials</h2>
            <p class="text-secondary mb-0">Manage the customer reviews displayed on the public homepage.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.testimonials.create') }}">Add testimonial</a>
    </div>

    <div class="card content-card">
        <div class="card-header">
            <form class="row g-2" method="GET" action="{{ route('admin.testimonials.index') }}">
                <div class="col-sm-8 col-lg-5">
                    <label class="visually-hidden" for="search">Search testimonials</label>
                    <input
                        class="form-control"
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        placeholder="Search client, company, or review"
                    >
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                </div>
                @if ($search !== '')
                    <div class="col-auto">
                        <a class="btn btn-light" href="{{ route('admin.testimonials.index') }}">Clear</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Client</th>
                        <th scope="col">Review</th>
                        <th scope="col">Rating</th>
                        <th scope="col">Status</th>
                        <th scope="col">Order</th>
                        <th class="text-end" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($testimonials as $testimonial)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if ($testimonial->image)
                                        <img
                                            class="rounded-circle object-fit-cover"
                                            src="{{ asset($testimonial->image) }}"
                                            alt=""
                                            width="52"
                                            height="52"
                                        >
                                    @else
                                        <div
                                            class="d-grid rounded-circle bg-light text-primary fw-semibold"
                                            style="width: 52px; height: 52px; place-items: center;"
                                            aria-hidden="true"
                                        >
                                            {{ strtoupper(substr($testimonial->client_name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-semibold">{{ $testimonial->client_name }}</span>
                                            @if ($testimonial->featured)
                                                <span class="badge text-bg-warning">Featured</span>
                                            @endif
                                        </div>
                                        @if ($testimonial->designation || $testimonial->company)
                                            <div class="text-secondary small">
                                                {{ collect([$testimonial->designation, $testimonial->company])->filter()->join(', ') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-secondary text-truncate" style="max-width: 24rem;">
                                    {{ $testimonial->review }}
                                </div>
                            </td>
                            <td class="text-nowrap" aria-label="{{ $testimonial->rating }} out of 5 stars">
                                @for ($star = 1; $star <= 5; $star++)
                                    <span class="{{ $star <= $testimonial->rating ? 'text-warning' : 'text-secondary opacity-25' }}">&#9733;</span>
                                @endfor
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.testimonials.toggle-status', $testimonial) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        class="btn btn-sm {{ $testimonial->status ? 'btn-success' : 'btn-outline-secondary' }}"
                                        type="submit"
                                        title="Toggle status"
                                    >
                                        {{ $testimonial->status ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td>{{ $testimonial->sort_order }}</td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.testimonials.edit', $testimonial) }}">
                                    Edit
                                </a>
                                <form
                                    class="d-inline"
                                    method="POST"
                                    action="{{ route('admin.testimonials.destroy', $testimonial) }}"
                                    onsubmit="return confirm('Delete this testimonial?')"
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
                                {{ $search !== '' ? 'No testimonials match your search.' : 'No testimonials have been created yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($testimonials->hasPages())
            <div class="card-footer py-3">
                {{ $testimonials->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
