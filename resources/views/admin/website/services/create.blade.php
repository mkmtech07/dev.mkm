@extends('layouts.admin')

@section('title', 'Create Service')
@section('page-title', 'Create Service')

@section('content')
    <form method="POST" action="{{ route('admin.services.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.website.services.form', ['submitLabel' => 'Create service'])
    </form>
@endsection
