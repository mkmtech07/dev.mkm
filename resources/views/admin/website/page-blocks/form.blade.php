@php
    $settingsValue = old('settings');
    if ($settingsValue === null) {
        $settingsValue = $pageBlock->settings
            ? json_encode($pageBlock->settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            : '';
    } elseif (is_array($settingsValue)) {
        $settingsValue = json_encode($settingsValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
@endphp

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">1. Page and block type</h2>
                <p class="small text-secondary mb-0">Assign this block to a page and choose how it should render.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label" for="page_id">Page <span class="text-danger">*</span></label>
                        <select class="form-select @error('page_id') is-invalid @enderror" id="page_id" name="page_id" required>
                            <option value="">Select page</option>
                            @foreach ($pages as $page)
                                <option value="{{ $page->id }}" @selected((int) old('page_id', $pageBlock->page_id) === $page->id)>
                                    {{ $page->title }} (/{{ $page->slug }})
                                </option>
                            @endforeach
                        </select>
                        @error('page_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="type">Block type <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            @foreach (\App\Models\PageBlock::TYPES as $blockType)
                                <option value="{{ $blockType }}" @selected(old('type', $pageBlock->type) === $blockType)>
                                    {{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $blockType)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="block_key">Block key</label>
                        <input class="form-control @error('block_key') is-invalid @enderror" id="block_key" name="block_key" type="text" value="{{ old('block_key', $pageBlock->block_key) }}" maxlength="255" placeholder="example: pricing-top">
                        <div class="form-text">Optional stable key for links or styling.</div>
                        @error('block_key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="sort_order">Sort order</label>
                        <input class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $pageBlock->sort_order ?? 0) }}" min="0">
                        <div class="form-text">Lower numbers render first.</div>
                        @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">2. Content</h2>
                <p class="small text-secondary mb-0">Add the text shown inside this page section.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="title">Title</label>
                        <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" type="text" value="{{ old('title', $pageBlock->title) }}" maxlength="255">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="subtitle">Subtitle</label>
                        <input class="form-control @error('subtitle') is-invalid @enderror" id="subtitle" name="subtitle" type="text" value="{{ old('subtitle', $pageBlock->subtitle) }}" maxlength="255">
                        @error('subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="content">Content</label>
                        <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="8">{{ old('content', $pageBlock->content) }}</textarea>
                        <div class="form-text">Custom HTML is sanitized on output. Script tags are not allowed.</div>
                        @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">6. Advanced settings</h2>
                <p class="small text-secondary mb-0">Optional JSON for features, pricing plans, layout choices, or block-specific options.</p>
            </div>
            <div class="card-body p-4">
                <label class="form-label" for="settings">Settings JSON</label>
                <textarea class="form-control font-monospace @error('settings') is-invalid @enderror" id="settings" name="settings" rows="10" placeholder='{"items": [{"title": "Fast Performance", "description": "Optimized website speed", "icon": "bi bi-lightning"}]}'>{{ $settingsValue }}</textarea>
                @error('settings') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">3. Media</h2>
                <p class="small text-secondary mb-0">Upload artwork for image, hero, CTA, or text-image blocks.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <label class="form-label" for="image">Image</label>
                    <input class="form-control @error('image') is-invalid @enderror" id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,.svg,image/jpeg,image/png,image/webp,image/svg+xml" data-preview="image-preview">
                    <div class="form-text">JPG, PNG, WebP, or SVG. Maximum 2 MB.</div>
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <img id="image-preview" class="img-fluid rounded border mt-3 {{ $pageBlock->image ? '' : 'd-none' }}" src="{{ $pageBlock->image ? asset($pageBlock->image) : '' }}" alt="Block image preview">
                    @include('admin.components.media-picker', [
                        'inputName' => 'image',
                        'previewUrl' => $pageBlock->image ? asset($pageBlock->image) : null,
                        'label' => 'Image',
                        'acceptType' => 'image',
                    ])
                </div>
                <div>
                    <label class="form-label" for="background_image">Background image</label>
                    <input class="form-control @error('background_image') is-invalid @enderror" id="background_image" name="background_image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-preview="background-preview">
                    <div class="form-text">JPG, PNG, or WebP. Maximum 4 MB.</div>
                    @error('background_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <img id="background-preview" class="img-fluid rounded border mt-3 {{ $pageBlock->background_image ? '' : 'd-none' }}" src="{{ $pageBlock->background_image ? asset($pageBlock->background_image) : '' }}" alt="Background image preview">
                    @include('admin.components.media-picker', [
                        'inputName' => 'background_image',
                        'previewUrl' => $pageBlock->background_image ? asset($pageBlock->background_image) : null,
                        'label' => 'Background image',
                        'acceptType' => 'image',
                    ])
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">4. Buttons</h2>
                <p class="small text-secondary mb-0">Add primary and secondary calls to action.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label" for="button_text">Button text</label>
                    <input class="form-control @error('button_text') is-invalid @enderror" id="button_text" name="button_text" type="text" value="{{ old('button_text', $pageBlock->button_text) }}" maxlength="255">
                    @error('button_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="button_url">Button URL</label>
                    <input class="form-control @error('button_url') is-invalid @enderror" id="button_url" name="button_url" type="text" value="{{ old('button_url', $pageBlock->button_url) }}" maxlength="500" placeholder="/contact or https://example.com">
                    @error('button_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="secondary_button_text">Secondary button text</label>
                    <input class="form-control @error('secondary_button_text') is-invalid @enderror" id="secondary_button_text" name="secondary_button_text" type="text" value="{{ old('secondary_button_text', $pageBlock->secondary_button_text) }}" maxlength="255">
                    @error('secondary_button_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label" for="secondary_button_url">Secondary button URL</label>
                    <input class="form-control @error('secondary_button_url') is-invalid @enderror" id="secondary_button_url" name="secondary_button_url" type="text" value="{{ old('secondary_button_url', $pageBlock->secondary_button_url) }}" maxlength="500" placeholder="#details">
                    @error('secondary_button_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">5. Design</h2>
                <p class="small text-secondary mb-0">Optional colors for this block.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label" for="background_color">Background</label>
                        <input class="form-control @error('background_color') is-invalid @enderror" id="background_color" name="background_color" type="text" value="{{ old('background_color', $pageBlock->background_color) }}" maxlength="20" placeholder="#ffffff">
                        @error('background_color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="text_color">Text</label>
                        <input class="form-control @error('text_color') is-invalid @enderror" id="text_color" name="text_color" type="text" value="{{ old('text_color', $pageBlock->text_color) }}" maxlength="20" placeholder="#212529">
                        @error('text_color') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card content-card">
            <div class="card-header">
                <h2 class="h5 mb-1">7. Status</h2>
                <p class="small text-secondary mb-0">Publish or hide this block.</p>
            </div>
            <div class="card-body p-4">
                <input name="status" type="hidden" value="0">
                <div class="form-check form-switch">
                    <input class="form-check-input" id="status" name="status" type="checkbox" value="1" @checked(old('status', $pageBlock->status ?? true))>
                    <label class="form-check-label" for="status">Active on public page</label>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2 py-3">
                <a class="btn btn-light" href="{{ route('admin.website.page-blocks.index', $pageBlock->page_id ? ['page_id' => $pageBlock->page_id] : []) }}">Cancel</a>
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
