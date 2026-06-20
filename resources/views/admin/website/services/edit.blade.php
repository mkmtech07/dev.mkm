@extends('layouts.admin')

@section('title', 'Edit Service')
@section('page-title', 'Edit Service')

@section('content')
    <form method="POST" action="{{ route('admin.services.update', $service) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.website.services.form', ['submitLabel' => 'Save changes'])
    </form>
@endsection
