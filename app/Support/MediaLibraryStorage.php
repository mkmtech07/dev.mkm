<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class MediaLibraryStorage
{
    public const DISK = 'media_library';

    public const DIRECTORY = 'media-library';

    /**
     * @return array{file_name: string, original_name: string, file_path: string, file_url: string, mime_type: string|null, file_type: string, file_size: int}
     */
    public static function store(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = Str::uuid().($extension !== '' ? ".{$extension}" : '');
        $path = $file->storeAs(self::DIRECTORY, $fileName, self::DISK);

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('The media file could not be stored.');
        }

        return [
            'file_name' => $fileName,
            'original_name' => Str::limit(basename(str_replace('\\', '/', $file->getClientOriginalName())), 255, ''),
            'file_path' => $path,
            'file_url' => Storage::disk(self::DISK)->url($path),
            'mime_type' => $file->getMimeType(),
            'file_type' => self::fileType($extension),
            'file_size' => (int) $file->getSize(),
        ];
    }

    public static function delete(?string $path): void
    {
        if ($path && str_starts_with($path, self::DIRECTORY.'/') && ! str_contains($path, '..')) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    public static function url(?string $path, ?string $storedUrl = null): ?string
    {
        if ($path) {
            return Storage::disk(self::DISK)->url($path);
        }

        return $storedUrl;
    }

    private static function fileType(string $extension): string
    {
        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'], true)
            ? 'image'
            : 'document';
    }
}
