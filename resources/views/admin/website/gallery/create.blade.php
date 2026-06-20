@extends('layouts.admin')

@section('title', 'Create Gallery Image')
@section('page-title', 'Create Gallery Image')

@section('content')
    <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.website.gallery.form', ['submitLabel' => 'Create image'])
    </form>
@endsection
