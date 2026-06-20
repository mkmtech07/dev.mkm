<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PublicImage
{
    public static function store(UploadedFile $image, string $directory): string
    {
        $directory = trim($directory, '/\\');

        if ($directory === '' || str_contains($directory, '..')) {
            throw new InvalidArgumentException('A valid public image directory is required.');
        }

        $relativeDirectory = "assets/images/{$directory}";
        $destination = public_path($relativeDirectory);

        File::ensureDirectoryExists($destination);

        $extension = strtolower($image->getClientOriginalExtension());
        $filename = Str::uuid().($extension !== '' ? ".{$extension}" : '');

        $image->move($destination, $filename);

        return "{$relativeDirectory}/{$filename}";
    }

    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        $relativePath = ltrim(str_replace('\\', '/', $path), '/');

        if (! str_starts_with($relativePath, 'assets/images/') || str_contains($relativePath, '../')) {
            return;
        }

        $fullPath = public_path($relativePath);

        if (File::isFile($fullPath)) {
            File::delete($fullPath);
        }
    }
}
