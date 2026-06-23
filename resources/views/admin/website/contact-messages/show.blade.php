@extends('layouts.admin')

@section('title', 'Contact Message')
@section('page-title', 'Contact Message')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <a class="small text-decoration-none" href="{{ route('admin.contact-messages.index') }}">&larr; Contact messages</a>
            <h2 class="h5 mt-2 mb-1">{{ $contactMessage->subject ?: 'No subject' }}</h2>
            <p class="text-secondary mb-0">Received {{ $contactMessage->created_at->format('d M Y \a\t h:i A') }}</p>
        </div>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('admin.contact-messages.convert-to-lead', $contactMessage) }}" onsubmit="return confirm('Convert this message into a new lead?')">
                @csrf
                <button class="btn btn-primary" type="submit">Convert to lead</button>
            </form>
            <form method="POST" action="{{ route('admin.contact-messages.toggle-read', $contactMessage) }}">
                @csrf
                @method('PATCH')
                <button class="btn btn-outline-primary" type="submit">
                    Mark as {{ $contactMessage->is_read ? 'unread' : 'read' }}
                </button>
            </form>
            <form
                method="POST"
                action="{{ route('admin.contact-messages.destroy', $contactMessage) }}"
                onsubmit="return confirm('Delete this contact message?')"
            >
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger" type="submit">Delete</button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card content-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center gap-3">
                    <h3 class="h6 mb-0">Message</h3>
                    <span class="badge {{ $contactMessage->is_read ? 'text-bg-secondary' : 'text-bg-primary' }}">
                        {{ $contactMessage->is_read ? 'Read' : 'Unread' }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div class="small text-secondary mb-1">Subject</div>
                        <div class="fw-semibold">{{ $contactMessage->subject ?: 'No subject' }}</div>
                    </div>
                    <div class="small text-secondary mb-2">Message</div>
                    <div class="text-break" style="white-space: pre-wrap;">{{ $contactMessage->message }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.contact-messages.update', $contactMessage) }}">
                @csrf
                @method('PUT')
                <div class="card content-card">
                    <div class="card-header">
                        <h3 class="h6 mb-1">Internal notes</h3>
                        <p class="small text-secondary mb-0">These notes are visible only to administrators.</p>
                    </div>
                    <div class="card-body p-4">
                        <label class="visually-hidden" for="notes">Internal notes</label>
                        <textarea
                            class="form-control @error('notes') is-invalid @enderror"
                            id="notes"
                            name="notes"
                            rows="6"
                            maxlength="10000"
                            placeholder="Add follow-up details or other internal context..."
                        >{{ old('notes', $contactMessage->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="card-footer bg-white text-end py-3">
                        <button class="btn btn-primary" type="submit">Save notes</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-xl-4">
            <div class="card content-card">
                <div class="card-header">
                    <h3 class="h6 mb-0">Sender details</h3>
                </div>
                <div class="card-body p-4">
                    <dl class="mb-0">
                        <dt class="small text-secondary fw-normal">Name</dt>
                        <dd class="fw-semibold mb-3">{{ $contactMessage->name }}</dd>

                        <dt class="small text-secondary fw-normal">Email</dt>
                        <dd class="mb-3">
                            @if ($contactMessage->email)
                                <a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a>
                            @else
                                <span class="text-secondary">Not provided</span>
                            @endif
                        </dd>

                        <dt class="small text-secondary fw-normal">Phone</dt>
                        <dd class="mb-3">
                            @if ($contactMessage->phone)
                                <a href="tel:{{ $contactMessage->phone }}">{{ $contactMessage->phone }}</a>
                            @else
                                <span class="text-secondary">Not provided</span>
                            @endif
                        </dd>

                        <dt class="small text-secondary fw-normal">Source</dt>
                        <dd class="mb-3">{{ $contactMessage->source ?: 'Not recorded' }}</dd>

                        <dt class="small text-secondary fw-normal">Replied</dt>
                        <dd class="mb-0">
                            {{ $contactMessage->replied_at?->format('d M Y, h:i A') ?: 'Not recorded' }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
