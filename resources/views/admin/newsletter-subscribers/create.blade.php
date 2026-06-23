@extends('layouts.admin')
@section('title', 'Add Newsletter Subscriber')
@section('page-title', 'Add Newsletter Subscriber')
@section('content')<form method="POST" action="{{ route('admin.newsletter-subscribers.store') }}">@csrf @include('admin.newsletter-subscribers.form', ['submitLabel' => 'Create subscriber'])</form>@endsection
