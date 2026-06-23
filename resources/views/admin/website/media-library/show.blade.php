@extends('layouts.admin')

@section('title', $mediaFile->title ?: $mediaFile->original_name)
@section('page-title', 'Media Details')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <a class="small text-decoration-none" href="{{ route('admin.website.media-library.index') }}">&larr; Back to media library</a>
            <h2 class="h4 mt-2 mb-1">{{ $mediaFile->title ?: $mediaFile->original_name }}</h2>
            <p class="text-secondary mb-0">Uploaded {{ $mediaFile->created_at->format('d M Y, g:i A') }}</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" id="copy-media-url" type="button" data-url="{{ $mediaFile->publicUrl() }}">Copy URL</button>
            <a class="btn btn-primary" href="{{ route('admin.website.media-library.edit', $mediaFile) }}">Edit media</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card content-card overflow-hidden">
                <div class="media-detail-preview">
                    @if ($mediaFile->isImage())
                        <img src="{{ $mediaFile->publicUrl() }}" alt="{{ $mediaFile->alt_text ?: ($mediaFile->title ?: $mediaFile->original_name) }}">
                    @else
                        <div class="media-document-preview py-5">
                            <span class="media-extension">{{ strtoupper(pathinfo($mediaFile->file_name, PATHINFO_EXTENSION)) }}</span>
                            <span class="text-secondary">{{ $mediaFile->original_name }}</span>
                            <a class="btn btn-outline-primary mt-3" href="{{ $mediaFile->publicUrl() }}" target="_blank" rel="noopener">Open document</a>
                        </div>
                    @endif
                </div>
                @if ($mediaFile->caption)
                    <div class="card-footer bg-white text-secondary">{{ $mediaFile->caption }}</div>
                @endif
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card content-card mb-4">
                <div class="card-header"><h3 class="h5 mb-0">File information</h3></div>
                <div class="card-body p-4">
                    <dl class="row mb-0">
                        <dt class="col-5 text-secondary fw-normal">Status</dt><dd class="col-7"><span class="badge {{ $mediaFile->status ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $mediaFile->status ? 'Active' : 'Inactive' }}</span></dd>
                        <dt class="col-5 text-secondary fw-normal">Original name</dt><dd class="col-7 text-break">{{ $mediaFile->original_name }}</dd>
                        <dt class="col-5 text-secondary fw-normal">Stored name</dt><dd class="col-7 text-break">{{ $mediaFile->file_name }}</dd>
                        <dt class="col-5 text-secondary fw-normal">File type</dt><dd class="col-7 text-capitalize">{{ $mediaFile->file_type }}</dd>
                        <dt class="col-5 text-secondary fw-normal">MIME type</dt><dd class="col-7 text-break">{{ $mediaFile->mime_type }}</dd>
                        <dt class="col-5 text-secondary fw-normal">Size</dt><dd class="col-7">{{ $mediaFile->formattedFileSize() }}</dd>
                        <dt class="col-5 text-secondary fw-normal">Uploaded by</dt><dd class="col-7">{{ $mediaFile->uploader?->name ?: 'Unknown user' }}</dd>
                        <dt class="col-5 text-secondary fw-normal">Alt text</dt><dd class="col-7">{{ $mediaFile->alt_text ?: '—' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card content-card">
                <div class="card-body p-4">
                    <form class="mb-3" method="POST" action="{{ route('admin.website.media-library.toggle-status', $mediaFile) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-outline-secondary w-100" type="submit">Mark as {{ $mediaFile->status ? 'inactive' : 'active' }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.website.media-library.destroy', $mediaFile) }}" onsubmit="return confirm('Delete this media record? The physical file will remain recoverable until permanent deletion.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger w-100" type="submit">Delete media</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('copy-media-url')?.addEventListener('click', async (event) => {
            const button = event.currentTarget;
            try {
                await navigator.clipboard.writeText(button.dataset.url);
                button.textContent = 'Copied';
                window.setTimeout(() => { button.textContent = 'Copy URL'; }, 1500);
            } catch (error) {
                window.prompt('Copy this media URL:', button.dataset.url);
            }
        });
    </script>
@endpush
