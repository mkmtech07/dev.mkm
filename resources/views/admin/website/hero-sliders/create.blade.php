@extends('layouts.admin')

@section('title', 'Create Hero Slider')
@section('page-title', 'Create Hero Slider')

@section('content')
    <form method="POST" action="{{ route('admin.hero-sliders.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.website.hero-sliders.form', ['submitLabel' => 'Create slider'])
    </form>
@endsection
