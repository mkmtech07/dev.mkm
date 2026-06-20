@extends('layouts.admin')

@section('title', 'Create Page')
@section('page-title', 'Create Page')

@section('content')
    <form method="POST" action="{{ route('admin.pages.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.website.pages.form', ['submitLabel' => 'Create page'])
    </form>
@endsection
