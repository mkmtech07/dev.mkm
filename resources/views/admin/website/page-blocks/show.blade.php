@extends('layouts.admin')

@section('title', 'Page Block Details')
@section('page-title', 'Page Block Details')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">{{ $pageBlock->title ?: 'Untitled block' }}</h2>
            <p class="text-secondary mb-0">{{ $pageBlock->page ? 'Assigned to '.$pageBlock->page->title : 'No page assigned' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-light" href="{{ route('admin.website.page-blocks.index', $pageBlock->page_id ? ['page_id' => $pageBlock->page_id] : []) }}">Back</a>
            <a class="btn btn-primary" href="{{ route('admin.website.page-blocks.edit', $pageBlock) }}">Edit block</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card content-card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-1">Block content</h2>
                </div>
                <div class="card-body p-4">
                    @if ($pageBlock->subtitle)
                        <p class="text-primary text-uppercase fw-semibold mb-2">{{ $pageBlock->subtitle }}</p>
                    @endif

                    @if ($pageBlock->content)
                        @if ($pageBlock->type === 'custom_html')
                            <div class="border rounded p-3">{!! $pageBlock->publicContent() !!}</div>
                        @else
                            <div class="text-secondary" style="white-space: pre-line;">{{ $pageBlock->content }}</div>
                        @endif
                    @else
                        <p class="text-secondary mb-0">No content has been added.</p>
                    @endif
                </div>
            </div>

            <div class="card content-card">
                <div class="card-header">
                    <h2 class="h5 mb-1">Settings</h2>
                </div>
                <div class="card-body p-4">
                    @if ($pageBlock->settings)
                        <pre class="bg-light border rounded p-3 mb-0"><code>{{ json_encode($pageBlock->settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                    @else
                        <p class="text-secondary mb-0">No advanced settings have been added.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card content-card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-1">Overview</h2>
                </div>
                <div class="card-body p-4">
                    <dl class="row mb-0">
                        <dt class="col-5">Type</dt>
                        <dd class="col-7"><span class="badge text-bg-primary">{{ $pageBlock->typeLabel() }}</span></dd>
                        <dt class="col-5">Block key</dt>
                        <dd class="col-7"><code>{{ $pageBlock->block_key ?: '-' }}</code></dd>
                        <dt class="col-5">Sort order</dt>
                        <dd class="col-7">{{ $pageBlock->sort_order }}</dd>
                        <dt class="col-5">Status</dt>
                        <dd class="col-7">
                            <span class="badge {{ $pageBlock->status ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $pageBlock->status ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                        <dt class="col-5">Page</dt>
                        <dd class="col-7">
                            @if ($pageBlock->page)
                                <a href="{{ route('admin.pages.edit', $pageBlock->page) }}">{{ $pageBlock->page->title }}</a>
                            @else
                                <span class="text-secondary">None</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card content-card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-1">Buttons</h2>
                </div>
                <div class="card-body p-4">
                    <dl class="mb-0">
                        <dt>Primary</dt>
                        <dd>{{ $pageBlock->button_text ?: '-' }} @if ($pageBlock->button_url)<code>{{ $pageBlock->button_url }}</code>@endif</dd>
                        <dt>Secondary</dt>
                        <dd class="mb-0">{{ $pageBlock->secondary_button_text ?: '-' }} @if ($pageBlock->secondary_button_url)<code>{{ $pageBlock->secondary_button_url }}</code>@endif</dd>
                    </dl>
                </div>
            </div>

            <div class="card content-card">
                <div class="card-header">
                    <h2 class="h5 mb-1">Media</h2>
                </div>
                <div class="card-body p-4">
                    @if ($pageBlock->image)
                        <img class="img-fluid rounded border mb-3" src="{{ asset($pageBlock->image) }}" alt="Block image">
                    @endif
                    @if ($pageBlock->background_image)
                        <img class="img-fluid rounded border" src="{{ asset($pageBlock->background_image) }}" alt="Block background image">
                    @endif
                    @if (! $pageBlock->image && ! $pageBlock->background_image)
                        <p class="text-secondary mb-0">No images have been uploaded.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
