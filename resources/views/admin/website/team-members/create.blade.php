@extends('layouts.admin')

@section('title', 'Create Team Member')
@section('page-title', 'Create Team Member')

@section('content')
    <form method="POST" action="{{ route('admin.team-members.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.website.team-members.form', ['submitLabel' => 'Create member'])
    </form>
@endsection
