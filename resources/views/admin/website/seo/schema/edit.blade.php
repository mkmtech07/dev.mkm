@extends('layouts.admin')
@section('title', 'Edit Schema Markup')
@section('page-title', 'Edit Schema Markup')
@section('content')<form method="POST" action="{{ route('admin.website.seo.schema.update', $schemaMarkup) }}">@csrf @method('PUT') @include('admin.website.seo.schema.form', ['submitLabel'=>'Save schema'])</form>@endsection
