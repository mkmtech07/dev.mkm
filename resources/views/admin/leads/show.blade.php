@extends('layouts.admin')

@section('title', $lead->name)
@section('page-title', 'Lead Details')

@section('content')
    @php
        $statusClasses = ['new' => 'text-bg-primary', 'contacted' => 'text-bg-info', 'follow_up' => 'text-bg-warning', 'interested' => 'text-bg-success', 'converted' => 'text-bg-success', 'not_interested' => 'text-bg-secondary', 'spam' => 'text-bg-danger', 'closed' => 'text-bg-dark'];
        $priorityClasses = ['low' => 'text-bg-light', 'medium' => 'text-bg-info', 'high' => 'text-bg-warning', 'urgent' => 'text-bg-danger'];
        $whatsappNumber = preg_replace('/\D+/', '', $lead->whatsapp ?: $lead->phone ?: '');
    @endphp

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div><a class="small text-decoration-none" href="{{ route('admin.leads.index') }}">&larr; Leads & enquiries</a><div class="d-flex flex-wrap align-items-center gap-2 mt-2"><h2 class="h4 mb-0">{{ $lead->name }}</h2><span class="badge {{ $statusClasses[$lead->status] ?? 'text-bg-secondary' }}">{{ \App\Models\Lead::label($lead->status) }}</span><span class="badge {{ $priorityClasses[$lead->priority] ?? 'text-bg-light' }}">{{ ucfirst($lead->priority) }} priority</span>@unless ($lead->status_active)<span class="badge text-bg-secondary">Inactive</span>@endunless</div><p class="text-secondary mb-0 mt-1">Lead #{{ $lead->id }} · Created {{ $lead->created_at->format('d M Y, h:i A') }}</p></div>
        <div class="d-flex flex-wrap gap-2">@if ($lead->phone)<a class="btn btn-outline-primary" href="tel:{{ $lead->phone }}">Call</a>@endif @if ($whatsappNumber)<a class="btn btn-outline-success" href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener">WhatsApp</a>@endif @if ($lead->email)<a class="btn btn-outline-secondary" href="mailto:{{ $lead->email }}">Email</a>@endif <a class="btn btn-primary" href="{{ route('admin.leads.edit', $lead) }}">Edit lead</a></div>
    </div>

    @if ($errors->any())<div class="alert alert-danger">Please correct the note form errors below.</div>@endif

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card content-card mb-4"><div class="card-header"><h3 class="h6 mb-0">Enquiry</h3></div><div class="card-body p-4">
                @if ($lead->subject)<div class="small text-secondary mb-1">Subject</div><div class="fw-semibold mb-4">{{ $lead->subject }}</div>@endif
                <div class="small text-secondary mb-2">Message</div><div class="text-break {{ $lead->message ? '' : 'text-secondary' }}" style="white-space: pre-wrap;">{{ $lead->message ?: 'No enquiry message was recorded.' }}</div>
            </div></div>

            <div class="card content-card mb-4"><div class="card-header"><h3 class="h6 mb-1">Add activity</h3><p class="small text-secondary mb-0">Keep the customer history and next action in one timeline.</p></div><div class="card-body p-4">@include('admin.leads.notes.form')</div></div>

            <div class="card content-card"><div class="card-header"><h3 class="h6 mb-1">Activity timeline</h3><p class="small text-secondary mb-0">{{ $lead->notes->count() }} recorded {{ Str::plural('activity', $lead->notes->count()) }}</p></div><div class="card-body p-4">
                @forelse ($lead->notes as $note)
                    <div class="d-flex gap-3 {{ $loop->last ? '' : 'border-bottom pb-4 mb-4' }}">
                        <div class="rounded-circle bg-primary-subtle text-primary d-grid flex-shrink-0" style="width: 42px; height: 42px; place-items: center;">{{ strtoupper(substr($note->note_type, 0, 1)) }}</div>
                        <div class="flex-grow-1 min-w-0"><div class="d-flex flex-wrap justify-content-between gap-2"><div><span class="badge text-bg-light">{{ \App\Models\Lead::label($note->note_type) }}</span><span class="small text-secondary ms-2">{{ $note->user?->name ?: 'System' }}</span></div><span class="small text-secondary">{{ $note->created_at->format('d M Y, h:i A') }}</span></div><div class="mt-2 text-break" style="white-space: pre-wrap;">{{ $note->note }}</div>@if ($note->next_follow_up_date)<div class="small text-primary mt-2">Next follow-up: {{ $note->next_follow_up_date->format('d M Y, h:i A') }}</div>@endif<form class="mt-2" method="POST" action="{{ route('admin.leads.notes.destroy', [$lead, $note]) }}" onsubmit="return confirm('Delete this activity note?')">@csrf @method('DELETE')<button class="btn btn-sm btn-link text-danger p-0" type="submit">Delete note</button></form></div>
                    </div>
                @empty
                    <div class="text-center text-secondary py-4">No activity has been recorded for this lead yet.</div>
                @endforelse
            </div></div>
        </div>

        <div class="col-xl-4">
            <div class="card content-card mb-4"><div class="card-header"><h3 class="h6 mb-0">Pipeline</h3></div><div class="card-body p-4">
                <form class="mb-4" method="POST" action="{{ route('admin.leads.status.update', $lead) }}">@csrf @method('PATCH')<label class="form-label" for="status">Quick status update</label><div class="input-group"><select class="form-select" id="status" name="status">@foreach (\App\Models\Lead::STATUSES as $item)<option value="{{ $item }}" @selected($lead->status === $item)>{{ \App\Models\Lead::label($item) }}</option>@endforeach</select><button class="btn btn-outline-primary" type="submit">Update</button></div></form>
                <dl class="mb-0"><dt class="small text-secondary fw-normal">Priority</dt><dd class="mb-3"><span class="badge {{ $priorityClasses[$lead->priority] ?? 'text-bg-light' }}">{{ ucfirst($lead->priority) }}</span></dd><dt class="small text-secondary fw-normal">Source</dt><dd class="mb-3">{{ \App\Models\Lead::label($lead->source) }}</dd><dt class="small text-secondary fw-normal">Service</dt><dd class="mb-3">{{ $lead->service?->title ?: 'General enquiry' }}</dd><dt class="small text-secondary fw-normal">Assigned to</dt><dd class="mb-3">{{ $lead->assignedUser?->name ?: 'Unassigned' }}</dd><dt class="small text-secondary fw-normal">Budget</dt><dd class="mb-3">{{ $lead->budget ?: 'Not specified' }}</dd><dt class="small text-secondary fw-normal">Preferred contact</dt><dd class="mb-3">{{ $lead->preferred_contact_method ? ucfirst($lead->preferred_contact_method) : 'Not specified' }}</dd><dt class="small text-secondary fw-normal">Follow-up</dt><dd class="mb-0">{{ $lead->follow_up_date?->format('d M Y, h:i A') ?: 'Not scheduled' }}</dd></dl>
            </div></div>

            <div class="card content-card mb-4"><div class="card-header"><h3 class="h6 mb-0">Contact details</h3></div><div class="card-body p-4"><dl class="mb-0"><dt class="small text-secondary fw-normal">Company</dt><dd class="mb-3">{{ $lead->company_name ?: 'Not provided' }}</dd><dt class="small text-secondary fw-normal">Email</dt><dd class="mb-3">@if ($lead->email)<a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>@else Not provided @endif</dd><dt class="small text-secondary fw-normal">Phone</dt><dd class="mb-3">@if ($lead->phone)<a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a>@else Not provided @endif</dd><dt class="small text-secondary fw-normal">WhatsApp</dt><dd class="mb-0">{{ $lead->whatsapp ?: 'Not provided' }}</dd></dl></div></div>

            <div class="card content-card"><div class="card-header"><h3 class="h6 mb-0">Submission reference</h3></div><div class="card-body p-4"><dl class="mb-0"><dt class="small text-secondary fw-normal">IP address</dt><dd class="mb-3"><code>{{ $lead->ip_address ?: 'Not recorded' }}</code></dd><dt class="small text-secondary fw-normal">User agent</dt><dd class="small text-break mb-0">{{ $lead->user_agent ?: 'Not recorded' }}</dd></dl></div></div>
        </div>
    </div>
@endsection
