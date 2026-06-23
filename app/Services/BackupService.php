<?php

namespace App\Services;

use App\Models\BackupRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class BackupService
{
    public const DISK = 'local';
    public const DIRECTORY = 'backups';

    /**
     * @return array{file_name: string, file_path: string, file_size: int, message: string}
     */
    public function generate(BackupRecord $record): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZIP support is unavailable. Enable the PHP zip extension to generate backups.');
        }

        Storage::disk(self::DISK)->makeDirectory(self::DIRECTORY);
        $fileName = $this->safeBackupFileName($record->type);
        $relativePath = self::DIRECTORY.'/'.$fileName;
        $destination = Storage::disk(self::DISK)->path($relativePath);
        $temporaryDirectory = storage_path('app/private/backup-temp/'.Str::uuid());
        File::ensureDirectoryExists($temporaryDirectory);
        $zip = new ZipArchive;

        try {
            if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('The backup archive could not be created. Check private storage permissions.');
            }

            match ($record->type) {
                'database' => $this->createDatabaseBackup($zip, $temporaryDirectory),
                'files' => $this->createFilesBackup($zip),
                'full' => $this->createFullBackup($zip, $temporaryDirectory),
                'content_export' => $this->createContentExport($zip, $temporaryDirectory),
                default => throw new RuntimeException('The selected backup type is not supported.'),
            };

            if (! $zip->close()) {
                throw new RuntimeException('The backup archive could not be finalized.');
            }

            clearstatcache(true, $destination);
            $fileSize = (int) filesize($destination);
            if ($fileSize <= 0) {
                throw new RuntimeException('The generated backup archive is empty.');
            }

            return [
                'file_name' => $fileName,
                'file_path' => $relativePath,
                'file_size' => $fileSize,
                'message' => 'Backup generated successfully ('.$this->formatFileSize($fileSize).').',
            ];
        } catch (Throwable $exception) {
            if ($zip->status === ZipArchive::ER_OK) {
                $zip->close();
            }
            Storage::disk(self::DISK)->delete($relativePath);
            throw $exception;
        } finally {
            if (File::isDirectory($temporaryDirectory)) {
                File::deleteDirectory($temporaryDirectory);
            }
        }
    }

    private function createDatabaseBackup(ZipArchive $zip, string $temporaryDirectory): void
    {
        $dump = $this->createMysqlDump($temporaryDirectory)
            ?? $this->createDatabaseJson($temporaryDirectory);

        if (! $zip->addFile($dump, 'database/'.basename($dump))) {
            throw new RuntimeException('The database export could not be added to the archive.');
        }
    }

    private function createFilesBackup(ZipArchive $zip): void
    {
        $sources = [
            storage_path('app/public') => 'uploads/storage',
            public_path('assets/images') => 'uploads/public-assets/images',
        ];
        $added = 0;

        foreach ($sources as $source => $archiveRoot) {
            $added += $this->addDirectory($zip, $source, $archiveRoot);
        }

        if ($added === 0) {
            $zip->addFromString('uploads/README.txt', 'No uploaded files were present when this backup was generated.');
        }
    }

    private function createFullBackup(ZipArchive $zip, string $temporaryDirectory): void
    {
        $this->createDatabaseBackup($zip, $temporaryDirectory);
        $this->createFilesBackup($zip);
    }

    private function createContentExport(ZipArchive $zip, string $temporaryDirectory): void
    {
        $tables = [
            'pages', 'blogs', 'blog_categories', 'services', 'galleries', 'faqs', 'testimonials',
            'menus', 'menu_items', 'footer_settings', 'footer_sections', 'footer_links',
            'footer_social_links', 'website_settings', 'homepage_sections', 'hero_sliders',
            'about_sections', 'team_members', 'media_files', 'seo_pages', 'seo_settings',
            'schema_markups', 'leads', 'lead_notes', 'newsletter_subscribers', 'contact_messages',
        ];
        $path = $temporaryDirectory.DIRECTORY_SEPARATOR.'content-export.json';
        $this->writeJsonExport($path, array_values(array_filter($tables, Schema::hasTable(...))));

        if (! $zip->addFile($path, 'content/content-export.json')) {
            throw new RuntimeException('The content export could not be added to the archive.');
        }
    }

    private function createDatabaseJson(string $temporaryDirectory): string
    {
        $excluded = [
            'cache', 'cache_locks', 'sessions', 'jobs', 'job_batches', 'failed_jobs', 'sqlite_sequence',
        ];
        $tables = array_values(array_filter(
            Schema::getTableListing(),
            fn (string $table) => ! in_array($table, $excluded, true)
        ));
        sort($tables);
        $path = $temporaryDirectory.DIRECTORY_SEPARATOR.'database-export.json';
        $this->writeJsonExport($path, $tables);

        return $path;
    }

    private function createMysqlDump(string $temporaryDirectory): ?string
    {
        $connectionName = config('database.default');
        $connection = config("database.connections.{$connectionName}", []);
        if (! in_array($connection['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            return null;
        }

        $executable = $this->findMysqlDump();
        if (! $executable) {
            return null;
        }

        $defaultsFile = $temporaryDirectory.DIRECTORY_SEPARATOR.'mysql-client.cnf';
        $sqlPath = $temporaryDirectory.DIRECTORY_SEPARATOR.'database.sql';
        $clientConfiguration = [
            '[client]',
            'host='.$this->iniValue($connection['host'] ?? '127.0.0.1'),
            'port='.$this->iniValue($connection['port'] ?? 3306),
            'user='.$this->iniValue($connection['username'] ?? ''),
            'password='.$this->iniValue($connection['password'] ?? ''),
        ];
        File::put($defaultsFile, implode(PHP_EOL, $clientConfiguration).PHP_EOL);

        $process = new Process([
            $executable,
            '--defaults-extra-file='.$defaultsFile,
            '--single-transaction',
            '--skip-lock-tables',
            '--routines',
            '--triggers',
            '--default-character-set=utf8mb4',
            (string) ($connection['database'] ?? ''),
        ]);
        $process->setTimeout(600);
        $handle = fopen($sqlPath, 'wb');
        try {
            $process->run(function (string $type, string $buffer) use ($handle): void {
                if ($type === Process::OUT) {
                    fwrite($handle, $buffer);
                }
            });
        } finally {
            fclose($handle);
            File::delete($defaultsFile);
        }

        if (! $process->isSuccessful() || ! File::isFile($sqlPath) || File::size($sqlPath) === 0) {
            File::delete($sqlPath);

            return null;
        }

        return $sqlPath;
    }

    /** @param list<string> $tables */
    private function writeJsonExport(string $path, array $tables): void
    {
        $handle = fopen($path, 'wb');
        if (! is_resource($handle)) {
            throw new RuntimeException('A temporary export file could not be created.');
        }

        try {
            fwrite($handle, '{"generated_at":'.json_encode(now()->toIso8601String(), JSON_THROW_ON_ERROR));
            fwrite($handle, ',"database_driver":'.json_encode((string) DB::connection()->getDriverName(), JSON_THROW_ON_ERROR));
            fwrite($handle, ',"tables":{');
            $firstTable = true;

            foreach ($tables as $table) {
                if (! $firstTable) {
                    fwrite($handle, ',');
                }
                $firstTable = false;
                fwrite($handle, json_encode($table, JSON_THROW_ON_ERROR).':[');
                $firstRow = true;
                $query = DB::table($table);
                if (Schema::hasColumn($table, 'id')) {
                    $query->orderBy('id');
                }
                foreach ($query->cursor() as $row) {
                    if (! $firstRow) {
                        fwrite($handle, ',');
                    }
                    $firstRow = false;
                    fwrite($handle, json_encode((array) $row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR));
                }
                fwrite($handle, ']');
            }

            fwrite($handle, '}}');
        } finally {
            fclose($handle);
        }
    }

    private function addDirectory(ZipArchive $zip, string $source, string $archiveRoot): int
    {
        if (! File::isDirectory($source)) {
            return 0;
        }

        $source = rtrim(str_replace('\\', '/', realpath($source) ?: $source), '/');
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        $added = 0;

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->isLink()) {
                continue;
            }
            $realPath = str_replace('\\', '/', $file->getRealPath() ?: '');
            if ($realPath === '' || ! str_starts_with($realPath, $source.'/')) {
                continue;
            }
            $relative = ltrim(substr($realPath, strlen($source)), '/');
            if ($relative === '' || str_contains($relative, '../')) {
                continue;
            }
            if (! $zip->addFile($realPath, trim($archiveRoot, '/').'/'.$relative)) {
                throw new RuntimeException('An uploaded file could not be added to the backup archive.');
            }
            $added++;
        }

        return $added;
    }

    private function safeBackupFileName(string $type): string
    {
        $prefix = match ($type) {
            'database' => 'database-backup',
            'files' => 'files-backup',
            'full' => 'full-backup',
            'content_export' => 'content-export',
            default => 'backup',
        };
        $base = $prefix.'-'.now()->format('Y-m-d-His');
        $fileName = $base.'.zip';
        $suffix = 1;
        while (Storage::disk(self::DISK)->exists(self::DIRECTORY.'/'.$fileName)) {
            $fileName = $base.'-'.$suffix.'.zip';
            $suffix++;
        }

        return $fileName;
    }

    private function findMysqlDump(): ?string
    {
        $finder = new ExecutableFinder;
        $candidates = array_filter([
            $finder->find('mysqldump'),
            dirname(dirname(base_path())).DIRECTORY_SEPARATOR.'mysql'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'mysqldump.exe',
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
        ]);

        foreach ($candidates as $candidate) {
            if (is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function iniValue(mixed $value): string
    {
        return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], (string) $value).'"';
    }

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = max(0, $bytes);
        $index = 0;
        while ($size >= 1024 && $index < count($units) - 1) {
            $size /= 1024;
            $index++;
        }

        return number_format($size, $index === 0 ? 0 : 2).' '.$units[$index];
    }
}
