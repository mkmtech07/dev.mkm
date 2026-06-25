@extends('layouts.admin')

@section('title', 'Mail Settings')
@section('page-title', 'Mail Settings')

@section('content')
    @php
        $isConfigured = $mailSetting->isConfigured();
        $statusBadge = ! $mailSetting->status
            ? ['Inactive', 'secondary']
            : ($isConfigured ? ['Active', 'success'] : ['Needs configuration', 'warning']);
    @endphp

    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <h2 class="h4 mb-1">SMTP / Mail Settings</h2>
            <p class="text-secondary mb-0">Configure database-backed email delivery and send safe test emails from the admin panel.</p>
        </div>
        <div class="align-self-lg-start">
            <span class="badge text-bg-{{ $statusBadge[1] }} fs-6 px-3 py-2">{{ $statusBadge[0] }}</span>
        </div>
    </div>

    @unless($isConfigured)
        <div class="alert alert-warning" role="alert">
            Mail settings are not configured. Save a usable mail driver before relying on system email delivery.
        </div>
    @endunless

    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-2">Please fix the following:</div>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.mail-settings.update') }}">
        @csrf
        @method('PUT')
        @include('admin.mail-settings.form')
    </form>

    @include('admin.mail-settings.test')
@endsection
