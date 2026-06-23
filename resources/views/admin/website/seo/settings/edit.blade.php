@extends('layouts.admin')
@section('title', 'SEO Settings')
@section('page-title', 'SEO Settings')
@section('content')
    <form method="POST" action="{{ route('admin.website.seo.settings.update') }}">@csrf @method('PUT') @include('admin.website.seo.settings.form')</form>
@endsection
