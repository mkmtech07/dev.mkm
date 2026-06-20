@extends('layouts.admin')

@section('title', 'Edit Testimonial')
@section('page-title', 'Edit Testimonial')

@section('content')
    <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.website.testimonials.form', ['submitLabel' => 'Save changes'])
    </form>
@endsection
