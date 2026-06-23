@extends('layouts.admin')

@section('title', 'Footer Settings')
@section('page-title', 'Footer Settings')

@section('content')
    <form method="POST" action="{{ route('admin.website.footer.settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.website.footer.settings.form')
    </form>
@endsection
