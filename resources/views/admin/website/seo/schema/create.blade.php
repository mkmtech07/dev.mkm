@extends('layouts.admin')
@section('title', 'Add Schema Markup')
@section('page-title', 'Add Schema Markup')
@section('content')<form method="POST" action="{{ route('admin.website.seo.schema.store') }}">@csrf @include('admin.website.seo.schema.form', ['submitLabel'=>'Create schema'])</form>@endsection
