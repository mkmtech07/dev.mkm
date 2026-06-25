@extends('layouts.admin')

@section('title', 'Email Automation')
@section('page-title', 'Email Automation')

@section('content')
    @php
        $mailReady = (bool) $mailSetting?->isConfigured();
        $enabledCount = collect(\App\Models\EmailAutomationSetting::EVENT_TOGGLES)->filter(fn ($key) => $automationSetting->{$key})->count();
        $availableTemplates = collect($templateStatuses)->where('available', true)->count();
    @endphp

    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <h2 class="h4 mb-1">Email Sending Automation</h2>
            <p class="text-secondary mb-0">Use saved email templates and database mail settings for customer replies and admin alerts.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-self-lg-start">
            <span class="badge text-bg-{{ $automationSetting->status ? 'success' : 'secondary' }} fs-6 px-3 py-2">{{ $automationSetting->status ? 'Enabled' : 'Disabled' }}</span>
            <span class="badge text-bg-{{ $mailReady ? 'success' : 'warning' }} fs-6 px-3 py-2">SMTP {{ $mailReady ? 'Active' : 'Inactive / Missing' }}</span>
        </div>
    </div>

    <div class="alert alert-warning" role="alert">
        Email automation depends on SMTP settings and active email templates.
    </div>

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

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card content-card h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="small text-secondary mb-1">Enabled events</div>
                    <div class="h3 mb-0">{{ $enabledCount }}/8</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card content-card h-100 border-start border-4 border-success">
                <div class="card-body">
                    <div class="small text-secondary mb-1">Templates available</div>
                    <div class="h3 mb-0">{{ $availableTemplates }}/8</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card content-card h-100 border-start border-4 border-{{ $mailReady ? 'success' : 'warning' }}">
                <div class="card-body">
                    <div class="small text-secondary mb-1">Mail settings</div>
                    <div class="h5 mb-0">{{ $mailReady ? 'Active' : 'Inactive / Missing' }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card content-card h-100 border-start border-4 border-info">
                <div class="card-body">
                    <div class="small text-secondary mb-1">Queue mode</div>
                    <div class="h5 mb-0">{{ $automationSetting->queue_emails ? 'Enabled' : 'Synchronous' }}</div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.email-automation.update') }}">
        @csrf
        @method('PUT')
        @include('admin.email-automation.form')
    </form>
@endsection
