<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card mb-4">
            <div class="card-header"><h2 class="h5 mb-1">1. Branding</h2><p class="small text-secondary mb-0">Site identity, logos, and global theme colors.</p></div>
            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-6"><label class="form-label" for="site_name">Site name</label><input class="form-control @error('site_name') is-invalid @enderror" id="site_name" name="site_name" type="text" value="{{ old('site_name', $websiteSetting->site_name) }}" maxlength="255">@error('site_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label" for="site_tagline">Site tagline</label><input class="form-control @error('site_tagline') is-invalid @enderror" id="site_tagline" name="site_tagline" type="text" value="{{ old('site_tagline', $websiteSetting->site_tagline) }}" maxlength="255">@error('site_tagline')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label" for="primary_color">Primary color</label><input class="form-control @error('primary_color') is-invalid @enderror" id="primary_color" name="primary_color" type="text" value="{{ old('primary_color', $websiteSetting->primary_color) }}" maxlength="20" placeholder="#0d6efd">@error('primary_color')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label" for="secondary_color">Secondary color</label><input class="form-control @error('secondary_color') is-invalid @enderror" id="secondary_color" name="secondary_color" type="text" value="{{ old('secondary_color', $websiteSetting->secondary_color) }}" maxlength="20" placeholder="#6c757d">@error('secondary_color')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>

                <div class="row g-4">
                    @foreach (['logo' => ['Main logo', 'JPG, PNG, WebP, or SVG. Max 2 MB.'], 'white_logo' => ['White logo', 'For dark backgrounds. Max 2 MB.'], 'favicon' => ['Favicon', 'JPG, PNG, ICO, WebP, or SVG. Max 1 MB.'], 'og_image' => ['Default OG image', 'JPG, PNG, or WebP. Max 2 MB.']] as $field => [$label, $help])
                        <div class="col-md-6">
                            <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                            <div class="bg-light border rounded p-3 mb-2 text-center">
                                <img id="{{ $field }}-preview" class="image-preview {{ $websiteSetting->{$field} ? '' : 'd-none' }}" @if ($websiteSetting->{$field}) src="{{ asset($websiteSetting->{$field}) }}" @endif alt="{{ $label }} preview">
                                <span id="{{ $field }}-empty" class="small text-secondary {{ $websiteSetting->{$field} ? 'd-none' : '' }}">No image uploaded</span>
                            </div>
                            <input class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" type="file" data-image-input data-preview="{{ $field }}-preview" data-empty="{{ $field }}-empty" accept="{{ $field === 'favicon' ? '.jpg,.jpeg,.png,.ico,.webp,.svg' : ($field === 'og_image' ? '.jpg,.jpeg,.png,.webp' : '.jpg,.jpeg,.png,.webp,.svg') }}">
                            <div class="form-text">{{ $help }}</div>
                            @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @include('admin.components.media-picker', [
                                'inputName' => $field,
                                'previewUrl' => $websiteSetting->{$field} ? asset($websiteSetting->{$field}) : null,
                                'label' => $label,
                                'acceptType' => 'image',
                            ])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header"><h2 class="h5 mb-1">2. Contact Information</h2><p class="small text-secondary mb-0">Default public contact details and map embed.</p></div>
            <div class="card-body p-4"><div class="row g-3">
                <div class="col-md-4"><label class="form-label" for="phone">Phone</label><input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" type="text" value="{{ old('phone', $websiteSetting->phone) }}" maxlength="50">@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-4"><label class="form-label" for="email">Email</label><input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email', $websiteSetting->email) }}" maxlength="255">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-4"><label class="form-label" for="whatsapp">WhatsApp</label><input class="form-control @error('whatsapp') is-invalid @enderror" id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $websiteSetting->whatsapp) }}" maxlength="50">@error('whatsapp')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-12"><label class="form-label" for="address">Address</label><textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address', $websiteSetting->address) }}</textarea>@error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-12"><label class="form-label" for="google_map_embed">Google Map embed</label><textarea class="form-control font-monospace @error('google_map_embed') is-invalid @enderror" id="google_map_embed" name="google_map_embed" rows="4" placeholder='<iframe src="https://www.google.com/maps/embed?..." ...></iframe>'>{{ old('google_map_embed', $websiteSetting->google_map_embed) }}</textarea><div class="form-text">Only a Google Maps embed iframe is accepted.</div>@error('google_map_embed')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            </div></div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header"><h2 class="h5 mb-1">3. Social Links</h2><p class="small text-secondary mb-0">Full HTTPS links to your public profiles.</p></div>
            <div class="card-body p-4"><div class="row g-3">
                @foreach (['facebook_url' => 'Facebook', 'instagram_url' => 'Instagram', 'linkedin_url' => 'LinkedIn', 'youtube_url' => 'YouTube', 'twitter_url' => 'Twitter / X'] as $field => $label)
                    <div class="col-md-6"><label class="form-label" for="{{ $field }}">{{ $label }}</label><input class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" type="url" value="{{ old($field, $websiteSetting->{$field}) }}" maxlength="2048" placeholder="https://">@error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                @endforeach
            </div></div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header"><h2 class="h5 mb-1">4. SEO Settings</h2><p class="small text-secondary mb-0">Fallback metadata used when a page or post has no specific SEO values.</p></div>
            <div class="card-body p-4"><div class="row g-3">
                <div class="col-12"><label class="form-label" for="meta_title">Meta title</label><input class="form-control @error('meta_title') is-invalid @enderror" id="meta_title" name="meta_title" type="text" value="{{ old('meta_title', $websiteSetting->meta_title) }}" maxlength="255">@error('meta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-12"><label class="form-label" for="meta_description">Meta description</label><textarea class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" rows="3" maxlength="500">{{ old('meta_description', $websiteSetting->meta_description) }}</textarea>@error('meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-12"><label class="form-label" for="meta_keywords">Meta keywords</label><textarea class="form-control @error('meta_keywords') is-invalid @enderror" id="meta_keywords" name="meta_keywords" rows="2" maxlength="500" placeholder="keyword one, keyword two">{{ old('meta_keywords', $websiteSetting->meta_keywords) }}</textarea>@error('meta_keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            </div></div>
        </div>

        <div class="card content-card">
            <div class="card-header"><h2 class="h5 mb-1">5. Custom Code</h2><p class="small text-secondary mb-0">Advanced global CSS and JavaScript. Script/style tags are not needed.</p></div>
            <div class="card-body p-4"><div class="mb-3"><label class="form-label" for="custom_css">Custom CSS</label><textarea class="form-control font-monospace @error('custom_css') is-invalid @enderror" id="custom_css" name="custom_css" rows="9" spellcheck="false">{{ old('custom_css', $websiteSetting->custom_css) }}</textarea>@error('custom_css')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div><label class="form-label" for="custom_js">Custom JavaScript</label><textarea class="form-control font-monospace @error('custom_js') is-invalid @enderror" id="custom_js" name="custom_js" rows="9" spellcheck="false">{{ old('custom_js', $websiteSetting->custom_js) }}</textarea>@error('custom_js')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card sticky-xl-top" style="top: 96px;">
            <div class="card-header"><h2 class="h5 mb-1">6. Status</h2><p class="small text-secondary mb-0">Control whether these settings are public.</p></div>
            <div class="card-body p-4"><input name="status" type="hidden" value="0"><div class="form-check form-switch"><input class="form-check-input" id="status" name="status" type="checkbox" value="1" @checked(old('status', $websiteSetting->status ?? true))><label class="form-check-label" for="status">Active</label></div></div>
            <div class="card-footer bg-white d-grid py-3"><button class="btn btn-primary btn-lg" type="submit">Save website settings</button></div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.querySelectorAll('[data-image-input]').forEach((input) => {
            input.addEventListener('change', () => {
                const [file] = input.files;
                const preview = document.getElementById(input.dataset.preview);
                const empty = document.getElementById(input.dataset.empty);
                if (! file || ! preview) return;
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('d-none');
                empty?.classList.add('d-none');
            });
        });
    </script>
@endpush
