<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BackupRecord extends Model
{
    use SoftDeletes;

    public const TYPES = ['database', 'files', 'full', 'content_export'];
    public const STATUSES = ['pending', 'processing', 'completed', 'failed'];

    protected $fillable = [
        'name', 'type', 'file_name', 'file_path', 'disk', 'file_size', 'status',
        'message', 'created_by', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'created_by' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public static function label(string $value): string
    {
        return str($value)->replace('_', ' ')->title()->toString();
    }

    public function formattedFileSize(): string
    {
        $bytes = max(0, (int) $this->file_size);
        if ($bytes === 0) {
            return '—';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $index = 0;
        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return number_format($bytes, $index === 0 ? 0 : 2).' '.$units[$index];
    }
}
