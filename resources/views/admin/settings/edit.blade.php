@extends('layouts.admin')

@section('title', 'Website Settings')
@section('page-title', 'Website Settings')

@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card content-card mb-4">
                    <div class="card-header">
                        <h2 class="h5 mb-1">General Information</h2>
                        <p class="text-secondary small mb-0">Your website name, tagline, and contact details.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="site_name">Site name <span class="text-danger">*</span></label>
                                <input class="form-control @error('site_name') is-invalid @enderror"
                                       id="site_name"
                                       name="site_name"
                                       type="text"
                                       value="{{ old('site_name', $settings->site_name) }}"
                                       required>
                                @error('site_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="tagline">Tagline</label>
                                <input class="form-control @error('tagline') is-invalid @enderror"
                                       id="tagline"
                                       name="tagline"
                                       type="text"
                                       value="{{ old('tagline', $settings->tagline) }}">
                                @error('tagline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="phone">Phone</label>
                                <input class="form-control @error('phone') is-invalid @enderror"
                                       id="phone"
                                       name="phone"
                                       type="text"
                                       value="{{ old('phone', $settings->phone) }}">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="email">Email</label>
                                <input class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       type="email"
                                       value="{{ old('email', $settings->email) }}">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="whatsapp_number">WhatsApp number</label>
                                <input class="form-control @error('whatsapp_number') is-invalid @enderror"
                                       id="whatsapp_number"
                                       name="whatsapp_number"
                                       type="text"
                                       value="{{ old('whatsapp_number', $settings->whatsapp_number) }}">
                                @error('whatsapp_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="address">Address</label>
                                <textarea class="form-control @error('address') is-invalid @enderror"
                                          id="address"
                                          name="address"
                                          rows="3">{{ old('address', $settings->address) }}</textarea>
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card content-card mb-4">
                    <div class="card-header">
                        <h2 class="h5 mb-1">Social Media</h2>
                        <p class="text-secondary small mb-0">Add full links to your social profiles.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="facebook_url">Facebook URL</label>
                                <input class="form-control @error('facebook_url') is-invalid @enderror"
                                       id="facebook_url"
                                       name="facebook_url"
                                       type="url"
                                       value="{{ old('facebook_url', $settings->facebook_url) }}"
                                       placeholder="https://facebook.com/your-page">
                                @error('facebook_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="instagram_url">Instagram URL</label>
                                <input class="form-control @error('instagram_url') is-invalid @enderror"
                                       id="instagram_url"
                                       name="instagram_url"
                                       type="url"
                                       value="{{ old('instagram_url', $settings->instagram_url) }}"
                                       placeholder="https://instagram.com/your-profile">
                                @error('instagram_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="youtube_url">YouTube URL</label>
                                <input class="form-control @error('youtube_url') is-invalid @enderror"
                                       id="youtube_url"
                                       name="youtube_url"
                                       type="url"
                                       value="{{ old('youtube_url', $settings->youtube_url) }}"
                                       placeholder="https://youtube.com/@your-channel">
                                @error('youtube_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card content-card">
                    <div class="card-header">
                        <h2 class="h5 mb-1">Search Engine Optimization</h2>
                        <p class="text-secondary small mb-0">Default title and description for search results.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label" for="meta_title">Meta title</label>
                            <input class="form-control @error('meta_title') is-invalid @enderror"
                                   id="meta_title"
                                   name="meta_title"
                                   type="text"
                                   value="{{ old('meta_title', $settings->meta_title) }}">
                            @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label" for="meta_description">Meta description</label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror"
                                      id="meta_description"
                                      name="meta_description"
                                      rows="4">{{ old('meta_description', $settings->meta_description) }}</textarea>
                            @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card content-card mb-4">
                    <div class="card-header">
                        <h2 class="h5 mb-1">Branding</h2>
                        <p class="text-secondary small mb-0">Upload your website logo and browser icon.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label" for="logo">Logo</label>
                            @if ($settings->logo)
                                <div class="mb-3">
                                    <img class="image-preview" src="{{ asset('storage/'.$settings->logo) }}" alt="Current logo">
                                </div>
                            @endif
                            <input class="form-control @error('logo') is-invalid @enderror"
                                   id="logo"
                                   name="logo"
                                   type="file"
                                   accept=".jpg,.jpeg,.png,.webp">
                            <div class="form-text">JPG, PNG, or WebP. Maximum 2 MB.</div>
                            @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="form-label" for="favicon">Favicon</label>
                            @if ($settings->favicon)
                                <div class="mb-3">
                                    <img class="image-preview" src="{{ asset('storage/'.$settings->favicon) }}" alt="Current favicon">
                                </div>
                            @endif
                            <input class="form-control @error('favicon') is-invalid @enderror"
                                   id="favicon"
                                   name="favicon"
                                   type="file"
                                   accept=".ico,.png">
                            <div class="form-text">ICO or PNG. Maximum 1 MB.</div>
                            @error('favicon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button class="btn btn-primary btn-lg" type="submit">Save settings</button>
                </div>
            </div>
        </div>
    </form>
@endsection
