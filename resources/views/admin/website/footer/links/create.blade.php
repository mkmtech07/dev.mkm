@extends('layouts.admin')
@section('title', 'Add Footer Link')
@section('page-title', 'Add Footer Link')
@section('content')<form method="POST" action="{{ route('admin.website.footer.links.store') }}">@csrf @include('admin.website.footer.links.form', ['submitLabel' => 'Create link'])</form>@endsection
