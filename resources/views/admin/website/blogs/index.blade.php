@extends('layouts.admin')

@section('title', 'Blog Posts')
@section('page-title', 'Blog Posts')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="h5 mb-1">Blog posts</h2>
            <p class="text-secondary mb-0">Create, schedule, and manage public blog content.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.blogs.create') }}">Add blog post</a>
    </div>

    <div class="card content-card">
        <div class="card-header">
            <form class="row g-2" method="GET" action="{{ route('admin.blogs.index') }}">
                <div class="col-sm-6 col-lg-5">
                    <label class="visually-hidden" for="search">Search blog posts</label>
                    <input
                        class="form-control"
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        placeholder="Search title, slug, excerpt, or author"
                    >
                </div>
                <div class="col-sm-4 col-lg-3">
                    <label class="visually-hidden" for="category">Filter by category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($categoryId === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                </div>
                @if ($search !== '' || $categoryId > 0)
                    <div class="col-auto">
                        <a class="btn btn-light" href="{{ route('admin.blogs.index') }}">Clear</a>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Post</th>
                        <th scope="col">Category / Author</th>
                        <th scope="col">Publication</th>
                        <th scope="col">Status</th>
                        <th scope="col">Featured</th>
                        <th scope="col">Views</th>
                        <th class="text-end" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($blogs as $blog)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if ($blog->featured_image)
                                        <img
                                            class="rounded object-fit-cover"
                                            src="{{ asset($blog->featured_image) }}"
                                            alt=""
                                            width="76"
                                            height="56"
                                        >
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $blog->title }}</div>
                                        <div class="small text-secondary">/blog/{{ $blog->slug }}</div>
                                        @if ($blog->excerpt)
                                            <div class="small text-secondary text-truncate" style="max-width: 26rem;">
                                                {{ $blog->excerpt }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $blog->category?->name ?: 'Uncategorized' }}</div>
                                <div class="small text-secondary">{{ $blog->author ?: 'No author' }}</div>
                            </td>
                            <td class="text-nowrap">
                                @if ($blog->publish_at)
                                    <div>{{ $blog->publish_at->format('d M Y') }}</div>
                                    <div class="small text-secondary">{{ $blog->publish_at->format('h:i A') }}</div>
                                @else
                                    <span class="text-secondary">Immediately</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.blogs.toggle-status', $blog) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        class="btn btn-sm {{ $blog->status ? 'btn-success' : 'btn-outline-secondary' }}"
                                        type="submit"
                                        title="Toggle status"
                                    >
                                        @if (! $blog->status)
                                            Inactive
                                        @elseif ($blog->publish_at?->isFuture())
                                            Scheduled
                                        @else
                                            Active
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.blogs.toggle-featured', $blog) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        class="btn btn-sm {{ $blog->is_featured ? 'btn-warning' : 'btn-outline-secondary' }}"
                                        type="submit"
                                        title="Toggle featured status"
                                    >
                                        {{ $blog->is_featured ? 'Featured' : 'Standard' }}
                                    </button>
                                </form>
                            </td>
                            <td>{{ number_format($blog->views) }}</td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.blogs.edit', $blog) }}">Edit</a>
                                <form
                                    class="d-inline"
                                    method="POST"
                                    action="{{ route('admin.blogs.destroy', $blog) }}"
                                    onsubmit="return confirm('Delete this blog post?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="py-5 text-center text-secondary" colspan="7">
                                {{ $search !== '' || $categoryId > 0 ? 'No blog posts match these filters.' : 'No blog posts have been created yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($blogs->hasPages())
            <div class="card-footer py-3">
                {{ $blogs->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
