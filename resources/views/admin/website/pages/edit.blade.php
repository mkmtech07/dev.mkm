@extends('layouts.admin')

@section('title', 'Edit Page')
@section('page-title', 'Edit Page')

@section('content')
    <form method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.website.pages.form', ['submitLabel' => 'Save changes'])
    </form>
@endsection
