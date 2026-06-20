@extends('layouts.admin')

@section('title', 'Create FAQ')
@section('page-title', 'Create FAQ')

@section('content')
    <form method="POST" action="{{ route('admin.faqs.store') }}">
        @csrf
        @include('admin.website.faqs.form', ['submitLabel' => 'Create FAQ'])
    </form>
@endsection
