@extends('layouts.admin')
@section('title', 'Edit Footer Link')
@section('page-title', 'Edit Footer Link')
@section('content')<form method="POST" action="{{ route('admin.website.footer.links.update', $footerLink) }}">@csrf @method('PUT') @include('admin.website.footer.links.form', ['submitLabel' => 'Save changes'])</form>@endsection
