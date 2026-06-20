@extends('layouts.admin')

@section('title', 'FAQs')
@section('page-title', 'FAQs')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Frequently asked questions</h2>
            <p class="text-secondary mb-0">Manage the questions displayed on the public homepage.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.faqs.create') }}">Add FAQ</a>
    </div>

    <div class="card content-card">
        <div class="card-header">
            <form class="row g-2" method="GET" action="{{ route('admin.faqs.index') }}">
                <div class="col-sm-8 col-lg-5">
                    <label class="visually-hidden" for="search">Search FAQs</label>
                    <input
                        class="form-control"
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        placeholder="Search question, answer, or category"
                    >
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                </div>
                @if ($search !== '')
                    <div class="col-auto">
                        <a class="btn btn-light" href="{{ route('admin.faqs.index') }}">Clear</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Question</th>
                        <th scope="col">Category</th>
                        <th scope="col">Status</th>
                        <th scope="col">Order</th>
                        <th class="text-end" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($faqs as $faq)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $faq->question }}</div>
                                <div class="text-secondary small text-truncate" style="max-width: 38rem;">
                                    {{ $faq->answer }}
                                </div>
                            </td>
                            <td>{{ $faq->category ?: '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.faqs.toggle-status', $faq) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        class="btn btn-sm {{ $faq->status ? 'btn-success' : 'btn-outline-secondary' }}"
                                        type="submit"
                                        title="Toggle status"
                                    >
                                        {{ $faq->status ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td>{{ $faq->sort_order }}</td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.faqs.edit', $faq) }}">Edit</a>
                                <form
                                    class="d-inline"
                                    method="POST"
                                    action="{{ route('admin.faqs.destroy', $faq) }}"
                                    onsubmit="return confirm('Delete this FAQ?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-5 text-center text-secondary" colspan="5">
                                {{ $search !== '' ? 'No FAQs match your search.' : 'No FAQs have been created yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($faqs->hasPages())
            <div class="card-footer py-3">
                {{ $faqs->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
