<?php

namespace App\Support;

use App\Models\MediaFile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MediaPicker
{
    /**
     * @param array<int, string> $fields
     * @return array<string, array<int, mixed>>
     */
    public static function validationRules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $rules["{$field}_media_id"] = ['nullable', "required_if:{$field}_media_action,select", 'integer', Rule::exists('media_files', 'id')->where('status', true)->whereNull('deleted_at')];
            $rules["{$field}_media_action"] = ['nullable', "required_with:{$field}_media_id", 'string', Rule::in(['select', 'clear'])];
        }

        return $rules;
    }

    /**
     * @param array<int, string> $fields
     * @return array<int, string>
     */
    public static function fieldInputs(array $fields): array
    {
        return collect($fields)
            ->flatMap(fn (string $field) => ["{$field}_media_id", "{$field}_media_action"])
            ->all();
    }

    public static function selectedPath(Request $request, string $field, string $acceptType = 'image'): ?string
    {
        $action = (string) $request->input("{$field}_media_action");

        if ($action !== 'select') {
            return null;
        }

        abort_unless(
            $request->user()?->hasAnyPermission(['media_library.view', 'media_picker.use']),
            403,
            'You do not have permission to select media.'
        );

        $mediaFile = MediaFile::query()
            ->active()
            ->whereKey($request->integer("{$field}_media_id"))
            ->first();

        if (! $mediaFile || ($acceptType !== 'any' && $mediaFile->file_type !== $acceptType)) {
            throw ValidationException::withMessages([
                "{$field}_media_id" => 'Select an active '.($acceptType === 'any' ? 'media' : $acceptType).' file from the Media Library.',
            ]);
        }

        return self::publicPath($mediaFile);
    }

    public static function shouldClear(Request $request, string $field): bool
    {
        return $request->input("{$field}_media_action") === 'clear';
    }

    public static function publicPath(MediaFile $mediaFile): string
    {
        $path = ltrim(str_replace('\\', '/', (string) $mediaFile->file_path), '/');

        if (! str_starts_with($path, MediaLibraryStorage::DIRECTORY.'/') || str_contains($path, '../')) {
            throw ValidationException::withMessages([
                'media' => 'The selected media file is not available.',
            ]);
        }

        return 'storage/'.$path;
    }

    /**
     * @return array<string, mixed>
     */
    public static function payload(MediaFile $mediaFile): array
    {
        $path = self::publicPath($mediaFile);

        return [
            'id' => $mediaFile->id,
            'title' => $mediaFile->title ?: $mediaFile->original_name,
            'original_name' => $mediaFile->original_name,
            'file_type' => $mediaFile->file_type,
            'mime_type' => $mediaFile->mime_type,
            'file_size' => $mediaFile->file_size,
            'formatted_size' => $mediaFile->formattedFileSize(),
            'url' => asset($path),
            'path' => $path,
            'is_image' => $mediaFile->isImage(),
            'alt_text' => $mediaFile->alt_text,
            'caption' => $mediaFile->caption,
            'uploaded_at' => $mediaFile->created_at?->toIso8601String(),
        ];
    }
}
