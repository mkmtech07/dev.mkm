@extends('layouts.admin')

@section('title', 'Add Page Block')
@section('page-title', 'Add Page Block')

@section('content')
    <form method="POST" action="{{ route('admin.website.page-blocks.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.website.page-blocks.form', ['submitLabel' => 'Create block'])
    </form>
@endsection
