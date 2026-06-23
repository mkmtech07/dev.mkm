@extends('layouts.admin')
@section('title', 'Generate Backup')
@section('page-title', 'Generate Backup')
@section('content')
    <div class="mb-4"><a class="small text-decoration-none" href="{{ route('admin.backups.index') }}">&larr; Backups</a><h2 class="h5 mt-2 mb-1">Create a private backup archive</h2><p class="text-secondary mb-0">Choose exactly what should be exported. Restore/import is intentionally not part of this module.</p></div>
    @unless($zipAvailable)<div class="alert alert-danger"><strong>ZIP support is currently unavailable.</strong> Enable the PHP <code>zip</code> extension before generating backups. Attempts are recorded as failed with a safe diagnostic message.</div>@endunless
    @if($errors->any())<div class="alert alert-danger">Please select a valid backup type.</div>@endif
    <form method="POST" action="{{ route('admin.backups.store') }}">@csrf<div class="row g-4"><div class="col-xl-8"><div class="card content-card"><div class="card-header"><h3 class="h6 mb-1">Backup type</h3><p class="small text-secondary mb-0">Archives never include <code>.env</code>, dependencies, logs, cache, or session files.</p></div><div class="card-body p-4"><div class="row g-3">
        @foreach ([
            'database'=>['Database backup','SQL via secure mysqldump when available; otherwise a streamed JSON table export.'],
            'files'=>['Uploaded files','Media Library storage and public uploaded image assets only.'],
            'full'=>['Full backup','Database export plus uploaded files in one private ZIP archive.'],
            'content_export'=>['Content export','Portable JSON export of CMS content, settings, leads, newsletter, and contact data.'],
        ] as $value=>[$label,$description])
            <div class="col-md-6"><label class="card h-100 border p-3 backup-choice"><div class="form-check"><input class="form-check-input" name="type" type="radio" value="{{ $value }}" @checked(old('type') === $value) required><span class="form-check-label fw-semibold ms-1">{{ $label }}</span></div><span class="small text-secondary d-block mt-2">{{ $description }}</span></label></div>
        @endforeach
        @error('type')<div class="text-danger small">{{ $message }}</div>@enderror
    </div></div></div></div><div class="col-xl-4"><div class="card content-card sticky-xl-top" style="top:96px"><div class="card-header"><h3 class="h6 mb-0">Backup details</h3></div><div class="card-body p-4"><label class="form-label" for="name">Name</label><input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" maxlength="255" placeholder="Optional internal label">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="alert alert-warning small mt-4 mb-0"><strong>Sensitive data:</strong> keep downloaded archives encrypted and access-controlled.</div></div><div class="card-footer bg-white d-grid gap-2 py-3"><button class="btn btn-primary btn-lg" type="submit">Generate backup</button><a class="btn btn-light" href="{{ route('admin.backups.index') }}">Cancel</a></div></div></div></div></form>
@endsection
