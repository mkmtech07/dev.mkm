@extends('layouts.admin')

@section('title', 'Blog Categories')
@section('page-title', 'Blog Categories')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Blog categories</h2>
            <p class="text-secondary mb-0">Organize blog content and manage its category metadata.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.blog-categories.create') }}">Add category</a>
    </div>

    <div class="card content-card">
        <div class="card-header">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                <form class="row g-2 flex-grow-1" method="GET" action="{{ route('admin.blog-categories.index') }}">
                    @if ($view !== 'all')
                        <input name="view" type="hidden" value="{{ $view }}">
                    @endif
                    <div class="col-sm-8 col-lg-6">
                        <label class="visually-hidden" for="search">Search blog categories</label>
                        <input
                            class="form-control"
                            id="search"
                            name="search"
                            type="search"
                            value="{{ $search }}"
                            placeholder="Search name, slug, or description"
                        >
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-primary" type="submit">Search</button>
                    </div>
                    @if ($search !== '')
                        <div class="col-auto">
                            <a class="btn btn-light" href="{{ route('admin.blog-categories.index', ['view' => $view]) }}">Clear</a>
                        </div>
                    @endif
                </form>

                @if ($view !== 'trashed' && $categories->isNotEmpty())
                    <button
                        class="btn btn-outline-danger align-self-start"
                        id="bulk-delete-button"
                        type="submit"
                        form="bulk-delete-form"
                        disabled
                    >
                        Delete selected
                    </button>
                @endif
            </div>

            <div class="nav nav-pills gap-2 mt-3" aria-label="Category filters">
                @foreach (['all' => 'All', 'active' => 'Active', 'inactive' => 'Inactive', 'trashed' => 'Trash'] as $filter => $label)
                    <a
                        class="nav-link py-1 px-3 {{ $view === $filter ? 'active' : '' }}"
                        href="{{ route('admin.blog-categories.index', array_filter(['view' => $filter, 'search' => $search])) }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        @error('categories')
            <div class="alert alert-danger rounded-0 mb-0">{{ $message }}</div>
        @enderror
        @error('categories.*')
            <div class="alert alert-danger rounded-0 mb-0">{{ $message }}</div>
        @enderror

        <form
            id="bulk-delete-form"
            method="POST"
            action="{{ route('admin.blog-categories.bulk-delete') }}"
            onsubmit="return confirm('Move the selected categories to trash?')"
        >
            @csrf
            @method('DELETE')
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        @if ($view !== 'trashed')
                            <th class="text-center" scope="col" style="width: 3rem;">
                                <input class="form-check-input" id="select-all-categories" type="checkbox" aria-label="Select all categories">
                            </th>
                        @endif
                        <th scope="col">Category</th>
                        <th scope="col">Description</th>
                        <th scope="col">Status</th>
                        <th scope="col">Order</th>
                        <th class="text-end" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            @if ($view !== 'trashed')
                                <td class="text-center">
                                    <input
                                        class="form-check-input category-checkbox"
                                        name="categories[]"
                                        type="checkbox"
                                        value="{{ $category->id }}"
                                        form="bulk-delete-form"
                                        aria-label="Select {{ $category->name }}"
                                    >
                                </td>
                            @endif
                            <td>
                                <div class="fw-semibold">{{ $category->name }}</div>
                                <div class="small text-secondary">/{{ $category->slug }}</div>
                            </td>
                            <td>
                                <div class="text-secondary text-truncate" style="max-width: 28rem;">
                                    {{ $category->description ?: '—' }}
                                </div>
                                @if ($category->trashed())
                                    <div class="small text-danger mt-1">Deleted {{ $category->deleted_at->diffForHumans() }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($category->trashed())
                                    <span class="badge text-bg-danger">Deleted</span>
                                @else
                                    <form method="POST" action="{{ route('admin.blog-categories.toggle-status', $category) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            class="btn btn-sm {{ $category->status ? 'btn-success' : 'btn-outline-secondary' }}"
                                            type="submit"
                                            title="Toggle status"
                                        >
                                            {{ $category->status ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td>{{ $category->sort_order }}</td>
                            <td class="text-end text-nowrap">
                                @if ($category->trashed())
                                    <form method="POST" action="{{ route('admin.blog-categories.restore', $category) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-success" type="submit">Restore</button>
                                    </form>
                                @else
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.blog-categories.edit', $category) }}">Edit</a>
                                    <form
                                        class="d-inline"
                                        method="POST"
                                        action="{{ route('admin.blog-categories.destroy', $category) }}"
                                        onsubmit="return confirm('Move this category to trash?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-5 text-center text-secondary" colspan="{{ $view === 'trashed' ? 5 : 6 }}">
                                @if ($search !== '')
                                    No blog categories match your search.
                                @elseif ($view === 'trashed')
                                    The trash is empty.
                                @else
                                    No blog categories have been created yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->hasPages())
            <div class="card-footer py-3">
                {{ $categories->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        const selectAll = document.getElementById('select-all-categories');
        const categoryCheckboxes = [...document.querySelectorAll('.category-checkbox')];
        const bulkDeleteButton = document.getElementById('bulk-delete-button');

        const updateBulkControls = () => {
            const selectedCount = categoryCheckboxes.filter((checkbox) => checkbox.checked).length;

            if (bulkDeleteButton) {
                bulkDeleteButton.disabled = selectedCount === 0;
                bulkDeleteButton.textContent = selectedCount > 0 ? `Delete selected (${selectedCount})` : 'Delete selected';
            }

            if (selectAll) {
                selectAll.checked = categoryCheckboxes.length > 0 && selectedCount === categoryCheckboxes.length;
                selectAll.indeterminate = selectedCount > 0 && selectedCount < categoryCheckboxes.length;
            }
        };

        selectAll?.addEventListener('change', () => {
            categoryCheckboxes.forEach((checkbox) => {
                checkbox.checked = selectAll.checked;
            });
            updateBulkControls();
        });

        categoryCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', updateBulkControls));
    </script>
@endpush
