<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Footer content</h2>
                <p class="small text-secondary mb-0">Configure the footer introduction and public contact details.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label" for="footer_description">Description</label>
                    <textarea class="form-control @error('footer_description') is-invalid @enderror" id="footer_description" name="footer_description" rows="4" maxlength="5000">{{ old('footer_description', $footerSetting->footer_description) }}</textarea>
                    @error('footer_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Phone</label>
                        <input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" type="text" value="{{ old('phone', $footerSetting->phone) }}" maxlength="50">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email', $footerSetting->email) }}" maxlength="255">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="whatsapp">WhatsApp</label>
                        <input class="form-control @error('whatsapp') is-invalid @enderror" id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $footerSetting->whatsapp) }}" maxlength="50">
                        @error('whatsapp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="copyright_text">Copyright text</label>
                        <input class="form-control @error('copyright_text') is-invalid @enderror" id="copyright_text" name="copyright_text" type="text" value="{{ old('copyright_text', $footerSetting->copyright_text) }}" maxlength="255">
                        @error('copyright_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="address">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" maxlength="5000">{{ old('address', $footerSetting->address) }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card mb-4">
            <div class="card-header"><h2 class="h5 mb-0">Footer logo</h2></div>
            <div class="card-body p-4">
                <img id="footer-logo-preview" class="image-preview mb-3 {{ $footerSetting->footer_logo ? '' : 'd-none' }}" @if ($footerSetting->footer_logo) src="{{ asset($footerSetting->footer_logo) }}" @endif alt="Footer logo preview">
                <input class="form-control @error('footer_logo') is-invalid @enderror" id="footer_logo" name="footer_logo" type="file" accept=".jpg,.jpeg,.png,.webp">
                <div class="form-text">JPG, PNG, or WebP. Maximum 2 MB.</div>
                @error('footer_logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @include('admin.components.media-picker', [
                    'inputName' => 'footer_logo',
                    'previewUrl' => $footerSetting->footer_logo ? asset($footerSetting->footer_logo) : null,
                    'label' => 'Footer logo',
                    'acceptType' => 'image',
                ])
                <input name="remove_footer_logo" type="hidden" value="0">
                @if ($footerSetting->footer_logo)
                    <div class="form-check mt-3">
                        <input class="form-check-input" id="remove_footer_logo" name="remove_footer_logo" type="checkbox" value="1">
                        <label class="form-check-label" for="remove_footer_logo">Remove current logo</label>
                    </div>
                @endif
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header"><h2 class="h5 mb-0">Visibility</h2></div>
            <div class="card-body p-4">
                <input name="newsletter_status" type="hidden" value="0">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" id="newsletter_status" name="newsletter_status" type="checkbox" value="1" @checked(old('newsletter_status', $footerSetting->newsletter_status))>
                    <label class="form-check-label" for="newsletter_status">Enable newsletter sections</label>
                </div>
                <input name="status" type="hidden" value="0">
                <div class="form-check form-switch">
                    <input class="form-check-input" id="status" name="status" type="checkbox" value="1" @checked(old('status', $footerSetting->status))>
                    <label class="form-check-label" for="status">Dynamic footer active</label>
                </div>
            </div>
        </div>

        <div class="d-grid"><button class="btn btn-primary btn-lg" type="submit">Save footer settings</button></div>
    </div>
</div>

@push('scripts')
    <script>
        document.getElementById('footer_logo')?.addEventListener('change', (event) => {
            const [file] = event.target.files;
            const preview = document.getElementById('footer-logo-preview');
            if (! file || ! preview) return;
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        });
    </script>
@endpush
