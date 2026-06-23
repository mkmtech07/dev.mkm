<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\MediaFileRequest;
use App\Models\MediaFile;
use App\Support\MediaLibraryStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class MediaLibraryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $fileType = trim((string) $request->query('file_type'));
        $status = trim((string) $request->query('status'));
        $viewMode = $request->query('view') === 'table' ? 'table' : 'grid';

        $mediaFiles = MediaFile::query()
            ->with('uploader:id,name')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('original_name', 'like', "%{$search}%")
                        ->orWhere('file_name', 'like', "%{$search}%")
                        ->orWhere('mime_type', 'like', "%{$search}%");
                });
            })
            ->when(in_array($fileType, MediaFile::FILE_TYPES, true), fn ($query) => $query->where('file_type', $fileType))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('status', $status === 'active'))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.website.media-library.index', compact(
            'mediaFiles',
            'search',
            'fileType',
            'status',
            'viewMode'
        ));
    }

    public function create(): View
    {
        return view('admin.website.media-library.create', [
            'mediaFile' => new MediaFile(['status' => true]),
        ]);
    }

    public function store(MediaFileRequest $request): RedirectResponse
    {
        $stored = MediaLibraryStorage::store($request->file('file'));

        try {
            MediaFile::create([
                ...$request->safe()->except('file'),
                ...$stored,
                'uploaded_by' => $request->user()->getKey(),
            ]);
        } catch (Throwable $exception) {
            MediaLibraryStorage::delete($stored['file_path']);
            throw $exception;
        }

        return to_route('admin.website.media-library.index')
            ->with('success', 'Media file uploaded successfully.');
    }

    public function show(MediaFile $mediaFile): View
    {
        $mediaFile->load('uploader:id,name');

        return view('admin.website.media-library.show', compact('mediaFile'));
    }

    public function edit(MediaFile $mediaFile): View
    {
        return view('admin.website.media-library.edit', compact('mediaFile'));
    }

    public function update(MediaFileRequest $request, MediaFile $mediaFile): RedirectResponse
    {
        $data = $request->safe()->except('file');
        $stored = null;

        if ($request->hasFile('file')) {
            $stored = MediaLibraryStorage::store($request->file('file'));
            $data = [...$data, ...$stored];
        }

        try {
            $oldPath = $mediaFile->file_path;
            $mediaFile->update($data);
        } catch (Throwable $exception) {
            MediaLibraryStorage::delete($stored['file_path'] ?? null);
            throw $exception;
        }

        if ($stored) {
            MediaLibraryStorage::delete($oldPath);
        }

        return to_route('admin.website.media-library.show', $mediaFile)
            ->with('success', 'Media file updated successfully.');
    }

    public function destroy(MediaFile $mediaFile): RedirectResponse
    {
        $mediaFile->delete();

        return to_route('admin.website.media-library.index')
            ->with('success', 'Media file moved to the deleted records successfully.');
    }

    public function toggleStatus(MediaFile $mediaFile): RedirectResponse
    {
        $mediaFile->update(['status' => ! $mediaFile->status]);

        return back()->with('success', 'Media status updated successfully.');
    }
}
