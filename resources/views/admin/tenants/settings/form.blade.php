<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Branding</h2>
                <p class="text-secondary small mb-0">Optional demo branding for admin reference and future template use.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label" for="primary_color">Primary color</label>
                        <input class="form-control form-control-color @error('primary_color') is-invalid @enderror" id="primary_color" name="primary_color" type="color" value="{{ old('primary_color', $setting->primary_color ?: '#2563eb') }}">
                        @error('primary_color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="secondary_color">Secondary color</label>
                        <input class="form-control form-control-color @error('secondary_color') is-invalid @enderror" id="secondary_color" name="secondary_color" type="color" value="{{ old('secondary_color', $setting->secondary_color ?: '#0f172a') }}">
                        @error('secondary_color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="accent_color">Accent color</label>
                        <input class="form-control form-control-color @error('accent_color') is-invalid @enderror" id="accent_color" name="accent_color" type="color" value="{{ old('accent_color', $setting->accent_color ?: '#16a34a') }}">
                        @error('accent_color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="logo">Logo</label>
                        @if($setting->logo)
                            <div class="mb-2"><img class="img-fluid rounded border" src="{{ asset($setting->logo) }}" alt="Tenant logo" style="max-height: 80px;"></div>
                        @endif
                        <input class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" type="file" accept=".jpg,.jpeg,.png,.webp,.svg">
                        @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="favicon">Favicon</label>
                        @if($setting->favicon)
                            <div class="mb-2"><img class="rounded border" src="{{ asset($setting->favicon) }}" alt="Tenant favicon" width="48" height="48"></div>
                        @endif
                        <input class="form-control @error('favicon') is-invalid @enderror" id="favicon" name="favicon" type="file" accept=".jpg,.jpeg,.png,.ico,.webp,.svg">
                        @error('favicon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card content-card">
            <div class="card-header">
                <h2 class="h5 mb-1">SEO and custom code</h2>
                <p class="text-secondary small mb-0">Tenant-specific metadata kept separate from global SEO settings.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label" for="meta_title">Meta title</label>
                    <input class="form-control @error('meta_title') is-invalid @enderror" id="meta_title" name="meta_title" type="text" value="{{ old('meta_title', $setting->meta_title) }}" maxlength="255">
                    @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="meta_description">Meta description</label>
                    <textarea class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" rows="3" maxlength="500">{{ old('meta_description', $setting->meta_description) }}</textarea>
                    @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="custom_css">Custom CSS</label>
                    <textarea class="form-control font-monospace @error('custom_css') is-invalid @enderror" id="custom_css" name="custom_css" rows="6">{{ old('custom_css', $setting->custom_css) }}</textarea>
                    @error('custom_css') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label" for="custom_js">Custom JavaScript</label>
                    <textarea class="form-control font-monospace @error('custom_js') is-invalid @enderror" id="custom_js" name="custom_js" rows="6">{{ old('custom_js', $setting->custom_js) }}</textarea>
                    @error('custom_js') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Contact</h2>
                <p class="text-secondary small mb-0">Reference details for this demo tenant.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label" for="contact_email">Contact email</label>
                    <input class="form-control @error('contact_email') is-invalid @enderror" id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $setting->contact_email) }}" maxlength="255">
                    @error('contact_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="contact_phone">Contact phone</label>
                    <input class="form-control @error('contact_phone') is-invalid @enderror" id="contact_phone" name="contact_phone" type="text" value="{{ old('contact_phone', $setting->contact_phone) }}" maxlength="50">
                    @error('contact_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="whatsapp">WhatsApp</label>
                    <input class="form-control @error('whatsapp') is-invalid @enderror" id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $setting->whatsapp) }}" maxlength="50">
                    @error('whatsapp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label" for="address">Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="4" maxlength="5000">{{ old('address', $setting->address) }}</textarea>
                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Locale</h2>
                <p class="text-secondary small mb-0">Basic regional defaults.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label" for="timezone">Timezone <span class="text-danger">*</span></label>
                    <input class="form-control @error('timezone') is-invalid @enderror" id="timezone" name="timezone" type="text" value="{{ old('timezone', $setting->timezone ?: config('app.timezone')) }}" maxlength="100" required>
                    @error('timezone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label" for="locale">Locale <span class="text-danger">*</span></label>
                    <input class="form-control @error('locale') is-invalid @enderror" id="locale" name="locale" type="text" value="{{ old('locale', $setting->locale ?: config('app.locale')) }}" maxlength="20" required>
                    @error('locale') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">Update settings</button>
            <a class="btn btn-light" href="{{ route('admin.tenants.show', $tenant) }}">Cancel</a>
        </div>
    </div>
</div>
