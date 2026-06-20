@extends('layouts.admin')

@section('title', 'Edit Blog Post')
@section('page-title', 'Edit Blog Post')

@section('content')
    <form method="POST" action="{{ route('admin.blogs.update', $blog) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.website.blogs.form', ['submitLabel' => 'Save changes'])
    </form>
@endsection
