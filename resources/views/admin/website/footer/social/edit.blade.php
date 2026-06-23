@extends('layouts.admin')
@section('title', 'Edit Footer Social Link')
@section('page-title', 'Edit Footer Social Link')
@section('content')<form method="POST" action="{{ route('admin.website.footer.social.update', $footerSocialLink) }}">@csrf @method('PUT') @include('admin.website.footer.social.form', ['submitLabel' => 'Save changes'])</form>@endsection
