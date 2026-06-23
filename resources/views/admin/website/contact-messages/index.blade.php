@extends('layouts.admin')

@section('title', 'Contact Messages')
@section('page-title', 'Contact Messages')

@section('content')
    <div class="mb-4">
        <h2 class="h5 mb-1">Contact messages</h2>
        <p class="text-secondary mb-0">Review and manage enquiries submitted through the website.</p>
    </div>

    <div class="card content-card">
        <div class="card-header">
            <form class="row g-2" method="GET" action="{{ route('admin.contact-messages.index') }}">
                <div class="col-sm-6 col-lg-5">
                    <label class="visually-hidden" for="search">Search contact messages</label>
                    <input
                        class="form-control"
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        placeholder="Search name, phone, email, or subject"
                    >
                </div>
                <div class="col-sm-4 col-lg-3">
                    <label class="visually-hidden" for="status">Filter by read status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="all" @selected($status === 'all')>All messages</option>
                        <option value="unread" @selected($status === 'unread')>Unread</option>
                        <option value="read" @selected($status === 'read')>Read</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                </div>
                @if ($search !== '' || $status !== 'all')
                    <div class="col-auto">
                        <a class="btn btn-light" href="{{ route('admin.contact-messages.index') }}">Clear</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Sender</th>
                        <th scope="col">Subject</th>
                        <th scope="col">Received</th>
                        <th scope="col">Status</th>
                        <th class="text-end" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contactMessages as $contactMessage)
                        <tr class="{{ $contactMessage->is_read ? '' : 'table-light' }}">
                            <td>
                                <div class="fw-semibold">{{ $contactMessage->name }}</div>
                                @if ($contactMessage->email)
                                    <div class="small text-secondary">{{ $contactMessage->email }}</div>
                                @endif
                                @if ($contactMessage->phone)
                                    <div class="small text-secondary">{{ $contactMessage->phone }}</div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $contactMessage->subject ?: 'No subject' }}</div>
                                <div class="small text-secondary text-truncate" style="max-width: 28rem;">
                                    {{ $contactMessage->message }}
                                </div>
                            </td>
                            <td class="text-nowrap">
                                <div>{{ $contactMessage->created_at->format('d M Y') }}</div>
                                <div class="small text-secondary">{{ $contactMessage->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $contactMessage->is_read ? 'text-bg-secondary' : 'text-bg-primary' }}">
                                    {{ $contactMessage->is_read ? 'Read' : 'Unread' }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                <a
                                    class="btn btn-sm btn-outline-primary"
                                    href="{{ route('admin.contact-messages.show', $contactMessage) }}"
                                >View</a>
                                <form
                                    class="d-inline"
                                    method="POST"
                                    action="{{ route('admin.contact-messages.destroy', $contactMessage) }}"
                                    onsubmit="return confirm('Delete this contact message?')"
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
                                {{ $search !== '' || $status !== 'all' ? 'No contact messages match these filters.' : 'No contact messages have been received yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($contactMessages->hasPages())
            <div class="card-footer py-3">
                {{ $contactMessages->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
