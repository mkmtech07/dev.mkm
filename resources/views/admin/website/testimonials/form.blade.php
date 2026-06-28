<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card">
            <div class="card-header">
                <h2 class="h5 mb-1">Testimonial details</h2>
                <p class="text-secondary small mb-0">Add the client information and their review.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label" for="client_name">Client name <span class="text-danger">*</span></label>
                    <input
                        class="form-control @error('client_name') is-invalid @enderror"
                        id="client_name"
                        name="client_name"
                        type="text"
                        value="{{ old('client_name', $testimonial->client_name) }}"
                        maxlength="255"
                        required
                    >
                    @error('client_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="company">Company</label>
                        <input
                            class="form-control @error('company') is-invalid @enderror"
                            id="company"
                            name="company"
                            type="text"
                            value="{{ old('company', $testimonial->company) }}"
                            maxlength="255"
                        >
                        @error('company') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="designation">Designation</label>
                        <input
                            class="form-control @error('designation') is-invalid @enderror"
                            id="designation"
                            name="designation"
                            type="text"
                            value="{{ old('designation', $testimonial->designation) }}"
                            maxlength="255"
                        >
                        @error('designation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="review">Review <span class="text-danger">*</span></label>
                    <textarea
                        class="form-control @error('review') is-invalid @enderror"
                        id="review"
                        name="review"
                        rows="8"
                        maxlength="5000"
                        required
                    >{{ old('review', $testimonial->review) }}</textarea>
                    @error('review') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="rating">Rating <span class="text-danger">*</span></label>
                    <select class="form-select @error('rating') is-invalid @enderror" id="rating" name="rating" required>
                        @for ($rating = 5; $rating >= 1; $rating--)
                            <option value="{{ $rating }}" @selected((int) old('rating', $testimonial->rating ?? 5) === $rating)>
                                {{ $rating }} {{ $rating === 1 ? 'star' : 'stars' }}
                            </option>
                        @endfor
                    </select>
                    @error('rating') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Client image</h2>
                <p class="text-secondary small mb-0">Upload an optional square portrait.</p>
            </div>
            <div class="card-body p-4">
                <img
                    id="testimonial-image-preview"
                    class="rounded-circle object-fit-cover border mb-3 {{ $testimonial->image ? '' : 'd-none' }}"
                    @if ($testimonial->image) src="{{ asset($testimonial->image) }}" @endif
                    alt="Client image preview"
                    width="140"
                    height="140"
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
                    'previewUrl' => $testimonial->image ? asset($testimonial->image) : null,
                    'label' => 'Client image',
                    'acceptType' => 'image',
                ])
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Display settings</h2>
                <p class="text-secondary small mb-0">Control visibility, priority, and order.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <label class="form-label" for="sort_order">Sort order <span class="text-danger">*</span></label>
                    <input
                        class="form-control @error('sort_order') is-invalid @enderror"
                        id="sort_order"
                        name="sort_order"
                        type="number"
                        value="{{ old('sort_order', $testimonial->sort_order ?? 0) }}"
                        min="0"
                        required
                    >
                    <div class="form-text">Featured testimonials appear first; lower numbers then appear earlier.</div>
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
                        @checked(old('status', $testimonial->status ?? true))
                    >
                    <label class="form-check-label" for="status">Active</label>
                </div>

                <input name="featured" type="hidden" value="0">
                <div class="form-check form-switch">
                    <input
                        class="form-check-input"
                        id="featured"
                        name="featured"
                        type="checkbox"
                        value="1"
                        @checked(old('featured', $testimonial->featured ?? false))
                    >
                    <label class="form-check-label" for="featured">Featured</label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">{{ $submitLabel }}</button>
            <a class="btn btn-light" href="{{ route('admin.testimonials.index') }}">Cancel</a>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.getElementById('image')?.addEventListener('change', (event) => {
            const [file] = event.target.files;
            const preview = document.getElementById('testimonial-image-preview');

            if (! file || ! preview) {
                return;
            }

            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        });
    </script>
@endpush
