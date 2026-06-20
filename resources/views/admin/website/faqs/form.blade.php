<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card">
            <div class="card-header">
                <h2 class="h5 mb-1">FAQ content</h2>
                <p class="text-secondary small mb-0">Add a clear question and a helpful answer.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label" for="question">Question <span class="text-danger">*</span></label>
                    <textarea
                        class="form-control @error('question') is-invalid @enderror"
                        id="question"
                        name="question"
                        rows="3"
                        maxlength="1000"
                        required
                    >{{ old('question', $faq->question) }}</textarea>
                    @error('question') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="answer">Answer <span class="text-danger">*</span></label>
                    <textarea
                        class="form-control @error('answer') is-invalid @enderror"
                        id="answer"
                        name="answer"
                        rows="10"
                        maxlength="10000"
                        required
                    >{{ old('answer', $faq->answer) }}</textarea>
                    @error('answer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label" for="category">Category</label>
                    <input
                        class="form-control @error('category') is-invalid @enderror"
                        id="category"
                        name="category"
                        type="text"
                        value="{{ old('category', $faq->category) }}"
                        maxlength="255"
                        placeholder="For example: Billing"
                    >
                    @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">Display settings</h2>
                <p class="text-secondary small mb-0">Control visibility and accordion order.</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <label class="form-label" for="sort_order">Sort order <span class="text-danger">*</span></label>
                    <input
                        class="form-control @error('sort_order') is-invalid @enderror"
                        id="sort_order"
                        name="sort_order"
                        type="number"
                        value="{{ old('sort_order', $faq->sort_order ?? 0) }}"
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
                        @checked(old('status', $faq->status ?? true))
                    >
                    <label class="form-check-label" for="status">Active</label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary flex-grow-1" type="submit">{{ $submitLabel }}</button>
            <a class="btn btn-light" href="{{ route('admin.faqs.index') }}">Cancel</a>
        </div>
    </div>
</div>
