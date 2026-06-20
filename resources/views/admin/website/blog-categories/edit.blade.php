@extends('layouts.admin')

@section('title', 'Edit Blog Category')
@section('page-title', 'Edit Blog Category')

@section('content')
    <form method="POST" action="{{ route('admin.blog-categories.update', $blogCategory) }}">
        @csrf
        @method('PUT')
        @include('admin.website.blog-categories.form', ['submitLabel' => 'Save changes'])
    </form>
@endsection
