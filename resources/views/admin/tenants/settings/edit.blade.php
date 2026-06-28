@extends('layouts.admin')

@section('title', 'Tenant Settings')
@section('page-title', 'Tenant Settings')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">{{ $tenant->name }}</h2>
            <p class="text-secondary mb-0">Branding and contact metadata for this tenant.</p>
        </div>
        <a class="btn btn-light" href="{{ route('admin.tenants.show', $tenant) }}">Back to tenant</a>
    </div>

    <form method="POST" action="{{ route('admin.tenants.settings.update', $tenant) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.tenants.settings.form')
    </form>
@endsection
