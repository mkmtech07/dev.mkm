@extends('layouts.admin')
@section('title', 'Edit Newsletter Subscriber')
@section('page-title', 'Edit Newsletter Subscriber')
@section('content')<form method="POST" action="{{ route('admin.newsletter-subscribers.update', $newsletterSubscriber) }}">@csrf @method('PUT') @include('admin.newsletter-subscribers.form', ['submitLabel' => 'Save changes'])</form>@endsection
