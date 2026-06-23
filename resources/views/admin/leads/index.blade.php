@extends('layouts.admin')

@section('title', 'Leads & Enquiries')
@section('page-title', 'Leads & Enquiries')

@section('content')
    @php
        $statusClasses = ['new' => 'text-bg-primary', 'contacted' => 'text-bg-info', 'follow_up' => 'text-bg-warning', 'interested' => 'text-bg-success', 'converted' => 'text-bg-success', 'not_interested' => 'text-bg-secondary', 'spam' => 'text-bg-danger', 'closed' => 'text-bg-dark'];
        $priorityClasses = ['low' => 'text-bg-light', 'medium' => 'text-bg-info', 'high' => 'text-bg-warning', 'urgent' => 'text-bg-danger'];
        $filtersActive = $search !== '' || $source !== '' || $status !== '' || $priority !== '' || $followUp !== '' || $serviceId > 0 || $dateFrom !== '' || $dateTo !== '';
    @endphp

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div><h2 class="h5 mb-1">Lead management dashboard</h2><p class="text-secondary mb-0">Track enquiries, conversations, follow-ups, and conversions.</p></div>
        <a class="btn btn-primary" href="{{ route('admin.leads.create') }}">Add lead</a>
    </div>

    <div class="row g-3 mb-4">
        @foreach ([
            ['Total leads', $summary['total'], 'primary'], ['New leads', $summary['new'], 'info'],
            ['Follow-ups', $summary['follow_up'], 'warning'], ['Converted', $summary['converted'], 'success'],
            ['Urgent', $summary['urgent'], 'danger'], ["Today's follow-ups", $summary['today'], 'dark'],
        ] as [$label, $value, $color])
            <div class="col-6 col-lg-4 col-xxl-2">
                <div class="card content-card h-100 border-start border-4 border-{{ $color }}">
                    <div class="card-body"><div class="small text-secondary mb-1">{{ $label }}</div><div class="h3 mb-0">{{ $value }}</div></div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card content-card">
        <div class="card-header">
            <form method="GET" action="{{ route('admin.leads.index') }}">
                <div class="row g-2">
                    <div class="col-md-6 col-xl-4"><label class="visually-hidden" for="search">Search</label><input class="form-control" id="search" name="search" type="search" value="{{ $search }}" placeholder="Search contact, company, subject, message"></div>
                    <div class="col-6 col-md-3 col-xl-2"><select class="form-select" name="source" aria-label="Source"><option value="">All sources</option>@foreach (\App\Models\Lead::SOURCES as $item)<option value="{{ $item }}" @selected($source === $item)>{{ \App\Models\Lead::label($item) }}</option>@endforeach</select></div>
                    <div class="col-6 col-md-3 col-xl-2"><select class="form-select" name="status" aria-label="Status"><option value="">All statuses</option>@foreach (\App\Models\Lead::STATUSES as $item)<option value="{{ $item }}" @selected($status === $item)>{{ \App\Models\Lead::label($item) }}</option>@endforeach</select></div>
                    <div class="col-6 col-md-3 col-xl-2"><select class="form-select" name="priority" aria-label="Priority"><option value="">All priorities</option>@foreach (\App\Models\Lead::PRIORITIES as $item)<option value="{{ $item }}" @selected($priority === $item)>{{ ucfirst($item) }}</option>@endforeach</select></div>
                    <div class="col-6 col-md-3 col-xl-2"><select class="form-select" name="service" aria-label="Service"><option value="">All services</option>@foreach ($services as $service)<option value="{{ $service->id }}" @selected($serviceId === $service->id)>{{ $service->title }}</option>@endforeach</select></div>
                    <div class="col-6 col-md-3 col-xl-2"><select class="form-select" name="follow_up" aria-label="Follow-up"><option value="">Any follow-up</option><option value="today" @selected($followUp === 'today')>Today</option><option value="overdue" @selected($followUp === 'overdue')>Overdue</option><option value="upcoming" @selected($followUp === 'upcoming')>Upcoming</option></select></div>
                    <div class="col-6 col-md-3 col-xl-2"><input class="form-control" name="date_from" type="date" value="{{ $dateFrom }}" aria-label="Created from"></div>
                    <div class="col-6 col-md-3 col-xl-2"><input class="form-control" name="date_to" type="date" value="{{ $dateTo }}" aria-label="Created to"></div>
                    <div class="col-auto"><button class="btn btn-outline-primary" type="submit">Filter</button></div>
                    @if ($filtersActive)<div class="col-auto"><a class="btn btn-light" href="{{ route('admin.leads.index') }}">Clear</a></div>@endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Lead</th><th>Source / Service</th><th>Status</th><th>Priority</th><th>Follow-up</th><th>Latest activity</th><th>Created</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse ($leads as $lead)
                        <tr class="{{ $lead->status_active ? '' : 'opacity-75' }}">
                            <td><a class="fw-semibold text-decoration-none" href="{{ route('admin.leads.show', $lead) }}">{{ $lead->name }}</a>@if ($lead->company_name)<div class="small text-secondary">{{ $lead->company_name }}</div>@endif<div class="small text-secondary">{{ $lead->phone ?: $lead->email ?: $lead->whatsapp ?: 'No contact details' }}</div></td>
                            <td><span class="badge text-bg-light">{{ \App\Models\Lead::label($lead->source) }}</span><div class="small text-secondary mt-1">{{ $lead->service?->title ?: 'General enquiry' }}</div></td>
                            <td>
                                <form method="POST" action="{{ route('admin.leads.status.update', $lead) }}">@csrf @method('PATCH')
                                    <select class="form-select form-select-sm {{ $statusClasses[$lead->status] ?? '' }}" name="status" onchange="this.form.submit()" aria-label="Update status">@foreach (\App\Models\Lead::STATUSES as $item)<option value="{{ $item }}" @selected($lead->status === $item)>{{ \App\Models\Lead::label($item) }}</option>@endforeach</select>
                                </form>
                            </td>
                            <td><span class="badge {{ $priorityClasses[$lead->priority] ?? 'text-bg-light' }}">{{ ucfirst($lead->priority) }}</span></td>
                            <td class="text-nowrap">@if ($lead->follow_up_date)<div class="{{ $lead->follow_up_date->isPast() && ! $lead->follow_up_date->isToday() ? 'text-danger fw-semibold' : '' }}">{{ $lead->follow_up_date->format('d M Y') }}</div><div class="small text-secondary">{{ $lead->follow_up_date->format('h:i A') }}</div>@else<span class="text-secondary">Not set</span>@endif</td>
                            <td><div class="small text-truncate" style="max-width: 14rem;">{{ $lead->latestNote?->note ?: ($lead->subject ?: 'No notes yet') }}</div>@if ($lead->latestNote)<div class="small text-secondary">{{ $lead->latestNote->created_at->diffForHumans() }}</div>@endif</td>
                            <td class="text-nowrap"><div>{{ $lead->created_at->format('d M Y') }}</div><div class="small text-secondary">{{ $lead->created_at->format('h:i A') }}</div></td>
                            <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.leads.show', $lead) }}">View</a> <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.leads.edit', $lead) }}">Edit</a> <form class="d-inline" method="POST" action="{{ route('admin.leads.destroy', $lead) }}" onsubmit="return confirm('Delete this lead?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">Delete</button></form></td>
                        </tr>
                    @empty
                        <tr><td class="py-5 text-center text-secondary" colspan="8">{{ $filtersActive ? 'No leads match the selected filters.' : 'No leads yet. New enquiries will appear here.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($leads->hasPages())<div class="card-footer py-3">{{ $leads->onEachSide(1)->links('pagination::bootstrap-5') }}</div>@endif
    </div>
@endsection
