@extends('layouts.admin')

@section('title', 'Edit Gallery Image')
@section('page-title', 'Edit Gallery Image')

@section('content')
    <form method="POST" action="{{ route('admin.gallery.update', $gallery) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.website.gallery.form', ['submitLabel' => 'Save changes'])
    </form>
@endsection
