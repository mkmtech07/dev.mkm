@extends('layouts.admin')

@section('title', 'Edit Media')
@section('page-title', 'Edit Media')

@section('content')
    <form method="POST" action="{{ route('admin.website.media-library.update', $mediaFile) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('admin.website.media-library.form', ['submitLabel' => 'Save changes'])
    </form>
@endsection
