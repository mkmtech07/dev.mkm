@extends('layouts.admin')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
    @php
        $filtersActive = $search !== '' || $type !== '' || $module !== '' || $status !== '' || $dateFrom !== '' || $dateTo !== '';
        $statusClasses = ['read' => 'text-bg-secondary', 'unread' => 'text-bg-primary'];
    @endphp

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Admin notifications</h2>
            <p class="text-secondary mb-0">Important CMS events and operational alerts for the admin panel.</p>
        </div>
        @if(auth()->user()->hasPermission('notifications.mark_read'))
            <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
                @csrf
                <button class="btn btn-outline-primary" type="submit">Mark all as read</button>
            </form>
        @endif
    </div>

    <div class="row g-3 mb-4">
        @foreach ([['Total notifications', $summary['total'], 'primary'], ['Unread notifications', $summary['unread'], 'info'], ['Today notifications', $summary['today'], 'success'], ['Warnings', $summary['warning'], 'warning'], ['Danger alerts', $summary['danger'], 'danger']] as [$label, $value, $color])
            <div class="col-6 col-lg-4 col-xxl-2">
                <div class="card content-card h-100 border-start border-4 border-{{ $color }}">
                    <div class="card-body">
                        <div class="small text-secondary mb-1">{{ $label }}</div>
                        <div class="h3 mb-0">{{ $value }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card content-card">
        <div class="card-header">
            <form class="row g-2" method="GET">
                <div class="col-md-6 col-xl-4">
                    <input class="form-control" name="search" type="search" value="{{ $search }}" placeholder="Search title, message, module, or type" aria-label="Search notifications">
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <select class="form-select" name="type">
                        <option value="">All types</option>
                        @foreach(\App\Models\AdminNotification::TYPES as $item)
                            <option value="{{ $item }}" @selected($type === $item)>{{ \App\Models\AdminNotification::label($item) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <select class="form-select" name="module">
                        <option value="">All modules</option>
                        @foreach($modules as $item)
                            <option value="{{ $item }}" @selected($module === $item)>{{ \App\Models\AdminNotification::label($item) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <select class="form-select" name="status">
                        <option value="">All statuses</option>
                        <option value="unread" @selected($status === 'unread')>Unread</option>
                        <option value="read" @selected($status === 'read')>Read</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <input class="form-control" name="date_from" type="date" value="{{ $dateFrom }}" aria-label="From date">
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <input class="form-control" name="date_to" type="date" value="{{ $dateTo }}" aria-label="To date">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary">Filter</button>
                </div>
                @if($filtersActive)
                    <div class="col-auto">
                        <a class="btn btn-light" href="{{ route('admin.notifications.index') }}">Clear</a>
                    </div>
                @endif
            </form>
        </div>

        <form method="POST" action="{{ route('admin.notifications.bulk-destroy') }}" onsubmit="return confirm('Delete selected notifications?')">
            @csrf
            @method('DELETE')
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            @if(auth()->user()->hasPermission('notifications.delete'))
                                <th style="width: 42px;"><span class="visually-hidden">Select</span></th>
                            @endif
                            <th>Notification</th>
                            <th>Type</th>
                            <th>Module</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adminNotifications as $notification)
                            <tr class="{{ $notification->is_read ? '' : 'table-light fw-semibold' }}">
                                @if(auth()->user()->hasPermission('notifications.delete'))
                                    <td><input class="form-check-input" name="notifications[]" type="checkbox" value="{{ $notification->id }}" aria-label="Select {{ $notification->title }}"></td>
                                @endif
                                <td>
                                    <a class="text-decoration-none" href="{{ route('admin.notifications.show', $notification) }}">{{ $notification->title }}</a>
                                    <div class="small text-secondary text-truncate" style="max-width: 32rem;">{{ $notification->message ?: 'No message provided.' }}</div>
                                </td>
                                <td><span class="badge {{ $notification->typeClass() }}">{{ \App\Models\AdminNotification::label($notification->type) }}</span></td>
                                <td><span class="badge text-bg-light">{{ \App\Models\AdminNotification::label($notification->module) }}</span></td>
                                <td><span class="badge {{ $notification->is_read ? $statusClasses['read'] : $statusClasses['unread'] }}">{{ $notification->is_read ? 'Read' : 'Unread' }}</span></td>
                                <td class="text-nowrap">{{ $notification->created_at?->format('d M Y') ?: 'N/A' }}@if($notification->created_at)<div class="small text-secondary">{{ $notification->created_at->format('h:i A') }}</div>@endif</td>
                                <td class="text-end text-nowrap">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.notifications.show', $notification) }}">View</a>
                                    @if(auth()->user()->hasPermission('notifications.mark_read') && ! $notification->is_read)
                                        <form class="d-inline" method="POST" action="{{ route('admin.notifications.mark-read', $notification) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" type="submit">Mark read</button>
                                        </form>
                                    @endif
                                    @if(auth()->user()->hasPermission('notifications.delete'))
                                        <form class="d-inline" method="POST" action="{{ route('admin.notifications.destroy', $notification) }}" onsubmit="return confirm('Delete this notification?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="py-5 text-center text-secondary" colspan="{{ auth()->user()->hasPermission('notifications.delete') ? 7 : 6 }}">
                                    {{ $filtersActive ? 'No notifications match the selected filters.' : 'No notifications have been created yet.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(auth()->user()->hasPermission('notifications.delete') && $adminNotifications->count())
                <div class="card-footer bg-white d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 py-3">
                    <button class="btn btn-outline-danger" type="submit">Delete selected</button>
                    @if($adminNotifications->hasPages())
                        <div>{{ $adminNotifications->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
                    @endif
                </div>
            @elseif($adminNotifications->hasPages())
                <div class="card-footer py-3">{{ $adminNotifications->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
            @endif
        </form>
    </div>
@endsection
