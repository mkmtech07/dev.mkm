@extends('layouts.admin')

@section('title', 'Mail Log #'.$mailLog->id)
@section('page-title', 'Mail Log Details')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <a class="small text-decoration-none" href="{{ route('admin.mail-logs.index') }}">&larr; Mail Logs</a>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                <h2 class="h4 mb-0">{{ $mailLog->subject ?: 'No subject' }}</h2>
                <span class="badge {{ $mailLog->statusClass() }}">{{ \App\Models\MailLog::label($mailLog->status) }}</span>
                <span class="badge text-bg-light">{{ \App\Models\MailLog::label($mailLog->mail_type) }}</span>
            </div>
            <p class="text-secondary mt-1 mb-0">Created {{ $mailLog->created_at?->format('d M Y, h:i A') ?: 'not recorded' }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(auth()->user()->hasPermission('mail_logs.delete'))
                <form method="POST" action="{{ route('admin.mail-logs.destroy', $mailLog) }}" onsubmit="return confirm('Delete this mail log?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger" type="submit">Delete</button>
                </form>
            @endif
            <a class="btn btn-outline-secondary" href="{{ route('admin.mail-logs.index') }}">Back</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card content-card mb-4">
                <div class="card-header"><h3 class="h6 mb-0">Delivery Result</h3></div>
                <div class="card-body p-4">
                    @if($mailLog->status === 'failed')
                        <div class="alert alert-danger mb-0" role="alert">{{ $mailLog->error_message ?: 'Mail delivery failed.' }}</div>
                    @else
                        <div class="text-secondary">No delivery error was recorded for this log.</div>
                    @endif
                </div>
            </div>

            <div class="card content-card">
                <div class="card-header"><h3 class="h6 mb-0">Data</h3></div>
                <div class="card-body p-4">
                    @if($mailLog->data)
                        <pre class="bg-light border rounded p-3 mb-0 small text-break"><code>{{ json_encode($mailLog->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                    @else
                        <div class="text-secondary">No additional data was stored for this mail log.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card content-card">
                <div class="card-header"><h3 class="h6 mb-0">Details</h3></div>
                <div class="card-body p-4">
                    <dl class="mb-0">
                        <dt class="small text-secondary fw-normal">Recipient</dt>
                        <dd class="mb-3">{{ $mailLog->recipient ?: 'Not recorded' }}</dd>

                        <dt class="small text-secondary fw-normal">Template slug</dt>
                        <dd class="mb-3">@if($mailLog->template_slug)<code>{{ $mailLog->template_slug }}</code>@else Not provided @endif</dd>

                        <dt class="small text-secondary fw-normal">Mail type</dt>
                        <dd class="mb-3">{{ \App\Models\MailLog::label($mailLog->mail_type) }}</dd>

                        <dt class="small text-secondary fw-normal">Sent at</dt>
                        <dd class="mb-3">{{ $mailLog->sent_at?->format('d M Y, h:i A') ?: 'Not sent' }}</dd>

                        <dt class="small text-secondary fw-normal">Created by</dt>
                        <dd class="mb-3">{{ $mailLog->creator?->name ?: 'System' }}</dd>

                        <dt class="small text-secondary fw-normal">Created at</dt>
                        <dd class="mb-0">{{ $mailLog->created_at?->format('d M Y, h:i A') ?: 'Not recorded' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
