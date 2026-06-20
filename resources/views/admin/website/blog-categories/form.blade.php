<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Category details</h2>
                <p class="text-secondary small mb-0">Add the category name, URL slug, and description.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                        <input
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $blogCategory->name) }}"
                            maxlength="255"
                            required
                        >
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="slug">Slug</label>
                        <div class="input-group">
                            <span class="input-group-text">/blog/</span>
                            <input
                                class="form-control @error('slug') is-invalid @enderror"
                                id="slug"
                                name="slug"
                                type="text"
                                value="{{ old('slug', $blogCategory->slug) }}"
                                maxlength="255"
                                placeholder="generated-from-name"
                            >
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-text">Leave blank to generate it automatically.</div>
                    </div>
                </div>

                <div>
                    <label class="form-label" for="description">Description</label>
                    <textarea
                        class="form-control @error('description') is-invalid @enderror"
                        id="description"
                        name="description"
                        rows="7"
                        maxlength="5000"
                    >{{ old('description', $blogCategory->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card content-card">
            <div class="card-header">
                <h2 class="h5 mb-1">Search engine details</h2>
                <p class="text-secondary small mb-0">Optional category metadata for future public blog pages.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label" for="meta_title">Meta title</label>
                    <input
                        class="form-control @error('meta_title') is-invalid @enderror"
                        id="meta_title"
                        name="meta_title"
                        type="text"
                        value="{{ old('meta_title', $blogCategory->meta_title) }}"
                        maxlength="255"
                    >
                    @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label" for="meta_description">Meta description</label>
                    <textarea
                        class="form-control @error('meta_description') is-invalid @enderror"
                        id="meta_description"
                        name="meta_description"
                        rows="4"
                        maxlength="1000"
                    >{{ old('meta_description', $blogCategory->meta_description) }}</textarea>
                    @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Display settings</h2>
                <p class="text-secondary small mb-0">Control category status and ordering.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <label class="form-label" for="sort_order">Sort order <span class="text-danger">*</span></label>
                    <input
                        class="form-control @error('sort_order') is-invalid @enderror"
                        id="sort_order"
                        name="sort_order"
                        type="number"
                        value="{{ old('sort_order', $blogCategory->sort_order ?? 0) }}"
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
                        @checked(old('status', $blogCategory->status ?? true))
                    >
                    <label class="form-check-label" for="status">Active</label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">{{ $submitLabel }}</button>
            <a class="btn btn-light" href="{{ route('admin.blog-categories.index') }}">Cancel</a>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        const nameField = document.getElementById('name');
        const slugField = document.getElementById('slug');
        let slugWasEdited = Boolean(slugField?.value.trim());

        const slugify = (value) => value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');

        slugField?.addEventListener('input', () => {
            slugWasEdited = slugField.value.trim() !== '';
        });

        nameField?.addEventListener('input', () => {
            if (slugField && ! slugWasEdited) {
                slugField.value = slugify(nameField.value);
            }
        });
    </script>
@endpush
