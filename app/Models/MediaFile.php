<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\MediaLibraryStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaFile extends Model
{
    use BelongsToTenant, SoftDeletes;

    public const FILE_TYPES = ['image', 'document', 'other'];

    protected $fillable = [
        'tenant_id',
        'title',
        'file_name',
        'original_name',
        'file_path',
        'file_url',
        'mime_type',
        'file_type',
        'file_size',
        'alt_text',
        'caption',
        'uploaded_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            'file_size' => 'integer',
            'status' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::forceDeleted(fn (MediaFile $mediaFile) => MediaLibraryStorage::delete($mediaFile->file_path));
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function isImage(): bool
    {
        return $this->file_type === 'image';
    }

    public function publicUrl(): ?string
    {
        return MediaLibraryStorage::url($this->file_path, $this->file_url);
    }

    public function formattedFileSize(): string
    {
        $bytes = max(0, (int) $this->file_size);
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return number_format($bytes, $index === 0 ? 0 : 1).' '.$units[$index];
    }
}
