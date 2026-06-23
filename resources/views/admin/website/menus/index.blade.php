@extends('layouts.admin')

@section('title', 'Menus')
@section('page-title', 'Menus')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Navigation menus</h2>
            <p class="text-secondary mb-0">Build navigation groups for the header, footer, and sidebar.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.menus.create') }}">Add menu</a>
    </div>

    <div class="card content-card">
        <div class="card-header">
            <form class="row g-2" method="GET" action="{{ route('admin.menus.index') }}">
                <div class="col-sm-6 col-lg-4">
                    <label class="visually-hidden" for="search">Search menus</label>
                    <input class="form-control" id="search" name="search" type="search" value="{{ $search }}" placeholder="Search menu name">
                </div>
                <div class="col-sm-3 col-lg-2">
                    <label class="visually-hidden" for="location">Location</label>
                    <select class="form-select" id="location" name="location">
                        <option value="">All locations</option>
                        @foreach (['header' => 'Header', 'footer' => 'Footer', 'sidebar' => 'Sidebar'] as $value => $label)
                            <option value="{{ $value }}" @selected($location === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3 col-lg-2">
                    <label class="visually-hidden" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="all" @selected($status === 'all')>All statuses</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-auto"><button class="btn btn-outline-primary" type="submit">Filter</button></div>
                @if ($search !== '' || $location !== '' || $status !== 'all')
                    <div class="col-auto"><a class="btn btn-light" href="{{ route('admin.menus.index') }}">Clear</a></div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Menu</th>
                        <th scope="col">Location</th>
                        <th scope="col">Items</th>
                        <th scope="col">Status</th>
                        <th class="text-end" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($menus as $menu)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $menu->name }}</div>
                                <div class="small text-secondary">Updated {{ $menu->updated_at->diffForHumans() }}</div>
                            </td>
                            <td><span class="badge text-bg-light text-capitalize">{{ $menu->location }}</span></td>
                            <td>{{ $menu->items_count }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.menus.toggle-status', $menu) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm {{ $menu->status ? 'btn-success' : 'btn-outline-secondary' }}" type="submit">
                                        {{ $menu->status ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.menus.items.index', $menu) }}">Items</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.menus.edit', $menu) }}">Edit</a>
                                <form class="d-inline" method="POST" action="{{ route('admin.menus.destroy', $menu) }}" onsubmit="return confirm('Delete this menu and its items?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-5 text-center text-secondary" colspan="5">
                                {{ $search !== '' || $location !== '' || $status !== 'all' ? 'No menus match these filters.' : 'No menus have been created yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($menus->hasPages())
            <div class="card-footer py-3">{{ $menus->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
