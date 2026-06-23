@extends('layouts.admin')
@section('title', 'Edit Lead')
@section('page-title', 'Edit Lead')
@section('content')
    <form method="POST" action="{{ route('admin.leads.update', $lead) }}">@csrf @method('PUT') @include('admin.leads.form', ['submitLabel' => 'Save changes'])</form>
@endsection
