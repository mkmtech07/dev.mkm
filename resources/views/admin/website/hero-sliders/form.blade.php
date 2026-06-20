<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card">
            <div class="card-header">
                <h2 class="h5 mb-1">Slide content</h2>
                <p class="text-secondary small mb-0">Add the headline, supporting text, and optional action.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                    <input
                        class="form-control @error('title') is-invalid @enderror"
                        id="title"
                        name="title"
                        type="text"
                        value="{{ old('title', $heroSlider->title) }}"
                        maxlength="255"
                        required
                    >
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="subtitle">Subtitle <span class="text-danger">*</span></label>
                    <textarea
                        class="form-control @error('subtitle') is-invalid @enderror"
                        id="subtitle"
                        name="subtitle"
                        rows="4"
                        maxlength="1000"
                        required
                    >{{ old('subtitle', $heroSlider->subtitle) }}</textarea>
                    @error('subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label" for="button_text">Button text</label>
                        <input
                            class="form-control @error('button_text') is-invalid @enderror"
                            id="button_text"
                            name="button_text"
                            type="text"
                            value="{{ old('button_text', $heroSlider->button_text) }}"
                            maxlength="100"
                            placeholder="Explore our services"
                        >
                        @error('button_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-7">
                        <label class="form-label" for="button_url">Button URL</label>
                        <input
                            class="form-control @error('button_url') is-invalid @enderror"
                            id="button_url"
                            name="button_url"
                            type="text"
                            value="{{ old('button_url', $heroSlider->button_url) }}"
                            maxlength="2048"
                            placeholder="/services or https://example.com"
                        >
                        @error('button_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Display settings</h2>
                <p class="text-secondary small mb-0">Choose the image, visibility, and order.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <label class="form-label" for="image">Background image</label>
                    @if ($heroSlider->image)
                        <div class="mb-3">
                            <img
                                class="img-fluid rounded border"
                                src="{{ asset($heroSlider->image) }}"
                                alt="Current slider image"
                            >
                        </div>
                    @endif
                    <input
                        class="form-control @error('image') is-invalid @enderror"
                        id="image"
                        name="image"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp"
                    >
                    <div class="form-text">JPG, PNG, or WebP. Maximum 4 MB.</div>
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="sort_order">Sort order <span class="text-danger">*</span></label>
                    <input
                        class="form-control @error('sort_order') is-invalid @enderror"
                        id="sort_order"
                        name="sort_order"
                        type="number"
                        value="{{ old('sort_order', $heroSlider->sort_order ?? 0) }}"
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
                        @checked(old('status', $heroSlider->status ?? true))
                    >
                    <label class="form-check-label" for="status">Active</label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">{{ $submitLabel }}</button>
            <a class="btn btn-light" href="{{ route('admin.hero-sliders.index') }}">Cancel</a>
        </div>
    </div>
</div>
