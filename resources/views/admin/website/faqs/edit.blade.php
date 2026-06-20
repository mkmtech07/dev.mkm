@extends('layouts.admin')

@section('title', 'Edit FAQ')
@section('page-title', 'Edit FAQ')

@section('content')
    <form method="POST" action="{{ route('admin.faqs.update', $faq) }}">
        @csrf
        @method('PUT')
        @include('admin.website.faqs.form', ['submitLabel' => 'Save changes'])
    </form>
@endsection
