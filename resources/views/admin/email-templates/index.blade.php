@extends('layouts.admin')

@section('title', 'Email Templates')
@section('page-title', 'Email Templates')

@section('content')
    @php($filtersActive = $search !== '' || $type !== '' || $status !== '')

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Email template manager</h2>
            <p class="text-secondary mb-0">Create, edit, preview, and manage reusable admin email content.</p>
        </div>
        @if(auth()->user()->hasPermission('email_templates.create'))
            <a class="btn btn-primary" href="{{ route('admin.email-templates.create') }}">Create template</a>
        @endif
    </div>

    <div class="card content-card">
        <div class="card-header">
            <form class="row g-2" method="GET">
                <div class="col-md-6 col-xl-4">
                    <input class="form-control" name="search" type="search" value="{{ $search }}" placeholder="Search name, slug, subject, or type" aria-label="Search templates">
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <select class="form-select" name="type">
                        <option value="">All types</option>
                        @foreach(\App\Models\EmailTemplate::TYPES as $item)
                            <option value="{{ $item }}" @selected($type === $item)>{{ \App\Models\EmailTemplate::label($item) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <select class="form-select" name="status">
                        <option value="">All statuses</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary">Filter</button>
                </div>
                @if($filtersActive)
                    <div class="col-auto">
                        <a class="btn btn-light" href="{{ route('admin.email-templates.index') }}">Clear</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Template</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Default</th>
                        <th>Updated</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                        <tr>
                            <td>
                                <a class="fw-semibold text-decoration-none" href="{{ route('admin.email-templates.show', $template) }}">{{ $template->name }}</a>
                                <div class="small text-secondary"><code>{{ $template->slug }}</code></div>
                                <div class="small text-secondary text-truncate" style="max-width: 34rem;">{{ $template->subject ?: 'No subject' }}</div>
                            </td>
                            <td><span class="badge {{ $template->typeClass() }}">{{ \App\Models\EmailTemplate::label($template->type) }}</span></td>
                            <td>
                                <span class="badge {{ $template->status ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $template->status ? 'Active' : 'Inactive' }}</span>
                                @if(auth()->user()->hasPermission('email_templates.edit'))
                                    <form class="d-inline ms-1" method="POST" action="{{ route('admin.email-templates.toggle-status', $template) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-link p-0" type="submit">{{ $template->status ? 'Disable' : 'Enable' }}</button>
                                    </form>
                                @endif
                            </td>
                            <td><span class="badge {{ $template->is_default ? 'text-bg-primary' : 'text-bg-light' }}">{{ $template->is_default ? 'Default' : 'Custom' }}</span></td>
                            <td class="text-nowrap">{{ $template->updated_at?->format('d M Y') ?: 'N/A' }}@if($template->updated_at)<div class="small text-secondary">{{ $template->updated_at->format('h:i A') }}</div>@endif</td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.email-templates.show', $template) }}">View</a>
                                @if(auth()->user()->hasPermission('email_templates.preview'))
                                    <a class="btn btn-sm btn-outline-info" href="{{ route('admin.email-templates.preview', $template) }}">Preview</a>
                                @endif
                                @if(auth()->user()->hasPermission('email_templates.edit'))
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.email-templates.edit', $template) }}">Edit</a>
                                @endif
                                @if(auth()->user()->hasPermission('email_templates.delete'))
                                    <form class="d-inline" method="POST" action="{{ route('admin.email-templates.destroy', $template) }}" onsubmit="return confirm('Delete this email template?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" @disabled($template->is_default)>Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-5 text-center text-secondary" colspan="6">
                                {{ $filtersActive ? 'No email templates match the selected filters.' : 'No email templates have been created yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($templates->hasPages())
            <div class="card-footer py-3">{{ $templates->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
