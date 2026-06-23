@extends('layouts.admin')
@section('title', 'Add Lead')
@section('page-title', 'Add Lead')
@section('content')
    <form method="POST" action="{{ route('admin.leads.store') }}">@csrf @include('admin.leads.form', ['submitLabel' => 'Create lead'])</form>
@endsection
