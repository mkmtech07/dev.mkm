@extends('layouts.admin')

@section('title', 'Upload Media')
@section('page-title', 'Upload Media')

@section('content')
    <form method="POST" action="{{ route('admin.website.media-library.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.website.media-library.form', ['submitLabel' => 'Upload file'])
    </form>
@endsection
