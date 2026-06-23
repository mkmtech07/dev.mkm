@extends('layouts.admin')

@section('title', 'Website Settings')
@section('page-title', 'Website Settings')

@section('content')
    <form method="POST" action="{{ route('admin.website.settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.website.settings.form')
    </form>
@endsection
