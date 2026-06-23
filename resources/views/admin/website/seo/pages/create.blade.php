@extends('layouts.admin')
@section('title', 'Add SEO Page')
@section('page-title', 'Add SEO Page')
@section('content')<form method="POST" action="{{ route('admin.website.seo.pages.store') }}" enctype="multipart/form-data">@csrf @include('admin.website.seo.pages.form', ['submitLabel' => 'Create SEO page'])</form>@endsection
