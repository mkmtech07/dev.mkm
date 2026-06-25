@extends('layouts.admin')

@section('title', 'Preview Email Template')
@section('page-title', 'Email Template Preview')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <a class="small text-decoration-none" href="{{ route('admin.email-templates.show', $template) }}">&larr; {{ $template->name }}</a>
            <h2 class="h4 mt-2 mb-1">Preview: {{ $template->name }}</h2>
            <p class="text-secondary mb-0">Rendered with sample data only. No email is sent.</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('admin.email-templates.edit', $template) }}">Edit template</a>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card content-card mb-4">
                <div class="card-header"><h3 class="h6 mb-0">Subject Preview</h3></div>
                <div class="card-body p-4 fw-semibold">{{ $subjectPreview ?: 'No subject configured.' }}</div>
            </div>
            <div class="card content-card">
                <div class="card-header"><h3 class="h6 mb-0">Email Body Preview</h3></div>
                <div class="card-body p-4 bg-light">
                    <div class="bg-white border rounded p-4 mx-auto" style="max-width: 680px;">
                        <div style="white-space: pre-wrap;">{!! nl2br(e($bodyPreview)) !!}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card content-card">
                <div class="card-header"><h3 class="h6 mb-0">Sample Data</h3></div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <tbody>
                                @foreach($sampleData as $key => $value)
                                    <tr>
                                        <th><code>{{ '{'.$key.'}' }}</code></th>
                                        <td class="text-break">{{ $value }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
