@extends('layouts.admin')

@section('title', 'Edit Team Member')
@section('page-title', 'Edit Team Member')

@section('content')
    <form method="POST" action="{{ route('admin.team-members.update', $teamMember) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.website.team-members.form', ['submitLabel' => 'Save changes'])
    </form>
@endsection
