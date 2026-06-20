<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Post content</h2>
                <p class="text-secondary small mb-0">Write the title, summary, and full blog article.</p>
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
                            value="{{ old('title', $blog->title) }}"
                            maxlength="255"
                            required
                        >
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-5">
                        <label class="form-label" for="slug">Slug</label>
                        <div class="input-group">
                            <span class="input-group-text">/blog/</span>
                            <input
                                class="form-control @error('slug') is-invalid @enderror"
                                id="slug"
                                name="slug"
                                type="text"
                                value="{{ old('slug', $blog->slug) }}"
                                maxlength="255"
                                placeholder="generated-from-title"
                            >
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-text">Leave blank to generate it from the title.</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="excerpt">Excerpt</label>
                    <textarea
                        class="form-control @error('excerpt') is-invalid @enderror"
                        id="excerpt"
                        name="excerpt"
                        rows="4"
                        maxlength="1000"
                    >{{ old('excerpt', $blog->excerpt) }}</textarea>
                    @error('excerpt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label" for="content">Content <span class="text-danger">*</span></label>
                    <textarea
                        class="form-control @error('content') is-invalid @enderror"
                        id="content"
                        name="content"
                        rows="20"
                        required
                    >{{ old('content', $blog->content) }}</textarea>
                    @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card content-card">
            <div class="card-header">
                <h2 class="h5 mb-1">Search and social metadata</h2>
                <p class="text-secondary small mb-0">Optional SEO, canonical, and social sharing details.</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="meta_title">Meta title</label>
                        <input
                            class="form-control @error('meta_title') is-invalid @enderror"
                            id="meta_title"
                            name="meta_title"
                            type="text"
                            value="{{ old('meta_title', $blog->meta_title) }}"
                            maxlength="255"
                        >
                        @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="canonical_url">Canonical URL</label>
                        <input
                            class="form-control @error('canonical_url') is-invalid @enderror"
                            id="canonical_url"
                            name="canonical_url"
                            type="url"
                            value="{{ old('canonical_url', $blog->canonical_url) }}"
                            maxlength="2048"
                            placeholder="https://example.com/blog/article"
                        >
                        @error('canonical_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div>
                    <label class="form-label" for="meta_description">Meta description</label>
                    <textarea
                        class="form-control @error('meta_description') is-invalid @enderror"
                        id="meta_description"
                        name="meta_description"
                        rows="4"
                        maxlength="1000"
                    >{{ old('meta_description', $blog->meta_description) }}</textarea>
                    @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Publishing</h2>
                <p class="text-secondary small mb-0">Set the category, author, schedule, and visibility.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label" for="blog_category_id">Category</label>
                    <select class="form-select @error('blog_category_id') is-invalid @enderror" id="blog_category_id" name="blog_category_id">
                        <option value="">Uncategorized</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('blog_category_id', $blog->blog_category_id) === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('blog_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="author">Author</label>
                    <input
                        class="form-control @error('author') is-invalid @enderror"
                        id="author"
                        name="author"
                        type="text"
                        value="{{ old('author', $blog->author) }}"
                        maxlength="255"
                    >
                    @error('author') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="publish_at">Publish at</label>
                    <input
                        class="form-control @error('publish_at') is-invalid @enderror"
                        id="publish_at"
                        name="publish_at"
                        type="datetime-local"
                        value="{{ old('publish_at', $blog->publish_at?->format('Y-m-d\TH:i')) }}"
                    >
                    <div class="form-text">Leave blank to publish immediately when active.</div>
                    @error('publish_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <input name="status" type="hidden" value="0">
                <div class="form-check form-switch mb-3">
                    <input
                        class="form-check-input"
                        id="status"
                        name="status"
                        type="checkbox"
                        value="1"
                        @checked(old('status', $blog->status ?? true))
                    >
                    <label class="form-check-label" for="status">Active</label>
                </div>

                <input name="is_featured" type="hidden" value="0">
                <div class="form-check form-switch">
                    <input
                        class="form-check-input"
                        id="is_featured"
                        name="is_featured"
                        type="checkbox"
                        value="1"
                        @checked(old('is_featured', $blog->is_featured ?? false))
                    >
                    <label class="form-check-label" for="is_featured">Featured post</label>
                </div>
            </div>
        </div>

        @foreach ([
            'featured_image' => ['Featured image', $blog->featured_image, 'blog-featured-image-preview'],
            'og_image' => ['Open Graph image', $blog->og_image, 'blog-og-image-preview'],
        ] as $field => [$label, $currentImage, $previewId])
            <div class="card content-card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-1">{{ $label }}</h2>
                </div>
                <div class="card-body p-4">
                    <img
                        id="{{ $previewId }}"
                        class="img-fluid rounded border mb-3 {{ $currentImage ? '' : 'd-none' }}"
                        @if ($currentImage) src="{{ asset($currentImage) }}" @endif
                        alt="{{ $label }} preview"
                    >
                    <input
                        class="form-control @error($field) is-invalid @enderror"
                        id="{{ $field }}"
                        name="{{ $field }}"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp"
                        data-preview="{{ $previewId }}"
                    >
                    <div class="form-text">JPG, PNG, or WebP. Maximum 4 MB.</div>
                    @error($field) <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        @endforeach

        <div class="d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">{{ $submitLabel }}</button>
            <a class="btn btn-light" href="{{ route('admin.blogs.index') }}">Cancel</a>
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

        const titleField = document.getElementById('title');
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

        titleField?.addEventListener('input', () => {
            if (slugField && ! slugWasEdited) {
                slugField.value = slugify(titleField.value);
            }
        });

        document.querySelectorAll('input[data-preview]').forEach((input) => {
            input.addEventListener('change', (event) => {
                const [file] = event.target.files;
                const preview = document.getElementById(event.target.dataset.preview);

                if (! file || ! preview) {
                    return;
                }

                preview.src = URL.createObjectURL(file);
                preview.classList.remove('d-none');
            });
        });
    </script>
@endpush
