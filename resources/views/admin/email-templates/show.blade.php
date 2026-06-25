@extends('layouts.admin')

@section('title', $template->name)
@section('page-title', 'Email Template Details')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <a class="small text-decoration-none" href="{{ route('admin.email-templates.index') }}">&larr; Email templates</a>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                <h2 class="h4 mb-0">{{ $template->name }}</h2>
                <span class="badge {{ $template->typeClass() }}">{{ \App\Models\EmailTemplate::label($template->type) }}</span>
                <span class="badge {{ $template->status ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $template->status ? 'Active' : 'Inactive' }}</span>
                @if($template->is_default)<span class="badge text-bg-primary">Default</span>@endif
            </div>
            <p class="text-secondary mt-1 mb-0"><code>{{ $template->slug }}</code></p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(auth()->user()->hasPermission('email_templates.preview'))
                <a class="btn btn-outline-info" href="{{ route('admin.email-templates.preview', $template) }}">Preview</a>
            @endif
            @if(auth()->user()->hasPermission('email_templates.edit'))
                <a class="btn btn-primary" href="{{ route('admin.email-templates.edit', $template) }}">Edit</a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card content-card mb-4">
                <div class="card-header"><h3 class="h6 mb-0">Subject</h3></div>
                <div class="card-body p-4">{{ $template->subject ?: 'No subject configured.' }}</div>
            </div>
            <div class="card content-card">
                <div class="card-header"><h3 class="h6 mb-0">Body</h3></div>
                <div class="card-body p-4">
                    <pre class="mb-0 text-break" style="white-space: pre-wrap;">{{ $template->body }}</pre>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card content-card">
                <div class="card-header"><h3 class="h6 mb-0">Variables</h3></div>
                <div class="card-body p-4">
                    @forelse($template->available_variables ?? [] as $variable)
                        <code class="d-inline-block me-2 mb-2">{{ '{'.$variable.'}' }}</code>
                    @empty
                        <div class="text-secondary">No variables configured.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
