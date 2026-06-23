@extends('layouts.admin')
@section('title','Edit Permission')
@section('page-title','Edit Permission')
@section('content')<form method="POST" action="{{ route('admin.permissions.update',$permission) }}">@csrf @method('PUT') @include('admin.permissions.form',['submitLabel'=>'Save permission'])</form>@endsection
