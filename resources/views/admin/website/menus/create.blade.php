@extends('layouts.admin')

@section('title', 'Add Menu')
@section('page-title', 'Add Menu')

@section('content')
    <form method="POST" action="{{ route('admin.menus.store') }}">
        @csrf
        @include('admin.website.menus.form', ['submitLabel' => 'Create menu'])
    </form>
@endsection
