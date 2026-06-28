@props([
    'inputName',
    'inputValue' => null,
    'previewUrl' => null,
    'label' => 'Media Library',
    'acceptType' => 'image',
    'buttonText' => 'Choose from Media Library',
    'helpText' => null,
    'allowClear' => true,
])

@php
    $mediaIdName = $inputName.'_media_id';
    $mediaActionName = $inputName.'_media_action';
    $selectedMediaId = old($mediaIdName, $inputValue);
    $selectedAction = old($mediaActionName);
    $previewUrl = old($mediaIdName) ? null : $previewUrl;
    $canUseMediaPicker = auth()->user()?->hasAnyPermission(['media_library.view', 'media_picker.use']);
@endphp

@if($canUseMediaPicker)
    <div
        class="media-picker-field mt-3"
        data-media-picker-field
        data-accept-type="{{ $acceptType }}"
        data-field-name="{{ $inputName }}"
    >
        <input type="hidden" name="{{ $mediaIdName }}" value="{{ $selectedMediaId }}" data-media-picker-id>
        <input type="hidden" name="{{ $mediaActionName }}" value="{{ $selectedAction }}" data-media-picker-action>

        <div class="d-flex flex-wrap gap-2 align-items-center">
            <button class="btn btn-outline-primary btn-sm" type="button" data-media-picker-open>
                {{ $buttonText }}
            </button>
            @if ($allowClear)
                <button class="btn btn-light btn-sm" type="button" data-media-picker-clear>
                    Clear media
                </button>
            @endif
        </div>

        @if ($helpText)
            <div class="form-text">{{ $helpText }}</div>
        @endif

        <div class="media-picker-selected border rounded bg-light p-2 mt-2 {{ $previewUrl || $selectedAction === 'clear' ? '' : 'd-none' }}" data-media-picker-selected>
            <div class="d-flex align-items-center gap-2">
                <img
                    class="rounded border {{ $previewUrl ? '' : 'd-none' }}"
                    src="{{ $previewUrl ?: '' }}"
                    alt="{{ $label }} preview"
                    width="54"
                    height="42"
                    style="object-fit: cover"
                    data-media-picker-preview-image
                >
                <div class="small">
                    <div class="fw-semibold" data-media-picker-preview-title>
                        {{ $selectedAction === 'clear' ? 'Current media will be removed.' : ($previewUrl ? $label : 'No media selected') }}
                    </div>
                    <div class="text-secondary" data-media-picker-preview-meta>
                        {{ $selectedAction === 'clear' ? 'Save the form to apply this change.' : 'Upload a new file to override this selection.' }}
                    </div>
                </div>
            </div>
        </div>

        @error($mediaIdName)
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
        @error($mediaActionName)
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    @once
        <div
            class="modal fade"
            id="admin-media-picker-modal"
            tabindex="-1"
            aria-labelledby="admin-media-picker-title"
            aria-hidden="true"
            data-media-picker-modal
            data-index-url="{{ route('admin.api.media-picker.index') }}"
            data-upload-url="{{ route('admin.website.media-library.create') }}"
        >
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title h5" id="admin-media-picker-title">Choose Media</h2>
                            <p class="small text-secondary mb-0">Select an active file from the Media Library.</p>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="col-md-7">
                                <label class="visually-hidden" for="media-picker-search">Search media</label>
                                <input class="form-control" id="media-picker-search" type="search" placeholder="Search title, filename, or MIME type" data-media-picker-search>
                            </div>
                            <div class="col-md-3">
                                <label class="visually-hidden" for="media-picker-file-type">File type</label>
                                <select class="form-select" id="media-picker-file-type" data-media-picker-type>
                                    <option value="">All file types</option>
                                    <option value="image">Images</option>
                                    <option value="document">Documents</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-grid">
                                <button class="btn btn-outline-primary" type="button" data-media-picker-refresh>Search</button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center py-5 d-none" data-media-picker-loading>
                            <div class="spinner-border text-primary" role="status" aria-label="Loading media"></div>
                        </div>

                        <div class="alert alert-danger d-none" role="alert" data-media-picker-error></div>

                        <div class="row g-3" data-media-picker-results></div>

                        <div class="text-center py-5 d-none" data-media-picker-empty>
                            <p class="text-secondary mb-3">No media files found. Upload media first.</p>
                            @if(auth()->user()->hasPermission('media_library.create'))
                                <a class="btn btn-primary" href="{{ route('admin.website.media-library.create') }}" target="_blank" rel="noopener">Upload New Media</a>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        @if(auth()->user()->hasPermission('media_library.create'))
                            <a class="btn btn-light" href="{{ route('admin.website.media-library.create') }}" target="_blank" rel="noopener">Upload New Media</a>
                        @else
                            <span></span>
                        @endif
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-secondary" type="button" data-media-picker-prev>Previous</button>
                            <button class="btn btn-outline-secondary" type="button" data-media-picker-next>Next</button>
                            <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endonce
@endif
