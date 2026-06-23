@extends('layouts.admin')

@section('title', 'Edit Menu')
@section('page-title', 'Edit Menu')

@section('content')
    <form method="POST" action="{{ route('admin.menus.update', $menu) }}">
        @csrf
        @method('PUT')
        @include('admin.website.menus.form', ['submitLabel' => 'Save changes'])
    </form>
@endsection
