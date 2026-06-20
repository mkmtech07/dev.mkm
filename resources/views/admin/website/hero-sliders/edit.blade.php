@extends('layouts.admin')

@section('title', 'Edit Hero Slider')
@section('page-title', 'Edit Hero Slider')

@section('content')
    <form method="POST" action="{{ route('admin.hero-sliders.update', $heroSlider) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.website.hero-sliders.form', ['submitLabel' => 'Save changes'])
    </form>
@endsection
