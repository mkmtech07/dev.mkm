@extends('layouts.admin')

@section('title', 'Edit About Section')
@section('page-title', 'Edit About Section')

@section('content')
    <form method="POST" action="{{ route('admin.about.update', $aboutSection) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.website.about.form', ['submitLabel' => 'Save changes'])
    </form>
@endsection
