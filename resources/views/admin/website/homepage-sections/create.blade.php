@extends('layouts.admin')

@section('title', 'Add Homepage Section')
@section('page-title', 'Add Homepage Section')

@section('content')
    <form method="POST" action="{{ route('admin.website.homepage-sections.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.website.homepage-sections.form', ['submitLabel' => 'Create section'])
    </form>
@endsection
