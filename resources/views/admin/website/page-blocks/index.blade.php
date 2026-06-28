@extends('layouts.admin')

@section('title', 'Page Blocks')
@section('page-title', 'Page Blocks')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Page builder blocks</h2>
            <p class="text-secondary mb-0">Build reusable landing page sections and assign them to dynamic pages.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.website.page-blocks.create', $pageId ? ['page_id' => $pageId] : []) }}">Add block</a>
    </div>

    <div class="card content-card">
        <div class="card-header">
            <form class="row g-2" method="GET" action="{{ route('admin.website.page-blocks.index') }}">
                <div class="col-sm-6 col-xl-4">
                    <label class="visually-hidden" for="search">Search blocks</label>
                    <input class="form-control" id="search" name="search" type="search" value="{{ $search }}" placeholder="Search title, key, page, or content">
                </div>
                <div class="col-sm-6 col-xl-3">
                    <label class="visually-hidden" for="page_id">Page</label>
                    <select class="form-select" id="page_id" name="page_id">
                        <option value="">All pages</option>
                        @foreach ($pages as $page)
                            <option value="{{ $page->id }}" @selected($pageId === $page->id)>{{ $page->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <label class="visually-hidden" for="type">Block type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">All types</option>
                        @foreach (\App\Models\PageBlock::TYPES as $blockType)
                            <option value="{{ $blockType }}" @selected($type === $blockType)>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $blockType)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto"><button class="btn btn-outline-primary" type="submit">Filter</button></div>
                @if ($search !== '' || $type !== '' || $pageId)
                    <div class="col-auto"><a class="btn btn-light" href="{{ route('admin.website.page-blocks.index') }}">Clear</a></div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Block</th>
                        <th scope="col">Page</th>
                        <th scope="col">Type</th>
                        <th scope="col">Key</th>
                        <th scope="col">Order</th>
                        <th scope="col">Status</th>
                        <th class="text-end" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($blocks as $block)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if ($block->image || $block->background_image)
                                        <img class="rounded border object-fit-cover" src="{{ asset($block->image ?: $block->background_image) }}" alt="" width="58" height="44">
                                    @else
                                        <div class="rounded bg-light border d-grid place-items-center text-secondary" style="width: 58px; height: 44px;">-</div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $block->title ?: 'Untitled block' }}</div>
                                        <div class="small text-secondary text-truncate" style="max-width: 24rem;">{{ $block->subtitle ?: ($block->content ?: 'No supporting content') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($block->page)
                                    <div class="fw-semibold">{{ $block->page->title }}</div>
                                    <a class="small text-secondary" href="{{ url($block->page->slug) }}" target="_blank" rel="noopener">/{{ $block->page->slug }}</a>
                                @else
                                    <span class="text-secondary">No page assigned</span>
                                @endif
                            </td>
                            <td><span class="badge text-bg-primary">{{ $block->typeLabel() }}</span></td>
                            <td><code>{{ $block->block_key ?: '-' }}</code></td>
                            <td>{{ $block->sort_order }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.website.page-blocks.toggle-status', $block) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm {{ $block->status ? 'btn-success' : 'btn-outline-secondary' }}" type="submit">
                                        {{ $block->status ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.website.page-blocks.show', $block) }}">View</a>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.website.page-blocks.edit', $block) }}">Edit</a>
                                <form class="d-inline" method="POST" action="{{ route('admin.website.page-blocks.destroy', $block) }}" onsubmit="return confirm('Delete this page block?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-5 text-center text-secondary" colspan="7">
                                {{ $search !== '' || $type !== '' || $pageId ? 'No page blocks match your filters.' : 'No page builder blocks have been created yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($blocks->hasPages())
            <div class="card-footer py-3">{{ $blocks->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
