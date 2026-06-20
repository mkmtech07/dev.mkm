@extends('layouts.admin')

@section('title', 'Create Blog Category')
@section('page-title', 'Create Blog Category')

@section('content')
    <form method="POST" action="{{ route('admin.blog-categories.store') }}">
        @csrf
        @include('admin.website.blog-categories.form', ['submitLabel' => 'Create category'])
    </form>
@endsection
