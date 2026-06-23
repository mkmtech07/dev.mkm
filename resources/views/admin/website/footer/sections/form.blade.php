<div class="card content-card">
    <div class="card-header"><h2 class="h5 mb-1">Section details</h2><p class="small text-secondary mb-0">Choose how this footer column should render.</p></div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-7">
                <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" type="text" value="{{ old('title', $footerSection->title) }}" maxlength="255" required>
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-5">
                <label class="form-label" for="type">Type <span class="text-danger">*</span></label>
                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                    @foreach (['about' => 'About', 'links' => 'Links', 'contact' => 'Contact', 'social' => 'Social', 'newsletter' => 'Newsletter', 'custom' => 'Custom'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $footerSection->type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="content">Content</label>
                <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="8" maxlength="50000">{{ old('content', $footerSection->content) }}</textarea>
                <div class="form-text">Content is rendered as safe plain text. Line breaks are preserved.</div>
                @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="sort_order">Sort order</label>
                <input class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $footerSection->sort_order ?? 0) }}" min="0" required>
                @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-8 d-flex align-items-end pb-2">
                <input name="status" type="hidden" value="0">
                <div class="form-check form-switch">
                    <input class="form-check-input" id="status" name="status" type="checkbox" value="1" @checked(old('status', $footerSection->status ?? true))>
                    <label class="form-check-label" for="status">Active</label>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-end gap-2 py-3">
        <a class="btn btn-light" href="{{ route('admin.website.footer.sections.index') }}">Cancel</a>
        <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
    </div>
</div>
