@extends('layouts.admin')

@section('title', 'Team Members')
@section('page-title', 'Team Members')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Team members</h2>
            <p class="text-secondary mb-0">Manage the people displayed on the public homepage.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.team-members.create') }}">Add team member</a>
    </div>

    <div class="card content-card">
        <div class="card-header">
            <form class="row g-2" method="GET" action="{{ route('admin.team-members.index') }}">
                <div class="col-sm-8 col-lg-5">
                    <label class="visually-hidden" for="search">Search team members</label>
                    <input
                        class="form-control"
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        placeholder="Search name, designation, or email"
                    >
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                </div>
                @if ($search !== '')
                    <div class="col-auto">
                        <a class="btn btn-light" href="{{ route('admin.team-members.index') }}">Clear</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Member</th>
                        <th scope="col">Contact</th>
                        <th scope="col">Status</th>
                        <th scope="col">Order</th>
                        <th class="text-end" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($teamMembers as $teamMember)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if ($teamMember->image)
                                        <img
                                            class="rounded-circle object-fit-cover"
                                            src="{{ asset($teamMember->image) }}"
                                            alt=""
                                            width="58"
                                            height="58"
                                        >
                                    @else
                                        <div
                                            class="d-grid rounded-circle bg-light text-primary fw-semibold"
                                            style="width: 58px; height: 58px; place-items: center;"
                                            aria-hidden="true"
                                        >
                                            {{ strtoupper(substr($teamMember->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $teamMember->name }}</div>
                                        @if ($teamMember->designation)
                                            <div class="text-secondary small">{{ $teamMember->designation }}</div>
                                        @endif
                                        @if ($teamMember->bio)
                                            <div class="text-secondary small text-truncate" style="max-width: 28rem;">
                                                {{ $teamMember->bio }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($teamMember->email)
                                    <div class="small">{{ $teamMember->email }}</div>
                                @endif
                                @if ($teamMember->phone)
                                    <div class="small text-secondary">{{ $teamMember->phone }}</div>
                                @endif
                                @if (! $teamMember->email && ! $teamMember->phone)
                                    <span class="text-secondary">—</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.team-members.toggle-status', $teamMember) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        class="btn btn-sm {{ $teamMember->status ? 'btn-success' : 'btn-outline-secondary' }}"
                                        type="submit"
                                        title="Toggle status"
                                    >
                                        {{ $teamMember->status ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td>{{ $teamMember->sort_order }}</td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.team-members.edit', $teamMember) }}">
                                    Edit
                                </a>
                                <form
                                    class="d-inline"
                                    method="POST"
                                    action="{{ route('admin.team-members.destroy', $teamMember) }}"
                                    onsubmit="return confirm('Delete this team member?')"
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
                                {{ $search !== '' ? 'No team members match your search.' : 'No team members have been created yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($teamMembers->hasPages())
            <div class="card-footer py-3">
                {{ $teamMembers->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
