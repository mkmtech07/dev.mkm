@extends('layouts.admin')
@section('title', 'Edit Footer Section')
@section('page-title', 'Edit Footer Section')
@section('content')
    <form method="POST" action="{{ route('admin.website.footer.sections.update', $footerSection) }}">
        @csrf @method('PUT')
        @include('admin.website.footer.sections.form', ['submitLabel' => 'Save changes'])
    </form>
@endsection
