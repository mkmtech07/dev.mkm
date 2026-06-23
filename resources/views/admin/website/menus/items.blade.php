@extends('layouts.admin')

@section('title', 'Menu Items')
@section('page-title', 'Menu Items')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <a class="small text-decoration-none" href="{{ route('admin.menus.index') }}">&larr; Menus</a>
            <h2 class="h5 mt-2 mb-1">{{ $menu->name }}</h2>
            <div class="d-flex gap-2">
                <span class="badge text-bg-light text-capitalize">{{ $menu->location }}</span>
                <span class="badge {{ $menu->status ? 'text-bg-success' : 'text-bg-secondary' }}">
                    {{ $menu->status ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('admin.menus.edit', $menu) }}">Edit menu details</a>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-xl-4">
            <form method="POST" action="{{ $menuItem->exists ? route('admin.menus.items.update', [$menu, $menuItem]) : route('admin.menus.items.store', $menu) }}">
                @csrf
                @if ($menuItem->exists)
                    @method('PUT')
                @endif

                <div class="card content-card">
                    <div class="card-header">
                        <h3 class="h6 mb-1">{{ $menuItem->exists ? 'Edit menu item' : 'Add menu item' }}</h3>
                        <p class="small text-secondary mb-0">Choose the destination and display behavior.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                            <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" type="text" value="{{ old('title', $menuItem->title) }}" maxlength="255" required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="type">Item type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required data-menu-type>
                                @foreach (['page' => 'Dynamic Page', 'blog' => 'Blog', 'blog_category' => 'Blog Category', 'custom_url' => 'Custom URL'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type', $menuItem->type) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3" data-type-field="page">
                            <label class="form-label" for="page_id">Page <span class="text-danger">*</span></label>
                            <select class="form-select @error('page_id') is-invalid @enderror" id="page_id" name="page_id">
                                <option value="">Select a page</option>
                                @foreach ($pages as $page)
                                    <option value="{{ $page->id }}" @selected((int) old('page_id', $menuItem->page_id) === $page->id)>{{ $page->title }}</option>
                                @endforeach
                            </select>
                            @error('page_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3" data-type-field="blog">
                            <label class="form-label" for="blog_id">Blog post <span class="text-danger">*</span></label>
                            <select class="form-select @error('blog_id') is-invalid @enderror" id="blog_id" name="blog_id">
                                <option value="">Select a blog post</option>
                                @foreach ($blogs as $blog)
                                    <option value="{{ $blog->id }}" @selected((int) old('blog_id', $menuItem->blog_id) === $blog->id)>{{ $blog->title }}</option>
                                @endforeach
                            </select>
                            @error('blog_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3" data-type-field="blog_category">
                            <label class="form-label" for="blog_category_id">Blog category <span class="text-danger">*</span></label>
                            <select class="form-select @error('blog_category_id') is-invalid @enderror" id="blog_category_id" name="blog_category_id">
                                <option value="">Select a blog category</option>
                                @foreach ($blogCategories as $blogCategory)
                                    <option value="{{ $blogCategory->id }}" @selected((int) old('blog_category_id', $menuItem->blog_category_id) === $blogCategory->id)>{{ $blogCategory->name }}</option>
                                @endforeach
                            </select>
                            @error('blog_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3" data-type-field="custom_url">
                            <label class="form-label" for="url">Custom URL <span class="text-danger">*</span></label>
                            <input class="form-control @error('url') is-invalid @enderror" id="url" name="url" type="text" value="{{ old('url', $menuItem->url) }}" maxlength="2048" placeholder="/contact or https://example.com">
                            <div class="form-text">Use an internal path or a full web URL.</div>
                            @error('url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="parent_id">Parent item</label>
                            <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                <option value="">None (top level)</option>
                                @foreach ($parentItems as $parentItem)
                                    <option value="{{ $parentItem->id }}" @selected((int) old('parent_id', $menuItem->parent_id) === $parentItem->id)>
                                        {{ $parentItem->parent_id ? '— ' : '' }}{{ $parentItem->title }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Child items appear in a dropdown.</div>
                            @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-7">
                                <label class="form-label" for="target">Open in</label>
                                <select class="form-select @error('target') is-invalid @enderror" id="target" name="target" required>
                                    <option value="_self" @selected(old('target', $menuItem->target) === '_self')>Same tab</option>
                                    <option value="_blank" @selected(old('target', $menuItem->target) === '_blank')>New tab</option>
                                </select>
                                @error('target') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-sm-5">
                                <label class="form-label" for="sort_order">Order</label>
                                <input class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $menuItem->sort_order ?? 0) }}" min="0" required>
                                @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="icon">Icon class</label>
                            <input class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" type="text" value="{{ old('icon', $menuItem->icon) }}" maxlength="255" placeholder="Optional CSS icon class">
                            @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <input name="status" type="hidden" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="item_status" name="status" type="checkbox" value="1" @checked(old('status', $menuItem->status ?? true))>
                            <label class="form-check-label" for="item_status">Active</label>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex gap-2 py-3">
                        <button class="btn btn-primary flex-grow-1" type="submit">{{ $menuItem->exists ? 'Save item' : 'Add item' }}</button>
                        @if ($menuItem->exists)
                            <a class="btn btn-light" href="{{ route('admin.menus.items.index', $menu) }}">Cancel</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="col-xl-8">
            <div class="card content-card">
                <div class="card-header">
                    <form class="row g-2" method="GET" action="{{ route('admin.menus.items.index', $menu) }}">
                        <div class="col-sm-8">
                            <label class="visually-hidden" for="item-search">Search menu items</label>
                            <input class="form-control" id="item-search" name="search" type="search" value="{{ $search }}" placeholder="Search title, URL, or icon">
                        </div>
                        <div class="col-auto"><button class="btn btn-outline-primary" type="submit">Search</button></div>
                        @if ($search !== '')
                            <div class="col-auto"><a class="btn btn-light" href="{{ route('admin.menus.items.index', $menu) }}">Clear</a></div>
                        @endif
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Item</th>
                                <th scope="col">Type / Parent</th>
                                <th scope="col">Order</th>
                                <th scope="col">Status</th>
                                <th class="text-end" scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($menuItems as $menuItemRow)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $menuItemRow->title }}</div>
                                        <div class="small text-secondary text-truncate" style="max-width: 18rem;">
                                            @switch($menuItemRow->type)
                                                @case('page') {{ $menuItemRow->page?->title ?: 'Missing page' }} @break
                                                @case('blog') {{ $menuItemRow->blog?->title ?: 'Missing blog post' }} @break
                                                @case('blog_category') {{ $menuItemRow->blogCategory?->name ?: 'Missing category' }} @break
                                                @default {{ $menuItemRow->url }}
                                            @endswitch
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-light">{{ str($menuItemRow->type)->replace('_', ' ')->title() }}</span>
                                        <div class="small text-secondary mt-1">{{ $menuItemRow->parent?->title ?: 'Top level' }}</div>
                                    </td>
                                    <td>{{ $menuItemRow->sort_order }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.menus.items.toggle-status', [$menu, $menuItemRow]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm {{ $menuItemRow->status ? 'btn-success' : 'btn-outline-secondary' }}" type="submit">
                                                {{ $menuItemRow->status ? 'Active' : 'Inactive' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.menus.items.edit', [$menu, $menuItemRow]) }}">Edit</a>
                                        <form class="d-inline" method="POST" action="{{ route('admin.menus.items.destroy', [$menu, $menuItemRow]) }}" onsubmit="return confirm('Delete this menu item? Its direct children will become top-level items.')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="py-5 text-center text-secondary" colspan="5">
                                        {{ $search !== '' ? 'No menu items match your search.' : 'No items have been added to this menu yet.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($menuItems->hasPages())
                    <div class="card-footer py-3">{{ $menuItems->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const typeSelect = document.querySelector('[data-menu-type]');
        const typeFields = document.querySelectorAll('[data-type-field]');

        const updateTypeFields = () => {
            typeFields.forEach((field) => {
                const active = field.dataset.typeField === typeSelect?.value;
                field.classList.toggle('d-none', ! active);
                field.querySelectorAll('input, select').forEach((input) => {
                    input.disabled = ! active;
                });
            });
        };

        typeSelect?.addEventListener('change', updateTypeFields);
        updateTypeFields();
    </script>
@endpush
