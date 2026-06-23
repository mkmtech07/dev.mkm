@extends('layouts.admin')
@section('title', 'Roles')
@section('page-title', 'Roles')
@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
    <div><h2 class="h5 mb-1">Access roles</h2><p class="text-secondary mb-0">Bundle permissions into reusable access levels for CMS users.</p></div>
    <a class="btn btn-primary" href="{{ route('admin.roles.create') }}">Create role</a>
</div>
<div class="card content-card">
    <div class="card-header"><form class="row g-2" method="GET" action="{{ route('admin.roles.index') }}"><div class="col-md-6"><input class="form-control" name="search" type="search" value="{{ $search }}" placeholder="Search role name, slug, or description"></div><div class="col-md-3"><select class="form-select" name="status"><option value="">All statuses</option><option value="active" @selected($status==='active')>Active</option><option value="inactive" @selected($status==='inactive')>Inactive</option></select></div><div class="col-auto"><button class="btn btn-outline-primary" type="submit">Filter</button></div>@if($search!==''||$status!=='')<div class="col-auto"><a class="btn btn-light" href="{{ route('admin.roles.index') }}">Clear</a></div>@endif</form></div>
    <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Role</th><th>Permissions</th><th>Users</th><th>Status</th><th>Updated</th><th class="text-end">Actions</th></tr></thead><tbody>
    @forelse($roles as $role)<tr><td><a class="fw-semibold text-decoration-none" href="{{ route('admin.roles.show',$role) }}">{{ $role->name }}</a><div><code>{{ $role->slug }}</code></div></td><td><span class="badge text-bg-primary">{{ $role->permissions_count }}</span></td><td>{{ $role->users_count }}</td><td><form method="POST" action="{{ route('admin.roles.toggle-status',$role) }}">@csrf @method('PATCH')<button class="btn btn-sm border-0 p-0" type="submit" @disabled($role->slug===\App\Models\Role::SUPER_ADMIN)><span class="badge {{ $role->status?'text-bg-success':'text-bg-secondary' }}">{{ $role->status?'Active':'Inactive' }}</span></button></form></td><td class="text-nowrap">{{ $role->updated_at->format('d M Y') }}</td><td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.roles.show',$role) }}">View</a> <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.roles.edit',$role) }}">Edit</a> <form class="d-inline" method="POST" action="{{ route('admin.roles.destroy',$role) }}" onsubmit="return confirm('Delete this role? Assigned access will be removed.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">Delete</button></form></td></tr>
    @empty<tr><td class="py-5 text-center text-secondary" colspan="6">No roles found. Create the first role to organize CMS permissions.</td></tr>@endforelse
    </tbody></table></div>@if($roles->hasPages())<div class="card-footer py-3">{{ $roles->links('pagination::bootstrap-5') }}</div>@endif
</div>
@endsection
