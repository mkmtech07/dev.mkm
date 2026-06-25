@extends('layouts.admin')

@section('title', $notification->title)
@section('page-title', 'Notification Details')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <a class="small text-decoration-none" href="{{ route('admin.notifications.index') }}">&larr; Notifications</a>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                <h2 class="h4 mb-0">{{ $notification->title }}</h2>
                <span class="badge {{ $notification->typeClass() }}">{{ \App\Models\AdminNotification::label($notification->type) }}</span>
                <span class="badge {{ $notification->is_read ? 'text-bg-secondary' : 'text-bg-primary' }}">{{ $notification->is_read ? 'Read' : 'Unread' }}</span>
            </div>
            <p class="text-secondary mt-1 mb-0">Created {{ $notification->created_at?->format('d M Y, h:i A') ?: 'not recorded' }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($notification->action_url)
                <a class="btn btn-primary" href="{{ $notification->action_url }}">Open action</a>
            @endif
            <a class="btn btn-outline-secondary" href="{{ route('admin.notifications.index') }}">Back</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card content-card mb-4">
                <div class="card-header"><h3 class="h6 mb-0">Message</h3></div>
                <div class="card-body p-4">
                    <div class="{{ $notification->message ? '' : 'text-secondary' }}" style="white-space: pre-wrap;">{{ $notification->message ?: 'No message provided.' }}</div>
                </div>
            </div>

            <div class="card content-card">
                <div class="card-header"><h3 class="h6 mb-0">Data</h3></div>
                <div class="card-body p-4">
                    @if($notification->data)
                        <pre class="bg-light border rounded p-3 mb-0 small text-break"><code>{{ json_encode($notification->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                    @else
                        <div class="text-secondary">No additional data was stored for this notification.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card content-card">
                <div class="card-header"><h3 class="h6 mb-0">Details</h3></div>
                <div class="card-body p-4">
                    <dl class="mb-0">
                        <dt class="small text-secondary fw-normal">Module</dt>
                        <dd class="mb-3">{{ \App\Models\AdminNotification::label($notification->module) }}</dd>

                        <dt class="small text-secondary fw-normal">Action URL</dt>
                        <dd class="mb-3">@if($notification->action_url)<code class="text-break">{{ $notification->action_url }}</code>@else Not provided @endif</dd>

                        <dt class="small text-secondary fw-normal">Read at</dt>
                        <dd class="mb-3">{{ $notification->read_at?->format('d M Y, h:i A') ?: 'Not read before opening this page' }}</dd>

                        <dt class="small text-secondary fw-normal">Created for</dt>
                        <dd class="mb-3">{{ $notification->user?->name ?: 'All admins' }}</dd>

                        <dt class="small text-secondary fw-normal">Created by</dt>
                        <dd class="mb-0">{{ $notification->creator?->name ?: 'System' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
