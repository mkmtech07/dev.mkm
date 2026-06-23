@extends('layouts.admin')
@section('title', 'Add Footer Social Link')
@section('page-title', 'Add Footer Social Link')
@section('content')<form method="POST" action="{{ route('admin.website.footer.social.store') }}">@csrf @include('admin.website.footer.social.form', ['submitLabel' => 'Create social link'])</form>@endsection
