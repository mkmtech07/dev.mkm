@extends('layouts.admin')

@section('title', 'Media Library')
@section('page-title', 'Media Library')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Media library</h2>
            <p class="text-secondary mb-0">Upload, find, and manage reusable website files.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.website.media-library.create') }}">Upload media</a>
    </div>

    <div class="card content-card mb-4">
        <div class="card-body p-3">
            <form class="row g-2 align-items-center" method="GET" action="{{ route('admin.website.media-library.index') }}">
                <input name="view" type="hidden" value="{{ $viewMode }}">
                <div class="col-sm-6 col-xl-5">
                    <label class="visually-hidden" for="search">Search media</label>
                    <input class="form-control" id="search" name="search" type="search" value="{{ $search }}" placeholder="Search title, filename, or MIME type">
                </div>
                <div class="col-sm-3 col-xl-2">
                    <label class="visually-hidden" for="file_type">File type</label>
                    <select class="form-select" id="file_type" name="file_type">
                        <option value="">All file types</option>
                        @foreach (\App\Models\MediaFile::FILE_TYPES as $type)
                            <option value="{{ $type }}" @selected($fileType === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3 col-xl-2">
                    <label class="visually-hidden" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All statuses</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-auto"><button class="btn btn-outline-primary" type="submit">Filter</button></div>
                @if ($search !== '' || $fileType !== '' || $status !== '')
                    <div class="col-auto"><a class="btn btn-light" href="{{ route('admin.website.media-library.index', ['view' => $viewMode]) }}">Clear</a></div>
                @endif
                <div class="col-auto ms-xl-auto">
                    <div class="btn-group" role="group" aria-label="Media view">
                        <a class="btn {{ $viewMode === 'grid' ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('admin.website.media-library.index', [...request()->except('page', 'view'), 'view' => 'grid']) }}" aria-label="Grid view">Grid</a>
                        <a class="btn {{ $viewMode === 'table' ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('admin.website.media-library.index', [...request()->except('page', 'view'), 'view' => 'table']) }}" aria-label="Table view">Table</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($mediaFiles->isEmpty())
        <div class="card content-card">
            <div class="card-body py-5 text-center">
                <div class="media-empty-icon mx-auto mb-3" aria-hidden="true">+</div>
                <h3 class="h5">{{ $search !== '' || $fileType !== '' || $status !== '' ? 'No media matches your filters' : 'Your media library is empty' }}</h3>
                <p class="text-secondary mb-3">{{ $search !== '' || $fileType !== '' || $status !== '' ? 'Try changing or clearing the current filters.' : 'Upload an image or document to get started.' }}</p>
                @if ($search === '' && $fileType === '' && $status === '')
                    <a class="btn btn-primary" href="{{ route('admin.website.media-library.create') }}">Upload first file</a>
                @endif
            </div>
        </div>
    @elseif ($viewMode === 'grid')
        <div class="row g-4">
            @foreach ($mediaFiles as $mediaFile)
                <div class="col-sm-6 col-xl-4 col-xxl-3">
                    <article class="card media-card h-100 border-0 shadow-sm overflow-hidden">
                        <a class="media-card-preview" href="{{ route('admin.website.media-library.show', $mediaFile) }}">
                            @if ($mediaFile->isImage())
                                <img src="{{ $mediaFile->publicUrl() }}" alt="{{ $mediaFile->alt_text ?: ($mediaFile->title ?: $mediaFile->original_name) }}" loading="lazy">
                            @else
                                <div class="media-document-preview">
                                    <span class="media-extension">{{ strtoupper(pathinfo($mediaFile->file_name, PATHINFO_EXTENSION)) }}</span>
                                    <span class="small text-secondary">Document</span>
                                </div>
                            @endif
                        </a>
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div class="min-w-0">
                                    <h3 class="h6 text-truncate mb-1" title="{{ $mediaFile->title ?: $mediaFile->original_name }}">{{ $mediaFile->title ?: $mediaFile->original_name }}</h3>
                                    <div class="small text-secondary text-truncate" title="{{ $mediaFile->original_name }}">{{ $mediaFile->original_name }}</div>
                                </div>
                                <span class="badge {{ $mediaFile->status ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $mediaFile->status ? 'Active' : 'Inactive' }}</span>
                            </div>
                            <div class="d-flex justify-content-between small text-secondary mb-3">
                                <span class="text-capitalize">{{ $mediaFile->file_type }}</span>
                                <span>{{ $mediaFile->formattedFileSize() }}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-copy-url="{{ $mediaFile->publicUrl() }}">Copy URL</button>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.website.media-library.show', $mediaFile) }}">View</a>
                                <a class="btn btn-sm btn-light" href="{{ route('admin.website.media-library.edit', $mediaFile) }}">Edit</a>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    @else
        <div class="card content-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Media</th><th>Type</th><th>Size</th><th>Uploaded</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        @foreach ($mediaFiles as $mediaFile)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($mediaFile->isImage())
                                            <img class="media-table-thumb" src="{{ $mediaFile->publicUrl() }}" alt="">
                                        @else
                                            <div class="media-table-thumb media-table-document">{{ strtoupper(pathinfo($mediaFile->file_name, PATHINFO_EXTENSION)) }}</div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="fw-semibold text-truncate" style="max-width: 24rem;">{{ $mediaFile->title ?: $mediaFile->original_name }}</div>
                                            <div class="small text-secondary text-truncate" style="max-width: 24rem;">{{ $mediaFile->original_name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge text-bg-light text-capitalize">{{ $mediaFile->file_type }}</span><div class="small text-secondary mt-1">{{ $mediaFile->mime_type }}</div></td>
                                <td>{{ $mediaFile->formattedFileSize() }}</td>
                                <td>{{ $mediaFile->created_at->format('d M Y') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.website.media-library.toggle-status', $mediaFile) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm {{ $mediaFile->status ? 'btn-success' : 'btn-outline-secondary' }}" type="submit">{{ $mediaFile->status ? 'Active' : 'Inactive' }}</button>
                                    </form>
                                </td>
                                <td class="text-end text-nowrap">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-copy-url="{{ $mediaFile->publicUrl() }}">Copy URL</button>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.website.media-library.show', $mediaFile) }}">View</a>
                                    <a class="btn btn-sm btn-light" href="{{ route('admin.website.media-library.edit', $mediaFile) }}">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($mediaFiles->hasPages())
        <div class="mt-4">{{ $mediaFiles->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
    @endif
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-copy-url]').forEach((button) => {
            button.addEventListener('click', async () => {
                const original = button.textContent;
                try {
                    await navigator.clipboard.writeText(button.dataset.copyUrl);
                    button.textContent = 'Copied';
                } catch (error) {
                    window.prompt('Copy this media URL:', button.dataset.copyUrl);
                }
                window.setTimeout(() => { button.textContent = original; }, 1500);
            });
        });
    </script>
@endpush
