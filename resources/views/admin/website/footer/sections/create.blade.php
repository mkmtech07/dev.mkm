@extends('layouts.admin')
@section('title', 'Add Footer Section')
@section('page-title', 'Add Footer Section')
@section('content')
    <form method="POST" action="{{ route('admin.website.footer.sections.store') }}">
        @csrf
        @include('admin.website.footer.sections.form', ['submitLabel' => 'Create section'])
    </form>
@endsection
