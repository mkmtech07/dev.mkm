@extends('layouts.admin')
@section('title','Edit Role')
@section('page-title','Edit Role')
@section('content')<form method="POST" action="{{ route('admin.roles.update',$role) }}">@csrf @method('PUT') @include('admin.roles.form',['submitLabel'=>'Save role'])</form>@endsection
