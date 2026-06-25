@extends('layouts.admin')

@section('title', 'Edit Email Template')
@section('page-title', 'Edit Email Template')

@section('content')
    <form method="POST" action="{{ route('admin.email-templates.update', $template) }}">
        @csrf
        @method('PUT')
        @include('admin.email-templates.form', ['submitLabel' => 'Save template'])
    </form>
@endsection
