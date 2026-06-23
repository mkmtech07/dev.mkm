<div class="row g-4">
    <div class="col-xl-8">
        <div class="card content-card">
            <div class="card-header"><h2 class="h5 mb-1">Media details</h2><p class="small text-secondary mb-0">Upload a safe image or document and add reusable metadata.</p></div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="file">File @if (! $mediaFile->exists)<span class="text-danger">*</span>@endif</label>
                        <input class="form-control @error('file') is-invalid @enderror" id="file" name="file" type="file" accept=".jpg,.jpeg,.png,.webp,.svg,.gif,.pdf,.doc,.docx,.xls,.xlsx" @required(! $mediaFile->exists)>
                        <div class="form-text">JPG, PNG, WebP, SVG, GIF, PDF, Word, or Excel. Maximum 5 MB.@if ($mediaFile->exists) Leave empty to keep the current file.@endif</div>
                        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="title">Title</label>
                        <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" type="text" value="{{ old('title', $mediaFile->title) }}" maxlength="255">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="alt_text">Alternative text</label>
                        <input class="form-control @error('alt_text') is-invalid @enderror" id="alt_text" name="alt_text" type="text" value="{{ old('alt_text', $mediaFile->alt_text) }}" maxlength="255">
                        <div class="form-text">Describe images for accessibility. Leave empty for decorative images.</div>
                        @error('alt_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="caption">Caption</label>
                        <textarea class="form-control @error('caption') is-invalid @enderror" id="caption" name="caption" rows="4" maxlength="500">{{ old('caption', $mediaFile->caption) }}</textarea>
                        @error('caption') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card content-card">
            <div class="card-header"><h2 class="h5 mb-1">Preview and publishing</h2><p class="small text-secondary mb-0">Confirm the file and visibility.</p></div>
            <div class="card-body p-4">
                <div id="media-preview" class="media-form-preview mb-4 {{ $mediaFile->exists ? '' : 'd-none' }}">
                    @if ($mediaFile->exists && $mediaFile->isImage())
                        <img id="media-preview-image" src="{{ $mediaFile->publicUrl() }}" alt="Current media preview">
                    @else
                        <img id="media-preview-image" class="d-none" src="" alt="Selected media preview">
                        <div id="media-preview-document" class="media-document-preview">
                            <span class="media-extension">{{ $mediaFile->exists ? strtoupper(pathinfo($mediaFile->file_name, PATHINFO_EXTENSION)) : 'FILE' }}</span>
                            <span class="small text-secondary">Document</span>
                        </div>
                    @endif
                </div>

                @if ($mediaFile->exists)
                    <dl class="row small mb-4">
                        <dt class="col-5 text-secondary fw-normal">Filename</dt><dd class="col-7 text-break">{{ $mediaFile->original_name }}</dd>
                        <dt class="col-5 text-secondary fw-normal">Type</dt><dd class="col-7">{{ $mediaFile->mime_type }}</dd>
                        <dt class="col-5 text-secondary fw-normal">Size</dt><dd class="col-7">{{ $mediaFile->formattedFileSize() }}</dd>
                    </dl>
                @endif

                <input name="status" type="hidden" value="0">
                <div class="form-check form-switch">
                    <input class="form-check-input" id="status" name="status" type="checkbox" value="1" @checked(old('status', $mediaFile->status ?? true))>
                    <label class="form-check-label" for="status">Active media</label>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2 py-3">
                <a class="btn btn-light" href="{{ $mediaFile->exists ? route('admin.website.media-library.show', $mediaFile) : route('admin.website.media-library.index') }}">Cancel</a>
                <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        const fileInput = document.getElementById('file');
        const preview = document.getElementById('media-preview');
        const previewImage = document.getElementById('media-preview-image');
        const previewDocument = document.getElementById('media-preview-document');

        fileInput?.addEventListener('change', () => {
            const file = fileInput.files?.[0];
            if (!file) return;

            preview.classList.remove('d-none');
            if (file.type.startsWith('image/')) {
                previewImage.src = URL.createObjectURL(file);
                previewImage.classList.remove('d-none');
                previewDocument?.classList.add('d-none');
            } else {
                previewImage.classList.add('d-none');
                if (previewDocument) {
                    previewDocument.classList.remove('d-none');
                    previewDocument.querySelector('.media-extension').textContent = file.name.split('.').pop()?.toUpperCase() || 'FILE';
                }
            }
        });
    </script>
@endpush
