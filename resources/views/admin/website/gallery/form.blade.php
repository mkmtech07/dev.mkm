<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card">
            <div class="card-header">
                <h2 class="h5 mb-1">Image details</h2>
                <p class="text-secondary small mb-0">Add descriptive content for visitors and search engines.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                    <input
                        class="form-control @error('title') is-invalid @enderror"
                        id="title"
                        name="title"
                        type="text"
                        value="{{ old('title', $gallery->title) }}"
                        maxlength="255"
                        required
                    >
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="category">Category</label>
                        <input
                            class="form-control @error('category') is-invalid @enderror"
                            id="category"
                            name="category"
                            type="text"
                            value="{{ old('category', $gallery->category) }}"
                            maxlength="100"
                            placeholder="Example: Retail"
                        >
                        @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="alt_text">Alternative text</label>
                        <input
                            class="form-control @error('alt_text') is-invalid @enderror"
                            id="alt_text"
                            name="alt_text"
                            type="text"
                            value="{{ old('alt_text', $gallery->alt_text) }}"
                            maxlength="255"
                            placeholder="Describe the image"
                        >
                        <div class="form-text">Used by screen readers when the image cannot be seen.</div>
                        @error('alt_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Image and display</h2>
                <p class="text-secondary small mb-0">Preview the image and control its visibility.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <label class="form-label" for="image">Image @if (! $gallery->exists)<span class="text-danger">*</span>@endif</label>
                    <img
                        id="gallery-image-preview"
                        class="img-fluid rounded border mb-3 {{ $gallery->image ? '' : 'd-none' }}"
                        @if ($gallery->image) src="{{ asset($gallery->image) }}" @endif
                        alt="Image preview"
                    >
                    <input
                        class="form-control @error('image') is-invalid @enderror"
                        id="image"
                        name="image"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp"
                    >
                    <div class="form-text">JPG, PNG, or WebP. Maximum 4 MB.</div>
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @include('admin.components.media-picker', [
                        'inputName' => 'image',
                        'previewUrl' => $gallery->image ? asset($gallery->image) : null,
                        'label' => 'Image',
                        'acceptType' => 'image',
                        'allowClear' => false,
                    ])
                </div>

                <div class="mb-4">
                    <label class="form-label" for="sort_order">Sort order <span class="text-danger">*</span></label>
                    <input
                        class="form-control @error('sort_order') is-invalid @enderror"
                        id="sort_order"
                        name="sort_order"
                        type="number"
                        value="{{ old('sort_order', $gallery->sort_order ?? 0) }}"
                        min="0"
                        required
                    >
                    <div class="form-text">Lower numbers appear first.</div>
                    @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <input name="status" type="hidden" value="0">
                <div class="form-check form-switch">
                    <input
                        class="form-check-input"
                        id="status"
                        name="status"
                        type="checkbox"
                        value="1"
                        @checked(old('status', $gallery->status ?? true))
                    >
                    <label class="form-check-label" for="status">Active</label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">{{ $submitLabel }}</button>
            <a class="btn btn-light" href="{{ route('admin.gallery.index') }}">Cancel</a>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.getElementById('image')?.addEventListener('change', (event) => {
            const [file] = event.target.files;
            const preview = document.getElementById('gallery-image-preview');

            if (! file || ! preview) {
                return;
            }

            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        });
    </script>
@endpush
