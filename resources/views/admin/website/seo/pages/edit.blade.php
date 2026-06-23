@extends('layouts.admin')
@section('title', 'Edit SEO Page')
@section('page-title', 'Edit SEO Page')
@section('content')<form method="POST" action="{{ route('admin.website.seo.pages.update', $seoPage) }}" enctype="multipart/form-data">@csrf @method('PUT') @include('admin.website.seo.pages.form', ['submitLabel' => 'Save SEO page'])</form>@endsection
