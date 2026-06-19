@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="row g-4">
        <div class="col-12">
            <div class="card content-card">
                <div class="card-body p-4 p-lg-5">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-8">
                            <span class="badge text-bg-primary mb-3">CMS Admin</span>
                            <h2 class="fw-bold">Welcome back, {{ auth()->user()->name }}.</h2>
                            <p class="text-secondary mb-4">
                                Manage your business website content and contact details from one place.
                            </p>
                            <a class="btn btn-primary" href="{{ route('admin.settings.edit') }}">
                                Configure website settings
                            </a>
                        </div>
                        <div class="col-lg-4 text-lg-center">
                            <div class="stat-icon bg-primary-subtle text-primary mb-3">
                                <svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="3"/>
                                    <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-4V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9A1.7 1.7 0 0 0 3 14H2.8v-4H3a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-1.6v-.2h4V3a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v4H21a1.7 1.7 0 0 0-1.6 1z"/>
                                </svg>
                            </div>
                            <h3 class="h5 fw-semibold">Website Settings</h3>
                            <p class="text-secondary small mb-0">Branding, contact details, social links, and SEO.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
