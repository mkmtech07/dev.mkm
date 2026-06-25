@extends('layouts.admin')

@section('title', 'Mail Logs')
@section('page-title', 'Mail Logs')

@section('content')
    @php
        $filtersActive = $search !== '' || $status !== '' || $mailType !== '' || $dateFrom !== '' || $dateTo !== '';
        $canDeleteMailLogs = auth()->user()->hasPermission('mail_logs.delete');
    @endphp

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Mail delivery logs</h2>
            <p class="text-secondary mb-0">Track test emails and future system email delivery results.</p>
        </div>
        @if(auth()->user()->hasPermission('mail_settings.view'))
            <a class="btn btn-outline-primary" href="{{ route('admin.mail-settings.edit') }}">Mail settings</a>
        @endif
    </div>

    <div class="row g-3 mb-4">
        @foreach ([['Total logs', $summary['total'], 'primary'], ['Sent', $summary['sent'], 'success'], ['Failed', $summary['failed'], 'danger'], ['Pending', $summary['pending'], 'warning']] as [$label, $value, $color])
            <div class="col-6 col-lg-3">
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
                    <input class="form-control" name="search" type="search" value="{{ $search }}" placeholder="Search recipient, subject, template, type, or status" aria-label="Search mail logs">
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <select class="form-select" name="status">
                        <option value="">All statuses</option>
                        @foreach(\App\Models\MailLog::STATUSES as $item)
                            <option value="{{ $item }}" @selected($status === $item)>{{ \App\Models\MailLog::label($item) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <select class="form-select" name="mail_type">
                        <option value="">All mail types</option>
                        @foreach($mailTypes as $item)
                            <option value="{{ $item }}" @selected($mailType === $item)>{{ \App\Models\MailLog::label($item) }}</option>
                        @endforeach
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
                        <a class="btn btn-light" href="{{ route('admin.mail-logs.index') }}">Clear</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Recipient</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Sent</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mailLogs as $mailLog)
                        <tr>
                            <td>
                                <a class="text-decoration-none fw-semibold" href="{{ route('admin.mail-logs.show', $mailLog) }}">{{ $mailLog->recipient ?: 'N/A' }}</a>
                                @if($mailLog->template_slug)
                                    <div class="small text-secondary"><code>{{ $mailLog->template_slug }}</code></div>
                                @endif
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 28rem;">{{ $mailLog->subject ?: 'No subject' }}</div>
                                @if($mailLog->error_message)
                                    <div class="small text-danger text-truncate" style="max-width: 28rem;">{{ $mailLog->error_message }}</div>
                                @endif
                            </td>
                            <td><span class="badge text-bg-light">{{ \App\Models\MailLog::label($mailLog->mail_type) }}</span></td>
                            <td><span class="badge {{ $mailLog->statusClass() }}">{{ \App\Models\MailLog::label($mailLog->status) }}</span></td>
                            <td class="text-nowrap">{{ $mailLog->sent_at?->format('d M Y') ?: 'N/A' }}@if($mailLog->sent_at)<div class="small text-secondary">{{ $mailLog->sent_at->format('h:i A') }}</div>@endif</td>
                            <td class="text-nowrap">{{ $mailLog->created_at?->format('d M Y') ?: 'N/A' }}@if($mailLog->created_at)<div class="small text-secondary">{{ $mailLog->created_at->format('h:i A') }}</div>@endif</td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.mail-logs.show', $mailLog) }}">View</a>
                                @if($canDeleteMailLogs)
                                    <form class="d-inline" method="POST" action="{{ route('admin.mail-logs.destroy', $mailLog) }}" onsubmit="return confirm('Delete this mail log?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-5 text-center text-secondary" colspan="7">
                                {{ $filtersActive ? 'No mail logs match the selected filters.' : 'No mail logs have been recorded yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mailLogs->hasPages())
            <div class="card-footer py-3">{{ $mailLogs->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
