@extends('layouts.admin')

@section('title', 'Create About Section')
@section('page-title', 'Create About Section')

@section('content')
    <form method="POST" action="{{ route('admin.about.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.website.about.form', ['submitLabel' => 'Create section'])
    </form>
@endsection
