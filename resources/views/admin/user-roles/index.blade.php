@extends('layouts.admin')
@section('title','User Roles')
@section('page-title','User Roles')
@section('content')
<div class="mb-4"><h2 class="h5 mb-1">CMS user access</h2><p class="text-secondary mb-0">Assign one or more active roles to each administrator or staff account.</p></div>
<div class="card content-card"><div class="card-header"><form class="row g-2" method="GET"><div class="col-md-6"><input class="form-control" name="search" type="search" value="{{ $search }}" placeholder="Search user name or email"></div><div class="col-auto"><button class="btn btn-outline-primary" type="submit">Search</button></div>@if($search!=='')<div class="col-auto"><a class="btn btn-light" href="{{ route('admin.user-roles.index') }}">Clear</a></div>@endif</form></div><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>User</th><th>Assigned roles</th><th>Access level</th><th class="text-end">Actions</th></tr></thead><tbody>
@forelse($users as $user)<tr><td><div class="fw-semibold">{{ $user->name }}</div><div class="small text-secondary">{{ $user->email }}</div></td><td>@forelse($user->roles as $role)<span class="badge {{ $role->status?'text-bg-primary':'text-bg-secondary' }} me-1">{{ $role->name }}</span>@empty<span class="text-secondary">No roles</span>@endforelse</td><td>@if($user->isSuperAdmin())<span class="badge text-bg-danger">Super Admin</span>@elseif($user->roles->where('status',true)->isNotEmpty())<span class="badge text-bg-success">Role-based</span>@else<span class="badge text-bg-secondary">No CMS access</span>@endif</td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.user-roles.edit',$user) }}">Manage roles</a></td></tr>
@empty<tr><td class="py-5 text-center text-secondary" colspan="4">No users match this search.</td></tr>@endforelse
</tbody></table></div>@if($users->hasPages())<div class="card-footer py-3">{{ $users->links('pagination::bootstrap-5') }}</div>@endif</div>
@endsection
