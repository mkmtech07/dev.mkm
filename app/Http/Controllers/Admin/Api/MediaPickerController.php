<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\MediaFile;
use App\Support\MediaPicker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MediaPickerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'file_type' => ['nullable', Rule::in(MediaFile::FILE_TYPES)],
            'accept_type' => ['nullable', Rule::in([...MediaFile::FILE_TYPES, 'any'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:24'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $fileType = (string) ($validated['file_type'] ?? '');
        $acceptType = (string) ($validated['accept_type'] ?? 'any');

        $mediaFiles = MediaFile::query()
            ->active()
            ->when($acceptType !== '' && $acceptType !== 'any', fn ($query) => $query->where('file_type', $acceptType))
            ->when($fileType !== '', fn ($query) => $query->where('file_type', $fileType))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('original_name', 'like', "%{$search}%")
                        ->orWhere('file_name', 'like', "%{$search}%")
                        ->orWhere('mime_type', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate((int) ($validated['per_page'] ?? 12))
            ->withQueryString();

        return response()->json([
            'data' => $mediaFiles->getCollection()->map(fn (MediaFile $mediaFile) => MediaPicker::payload($mediaFile)),
            'meta' => [
                'current_page' => $mediaFiles->currentPage(),
                'last_page' => $mediaFiles->lastPage(),
                'per_page' => $mediaFiles->perPage(),
                'total' => $mediaFiles->total(),
            ],
        ]);
    }

    public function show(MediaFile $mediaFile): JsonResponse
    {
        abort_unless($mediaFile->status && ! $mediaFile->trashed(), 404);

        return response()->json([
            'data' => MediaPicker::payload($mediaFile),
        ]);
    }
}
