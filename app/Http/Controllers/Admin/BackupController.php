<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackupRequest;
use App\Models\BackupRecord;
use App\Services\BackupService;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class BackupController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $backups = $this->applyFilters(BackupRecord::query()->with('creator:id,name'), $filters)
            ->latest()
            ->paginate(15)
            ->withQueryString();
        $summary = [
            'total' => BackupRecord::query()->count(),
            'completed' => BackupRecord::query()->where('status', 'completed')->count(),
            'failed' => BackupRecord::query()->where('status', 'failed')->count(),
            'database' => BackupRecord::query()->where('type', 'database')->count(),
            'full' => BackupRecord::query()->where('type', 'full')->count(),
            'storage' => (int) BackupRecord::query()->completed()->sum('file_size'),
        ];

        return view('admin.backups.index', [
            'backups' => $backups,
            'summary' => $summary,
            ...$filters,
        ]);
    }

    public function create(): View
    {
        return view('admin.backups.create', ['zipAvailable' => class_exists(\ZipArchive::class)]);
    }

    public function store(
        BackupRequest $request,
        BackupService $backupService,
        ActivityLogger $activityLogger
    ): RedirectResponse
    {
        $placeholder = 'pending-'.Str::uuid().'.zip';
        $backup = BackupRecord::create([
            ...$request->validated(),
            'file_name' => $placeholder,
            'file_path' => BackupService::DIRECTORY.'/'.$placeholder,
            'disk' => BackupService::DISK,
            'status' => 'pending',
            'created_by' => $request->user()?->id,
        ]);
        $backup->update(['status' => 'processing', 'message' => 'Backup generation is in progress.']);

        try {
            $result = $backupService->generate($backup);
            $backup->update([
                ...$result,
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            $activityLogger->log(
                'backup',
                'backups',
                'Generated '.BackupRecord::label($backup->type).' archive.',
                $backup,
                null,
                ['file_name' => $backup->file_name, 'file_size' => $backup->file_size],
            );

            return to_route('admin.backups.show', $backup)
                ->with('success', 'Backup generated successfully.');
        } catch (Throwable $exception) {
            Log::warning('Backup generation failed.', [
                'backup_id' => $backup->id,
                'exception' => $exception::class,
            ]);
            $message = $this->safeFailureMessage($exception);
            $backup->update([
                'status' => 'failed',
                'message' => $message,
                'completed_at' => now(),
            ]);
            $activityLogger->log(
                'backup',
                'backups',
                'Failed to generate '.BackupRecord::label($backup->type).' archive.',
                $backup,
                null,
                ['message' => $message],
                'failed',
            );

            return to_route('admin.backups.show', $backup)
                ->with('error', $message);
        }
    }

    public function show(BackupRecord $backup): View
    {
        $backup->load('creator:id,name,email');

        return view('admin.backups.show', compact('backup'));
    }

    public function download(BackupRecord $backup): StreamedResponse
    {
        abort_unless($backup->status === 'completed' && $backup->disk === BackupService::DISK, 404);
        abort_unless($this->validStoredPath($backup), 404);
        abort_unless(Storage::disk(BackupService::DISK)->exists($backup->file_path), 404);

        return Storage::disk(BackupService::DISK)->download(
            $backup->file_path,
            $backup->file_name,
            ['Content-Type' => 'application/zip', 'X-Content-Type-Options' => 'nosniff']
        );
    }

    public function destroy(BackupRecord $backup): RedirectResponse
    {
        if ($backup->disk === BackupService::DISK && $this->validStoredPath($backup)) {
            $disk = Storage::disk(BackupService::DISK);
            if ($disk->exists($backup->file_path) && ! $disk->delete($backup->file_path)) {
                return back()->with('error', 'The backup file could not be deleted from private storage.');
            }
        }

        $backup->delete();

        return to_route('admin.backups.index')->with('success', 'Backup deleted successfully.');
    }

    /** @return array<string, mixed> */
    private function filters(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search')),
            'type' => in_array($request->query('type'), BackupRecord::TYPES, true) ? $request->query('type') : '',
            'status' => in_array($request->query('status'), BackupRecord::STATUSES, true) ? $request->query('status') : '',
            'dateFrom' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query('date_from')) ? $request->query('date_from') : '',
            'dateTo' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query('date_to')) ? $request->query('date_to') : '',
        ];
    }

    /** @param array<string, mixed> $filters */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($filters) {
                $search = $filters['search'];
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            }))
            ->when($filters['type'] !== '', fn (Builder $query) => $query->where('type', $filters['type']))
            ->when($filters['status'] !== '', fn (Builder $query) => $query->where('status', $filters['status']))
            ->when($filters['dateFrom'] !== '', fn (Builder $query) => $query->whereDate('created_at', '>=', $filters['dateFrom']))
            ->when($filters['dateTo'] !== '', fn (Builder $query) => $query->whereDate('created_at', '<=', $filters['dateTo']));
    }

    private function validStoredPath(BackupRecord $backup): bool
    {
        if ($backup->file_name !== basename(str_replace('\\', '/', $backup->file_name))) {
            return false;
        }
        if (! preg_match('/^[A-Za-z0-9._-]+\.zip$/', $backup->file_name)) {
            return false;
        }

        return hash_equals(BackupService::DIRECTORY.'/'.$backup->file_name, str_replace('\\', '/', $backup->file_path));
    }

    private function safeFailureMessage(Throwable $exception): string
    {
        $message = $exception->getMessage();
        $safePrefixes = [
            'ZIP support is unavailable.',
            'The backup archive could not be created.',
            'The backup archive could not be finalized.',
            'The generated backup archive is empty.',
            'The selected backup type is not supported.',
            'A temporary export file could not be created.',
            'The database export could not be added',
            'The content export could not be added',
            'An uploaded file could not be added',
        ];

        foreach ($safePrefixes as $prefix) {
            if (str_starts_with($message, $prefix)) {
                return Str::limit($message, 500, '');
            }
        }

        return 'Backup generation failed. Check private storage permissions and server backup tooling.';
    }
}
