@extends('layouts.admin')

@section('title', 'Create Email Template')
@section('page-title', 'Create Email Template')

@section('content')
    <form method="POST" action="{{ route('admin.email-templates.store') }}">
        @csrf
        @include('admin.email-templates.form', ['submitLabel' => 'Create template'])
    </form>
@endsection
