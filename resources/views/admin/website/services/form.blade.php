<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card">
            <div class="card-header">
                <h2 class="h5 mb-1">Service content</h2>
                <p class="text-secondary small mb-0">Describe the service shown to website visitors.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                        <input
                            class="form-control @error('title') is-invalid @enderror"
                            id="title"
                            name="title"
                            type="text"
                            value="{{ old('title', $service->title) }}"
                            maxlength="255"
                            required
                        >
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-5">
                        <label class="form-label" for="slug">Slug</label>
                        <input
                            class="form-control @error('slug') is-invalid @enderror"
                            id="slug"
                            name="slug"
                            type="text"
                            value="{{ old('slug', $service->slug) }}"
                            maxlength="255"
                            placeholder="generated-from-title"
                        >
                        <div class="form-text">Leave blank to generate it from the title.</div>
                        @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="short_description">Short description</label>
                    <textarea
                        class="form-control @error('short_description') is-invalid @enderror"
                        id="short_description"
                        name="short_description"
                        rows="3"
                        maxlength="500"
                    >{{ old('short_description', $service->short_description) }}</textarea>
                    <div class="form-text">Used on homepage service cards.</div>
                    @error('short_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="description">Full description</label>
                    <textarea
                        class="form-control @error('description') is-invalid @enderror"
                        id="description"
                        name="description"
                        rows="8"
                        maxlength="10000"
                    >{{ old('description', $service->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label" for="icon">Icon</label>
                    <input
                        class="form-control @error('icon') is-invalid @enderror"
                        id="icon"
                        name="icon"
                        type="text"
                        value="{{ old('icon', $service->icon) }}"
                        maxlength="255"
                        placeholder="Example: 01 or ✓"
                    >
                    <div class="form-text">Use a short label, number, or emoji.</div>
                    @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    <label class="form-label" for="image">Service image</label>
                    @if ($service->image)
                        <div class="mb-3">
                            <img
                                class="img-fluid rounded border"
                                src="{{ asset($service->image) }}"
                                alt="Current service image"
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
                    @include('admin.components.media-picker', [
                        'inputName' => 'image',
                        'previewUrl' => $service->image ? asset($service->image) : null,
                        'label' => 'Service image',
                        'acceptType' => 'image',
                    ])
                </div>

                <div class="mb-4">
                    <label class="form-label" for="sort_order">Sort order <span class="text-danger">*</span></label>
                    <input
                        class="form-control @error('sort_order') is-invalid @enderror"
                        id="sort_order"
                        name="sort_order"
                        type="number"
                        value="{{ old('sort_order', $service->sort_order ?? 0) }}"
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
                        @checked(old('status', $service->status ?? true))
                    >
                    <label class="form-check-label" for="status">Active</label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">{{ $submitLabel }}</button>
            <a class="btn btn-light" href="{{ route('admin.services.index') }}">Cancel</a>
        </div>
    </div>
</div>
