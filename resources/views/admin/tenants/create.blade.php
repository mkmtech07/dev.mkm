@extends('layouts.admin')

@section('title', 'Create Tenant')
@section('page-title', 'Create Tenant')

@section('content')
    <form method="POST" action="{{ route('admin.tenants.store') }}">
        @csrf
        @include('admin.tenants.form', ['submitLabel' => 'Create tenant'])
    </form>
@endsection
