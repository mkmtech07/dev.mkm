@php
    $settingsValue = old('settings');
    if ($settingsValue === null) {
        $settingsValue = $homepageSection->settings
            ? json_encode($homepageSection->settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            : '';
    } elseif (is_array($settingsValue)) {
        $settingsValue = json_encode($settingsValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
@endphp

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card mb-4">
            <div class="card-header"><h2 class="h5 mb-1">Section content</h2><p class="small text-secondary mb-0">Choose a section type and add its public content.</p></div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="type">Section type <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            @foreach (\App\Models\HomepageSection::TYPES as $sectionType)
                                <option value="{{ $sectionType }}" @selected(old('type', $homepageSection->type) === $sectionType)>{{ ucfirst($sectionType) }}</option>
                            @endforeach
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="section_key">Section key</label>
                        <input class="form-control @error('section_key') is-invalid @enderror" id="section_key" name="section_key" type="text" value="{{ old('section_key', $homepageSection->section_key) }}" maxlength="255" placeholder="example: main-hero">
                        <div class="form-text">Optional stable key for links or styling.</div>
                        @error('section_key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="title">Title</label>
                        <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" type="text" value="{{ old('title', $homepageSection->title) }}" maxlength="255">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="subtitle">Subtitle</label>
                        <input class="form-control @error('subtitle') is-invalid @enderror" id="subtitle" name="subtitle" type="text" value="{{ old('subtitle', $homepageSection->subtitle) }}" maxlength="255">
                        @error('subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="content">Content</label>
                        <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="7">{{ old('content', $homepageSection->content) }}</textarea>
                        <div class="form-text">Content is displayed as safe plain text with line breaks preserved.</div>
                        @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="button_text">Button text</label>
                        <input class="form-control @error('button_text') is-invalid @enderror" id="button_text" name="button_text" type="text" value="{{ old('button_text', $homepageSection->button_text) }}" maxlength="255">
                        @error('button_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="button_url">Button URL</label>
                        <input class="form-control @error('button_url') is-invalid @enderror" id="button_url" name="button_url" type="text" value="{{ old('button_url', $homepageSection->button_url) }}" maxlength="500" placeholder="/contact or https://example.com">
                        @error('button_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card content-card">
            <div class="card-header"><h2 class="h5 mb-1">Advanced settings</h2><p class="small text-secondary mb-0">Optional JSON data for future section-specific presentation.</p></div>
            <div class="card-body p-4">
                <label class="form-label" for="settings">Settings JSON</label>
                <textarea class="form-control font-monospace @error('settings') is-invalid @enderror" id="settings" name="settings" rows="7" placeholder='{"layout": "wide"}'>{{ $settingsValue }}</textarea>
                @error('settings') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card mb-4">
            <div class="card-header"><h2 class="h5 mb-1">Images</h2><p class="small text-secondary mb-0">Upload responsive section artwork.</p></div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <label class="form-label" for="image">Section image</label>
                    <input class="form-control @error('image') is-invalid @enderror" id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,.svg,image/jpeg,image/png,image/webp,image/svg+xml" data-preview="image-preview">
                    <div class="form-text">JPG, PNG, WebP, or SVG. Maximum 2 MB.</div>
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <img id="image-preview" class="img-fluid rounded border mt-3 {{ $homepageSection->image ? '' : 'd-none' }}" src="{{ $homepageSection->image ? asset($homepageSection->image) : '' }}" alt="Section image preview">
                    @include('admin.components.media-picker', [
                        'inputName' => 'image',
                        'previewUrl' => $homepageSection->image ? asset($homepageSection->image) : null,
                        'label' => 'Section image',
                        'acceptType' => 'image',
                    ])
                </div>
                <div>
                    <label class="form-label" for="background_image">Background image</label>
                    <input class="form-control @error('background_image') is-invalid @enderror" id="background_image" name="background_image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-preview="background-preview">
                    <div class="form-text">JPG, PNG, or WebP. Maximum 4 MB.</div>
                    @error('background_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <img id="background-preview" class="img-fluid rounded border mt-3 {{ $homepageSection->background_image ? '' : 'd-none' }}" src="{{ $homepageSection->background_image ? asset($homepageSection->background_image) : '' }}" alt="Background image preview">
                    @include('admin.components.media-picker', [
                        'inputName' => 'background_image',
                        'previewUrl' => $homepageSection->background_image ? asset($homepageSection->background_image) : null,
                        'label' => 'Background image',
                        'acceptType' => 'image',
                    ])
                </div>
            </div>
        </div>

        <div class="card content-card">
            <div class="card-header"><h2 class="h5 mb-1">Display</h2><p class="small text-secondary mb-0">Control colors, order, and visibility.</p></div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label" for="background_color">Background</label>
                        <input class="form-control @error('background_color') is-invalid @enderror" id="background_color" name="background_color" type="text" value="{{ old('background_color', $homepageSection->background_color) }}" maxlength="20" placeholder="#ffffff">
                        @error('background_color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="text_color">Text</label>
                        <input class="form-control @error('text_color') is-invalid @enderror" id="text_color" name="text_color" type="text" value="{{ old('text_color', $homepageSection->text_color) }}" maxlength="20" placeholder="#212529">
                        @error('text_color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="sort_order">Sort order</label>
                        <input class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $homepageSection->sort_order ?? 0) }}" min="0">
                        @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <input name="status" type="hidden" value="0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="status" name="status" type="checkbox" value="1" @checked(old('status', $homepageSection->status ?? true))>
                            <label class="form-check-label" for="status">Active on homepage</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2 py-3">
                <a class="btn btn-light" href="{{ route('admin.website.homepage-sections.index') }}">Cancel</a>
                <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.querySelectorAll('[data-preview]').forEach((input) => {
            input.addEventListener('change', () => {
                const preview = document.getElementById(input.dataset.preview);
                const file = input.files?.[0];

                if (!preview || !file) return;

                preview.src = URL.createObjectURL(file);
                preview.classList.remove('d-none');
            });
        });
    </script>
@endpush
