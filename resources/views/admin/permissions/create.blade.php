@extends('layouts.admin')
@section('title','Create Permission')
@section('page-title','Create Permission')
@section('content')<form method="POST" action="{{ route('admin.permissions.store') }}">@csrf @include('admin.permissions.form',['submitLabel'=>'Create permission'])</form>@endsection
