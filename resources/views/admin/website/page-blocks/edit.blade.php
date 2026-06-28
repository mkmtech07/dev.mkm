@extends('layouts.admin')

@section('title', 'Edit Page Block')
@section('page-title', 'Edit Page Block')

@section('content')
    <form method="POST" action="{{ route('admin.website.page-blocks.update', $pageBlock) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.website.page-blocks.form', ['submitLabel' => 'Save changes'])
    </form>
@endsection
