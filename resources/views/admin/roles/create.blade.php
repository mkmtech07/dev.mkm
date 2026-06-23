@extends('layouts.admin')
@section('title','Create Role')
@section('page-title','Create Role')
@section('content')<form method="POST" action="{{ route('admin.roles.store') }}">@csrf @include('admin.roles.form',['submitLabel'=>'Create role'])</form>@endsection
