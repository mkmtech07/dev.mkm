@extends('layouts.admin')

@section('title', 'Theme Settings')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Theme Settings</h4>

        <form action="{{ route('admin.website.theme-settings.reset') }}" method="POST"
              onsubmit="return confirm('Reset theme settings to default?')">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">
                Reset Defaults
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.website.theme-settings.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    @foreach(\App\Models\ThemeSetting::PUBLIC_FIELDS as $field)
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                {{ ucwords(str_replace('_', ' ', $field)) }}
                            </label>

                            <input type="text"
                                   name="{{ $field }}"
                                   class="form-control"
                                   value="{{ old($field, $themeSetting->$field ?? '') }}">
                        </div>
                    @endforeach

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="1" {{ old('status', $themeSetting->status ?? 1) == 1 ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="0" {{ old('status', $themeSetting->status ?? 1) == 0 ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Save Settings
                </button>
            </form>
        </div>
    </div>
</div>
@endsection