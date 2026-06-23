@extends('layouts.admin')

@section('title', 'Edit Homepage Section')
@section('page-title', 'Edit Homepage Section')

@section('content')
    <form method="POST" action="{{ route('admin.website.homepage-sections.update', $homepageSection) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('admin.website.homepage-sections.form', ['submitLabel' => 'Save changes'])
    </form>
@endsection
