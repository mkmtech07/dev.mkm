@extends('layouts.admin')

@section('title', 'Edit Tenant')
@section('page-title', 'Edit Tenant')

@section('content')
    <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}">
        @csrf
        @method('PUT')
        @include('admin.tenants.form', ['submitLabel' => 'Update tenant'])
    </form>
@endsection
