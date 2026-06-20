@extends('layouts.admin')

@section('title', 'Create Testimonial')
@section('page-title', 'Create Testimonial')

@section('content')
    <form method="POST" action="{{ route('admin.testimonials.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.website.testimonials.form', ['submitLabel' => 'Create testimonial'])
    </form>
@endsection
