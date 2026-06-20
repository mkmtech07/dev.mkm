@extends('layouts.admin')

@section('title', 'Create Blog Post')
@section('page-title', 'Create Blog Post')

@section('content')
    <form method="POST" action="{{ route('admin.blogs.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.website.blogs.form', ['submitLabel' => 'Create post'])
    </form>
@endsection
