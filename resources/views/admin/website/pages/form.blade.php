<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Page content</h2>
                <p class="text-secondary small mb-0">Write the page title, URL slug, and main content.</p>
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
                            value="{{ old('title', $page->title) }}"
                            maxlength="255"
                            required
                        >
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-5">
                        <label class="form-label" for="slug">Slug</label>
                        <div class="input-group">
                            <span class="input-group-text">/</span>
                            <input
                                class="form-control @error('slug') is-invalid @enderror"
                                id="slug"
                                name="slug"
                                type="text"
                                value="{{ old('slug', $page->slug) }}"
                                maxlength="255"
                                placeholder="generated-from-title"
                            >
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-text">Leave blank to generate it from the title.</div>
                    </div>
                </div>

                <div>
                    <label class="form-label" for="content">Content <span class="text-danger">*</span></label>
                    <textarea
                        class="form-control @error('content') is-invalid @enderror"
                        id="content"
                        name="content"
                        rows="18"
                        required
                    >{{ old('content', $page->content) }}</textarea>
                    @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card content-card">
            <div class="card-header">
                <h2 class="h5 mb-1">Search engine details</h2>
                <p class="text-secondary small mb-0">Optional title and summary used by the browser and search engines.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label" for="meta_title">Meta title</label>
                    <input
                        class="form-control @error('meta_title') is-invalid @enderror"
                        id="meta_title"
                        name="meta_title"
                        type="text"
                        value="{{ old('meta_title', $page->meta_title) }}"
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
                    >{{ old('meta_description', $page->meta_description) }}</textarea>
                    @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Featured image</h2>
                <p class="text-secondary small mb-0">Optional header image for this page.</p>
            </div>
            <div class="card-body p-4">
                <img
                    id="page-image-preview"
                    class="img-fluid rounded border mb-3 {{ $page->featured_image ? '' : 'd-none' }}"
                    @if ($page->featured_image) src="{{ asset($page->featured_image) }}" @endif
                    alt="Featured image preview"
                >
                <input
                    class="form-control @error('featured_image') is-invalid @enderror"
                    id="featured_image"
                    name="featured_image"
                    type="file"
                    accept=".jpg,.jpeg,.png,.webp"
                >
                <div class="form-text">JPG, PNG, or WebP. Maximum 4 MB.</div>
                @error('featured_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Display settings</h2>
                <p class="text-secondary small mb-0">Control the page style, visibility, and ordering.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label" for="page_type">Page type <span class="text-danger">*</span></label>
                    <select class="form-select @error('page_type') is-invalid @enderror" id="page_type" name="page_type" required>
                        @foreach (['default' => 'Default', 'page' => 'Page', 'landing' => 'Landing'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('page_type', $page->page_type ?? 'default') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('page_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="template">Template <span class="text-danger">*</span></label>
                    <input
                        class="form-control @error('template') is-invalid @enderror"
                        id="template"
                        name="template"
                        type="text"
                        value="{{ old('template', $page->template ?? 'default') }}"
                        maxlength="100"
                        required
                    >
                    @error('template') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="sort_order">Sort order <span class="text-danger">*</span></label>
                    <input
                        class="form-control @error('sort_order') is-invalid @enderror"
                        id="sort_order"
                        name="sort_order"
                        type="number"
                        value="{{ old('sort_order', $page->sort_order ?? 0) }}"
                        min="0"
                        required
                    >
                    <div class="form-text">Lower numbers appear first in menu listings.</div>
                    @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <input name="status" type="hidden" value="0">
                <div class="form-check form-switch mb-3">
                    <input
                        class="form-check-input"
                        id="status"
                        name="status"
                        type="checkbox"
                        value="1"
                        @checked(old('status', $page->status ?? true))
                    >
                    <label class="form-check-label" for="status">Active</label>
                </div>

                <input name="show_in_menu" type="hidden" value="0">
                <div class="form-check form-switch">
                    <input
                        class="form-check-input"
                        id="show_in_menu"
                        name="show_in_menu"
                        type="checkbox"
                        value="1"
                        @checked(old('show_in_menu', $page->show_in_menu ?? false))
                    >
                    <label class="form-check-label" for="show_in_menu">Show in menu</label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">{{ $submitLabel }}</button>
            <a class="btn btn-light" href="{{ route('admin.pages.index') }}">Cancel</a>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script>
        const contentField = document.getElementById('content');

        if (contentField && window.ClassicEditor) {
            ClassicEditor.create(contentField).catch((error) => console.error(error));
        }

        document.getElementById('featured_image')?.addEventListener('change', (event) => {
            const [file] = event.target.files;
            const preview = document.getElementById('page-image-preview');

            if (! file || ! preview) {
                return;
            }

            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        });
    </script>
@endpush
