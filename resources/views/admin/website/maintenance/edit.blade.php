@extends('layouts.admin')

@section('title', 'Maintenance Mode')
@section('page-title', 'Maintenance Mode')

@section('content')
    @php
        $isActiveNow = $maintenanceSetting->isCurrentlyActive();
        $statusBadge = ! $maintenanceSetting->status
            ? ['Disabled', 'success']
            : ($isActiveNow ? ['Enabled', 'danger'] : ['Scheduled', 'warning']);
    @endphp

    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <h2 class="h4 mb-1">Maintenance Mode Manager</h2>
            <p class="text-secondary mb-0">Control public website access without using command-line maintenance mode.</p>
        </div>
        <div class="align-self-lg-start">
            <span class="badge text-bg-{{ $statusBadge[1] }} fs-6 px-3 py-2">{{ $statusBadge[0] }}</span>
        </div>
    </div>

    @if($maintenanceSetting->status)
        <div class="alert alert-warning d-flex gap-2 align-items-start" role="alert">
            <span class="fw-semibold">Warning:</span>
            <span>Maintenance mode can hide the public website from visitors.</span>
        </div>
    @endif

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

    <form method="POST" action="{{ route('admin.website.maintenance.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.website.maintenance.form')
    </form>
@endsection
