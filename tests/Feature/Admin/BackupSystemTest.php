<?php

namespace Tests\Feature\Admin;

use App\Models\BackupRecord;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_routes_require_authentication(): void
    {
        $backup = BackupRecord::create($this->recordData());

        $this->get(route('admin.backups.index'))->assertRedirect(route('login'));
        $this->get(route('admin.backups.create'))->assertRedirect(route('login'));
        $this->get(route('admin.backups.show', $backup))->assertRedirect(route('login'));
        $this->get(route('admin.backups.download', $backup))->assertRedirect(route('login'));
    }

    public function test_admin_pages_render_and_backups_can_be_filtered(): void
    {
        $admin = User::factory()->create();
        BackupRecord::create($this->recordData([
            'name' => 'Nightly database', 'type' => 'database', 'status' => 'completed',
        ]));
        BackupRecord::create($this->recordData([
            'name' => 'Uploads only', 'type' => 'files', 'status' => 'failed',
            'file_name' => 'files-backup.zip', 'file_path' => 'backups/files-backup.zip',
        ]));

        $this->actingAs($admin)->get(route('admin.backups.create'))
            ->assertOk()->assertSee('Create a private backup archive');
        $this->actingAs($admin)->get(route('admin.backups.index', [
            'search' => 'Nightly', 'type' => 'database', 'status' => 'completed',
        ]))->assertOk()->assertSee('Nightly database')->assertDontSee('Uploads only');
    }

    public function test_backup_request_is_validated_and_tooling_failure_is_recorded_safely(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin)->post(route('admin.backups.store'), [
            'type' => 'invalid',
        ])->assertSessionHasErrors('type');

        $response = $this->actingAs($admin)->post(route('admin.backups.store'), [
            'name' => '<b>Database snapshot</b>',
            'type' => 'database',
        ]);
        $backup = BackupRecord::firstOrFail();
        $response->assertRedirect(route('admin.backups.show', $backup));
        $this->assertSame('Database snapshot', $backup->name);
        $this->assertSame($admin->id, $backup->created_by);

        if (class_exists(\ZipArchive::class)) {
            $this->assertSame('completed', $backup->status);
            $this->assertTrue(Storage::disk('local')->exists($backup->file_path));
            Storage::disk('local')->delete($backup->file_path);
        } else {
            $this->assertSame('failed', $backup->status);
            $this->assertStringContainsString('ZIP support is unavailable', $backup->message);
            $this->assertStringNotContainsString('password', strtolower($backup->message));
        }
    }

    public function test_completed_backup_download_is_private_and_record_bound(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $backup = BackupRecord::create($this->recordData());
        Storage::disk('local')->put($backup->file_path, 'private zip bytes');

        $this->get(route('admin.backups.download', $backup))->assertRedirect(route('login'));
        $this->actingAs($admin)->get(route('admin.backups.download', $backup))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/zip');

        $invalid = BackupRecord::create($this->recordData([
            'file_name' => 'database-backup.zip',
            'file_path' => '../database-backup.zip',
        ]));
        Storage::disk('local')->put('database-backup.zip', 'must remain inaccessible');
        $this->actingAs($admin)->get(route('admin.backups.download', $invalid))->assertNotFound();
    }

    public function test_delete_removes_private_file_and_soft_deletes_record(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $backup = BackupRecord::create($this->recordData());
        Storage::disk('local')->put($backup->file_path, 'private zip bytes');

        $this->actingAs($admin)->delete(route('admin.backups.destroy', $backup))
            ->assertRedirect(route('admin.backups.index'));

        Storage::disk('local')->assertMissing($backup->file_path);
        $this->assertSoftDeleted($backup);
    }

    public function test_all_archive_types_generate_when_zip_support_is_available(): void
    {
        if (! class_exists(\ZipArchive::class)) {
            $this->assertFalse(class_exists(\ZipArchive::class));

            return;
        }

        $admin = User::factory()->create();
        foreach (BackupRecord::TYPES as $type) {
            $this->actingAs($admin)->post(route('admin.backups.store'), [
                'name' => BackupRecord::label($type),
                'type' => $type,
            ])->assertSessionHasNoErrors();

            $backup = BackupRecord::query()->latest('id')->firstOrFail();
            $this->assertSame('completed', $backup->status, $backup->message ?? $type);
            $this->assertTrue(Storage::disk('local')->exists($backup->file_path));

            $zip = new \ZipArchive;
            $this->assertTrue($zip->open(Storage::disk('local')->path($backup->file_path)) === true);
            $this->assertGreaterThan(0, $zip->numFiles);
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entry = (string) $zip->getNameIndex($index);
                $this->assertStringNotContainsString('.env', $entry);
                $this->assertStringNotContainsString('node_modules', $entry);
                $this->assertStringNotContainsString('vendor/', $entry);
            }
            $zip->close();

            Storage::disk('local')->delete($backup->file_path);
        }
    }

    /** @return array<string, mixed> */
    private function recordData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Database backup',
            'type' => 'database',
            'file_name' => 'database-backup-2026-06-21-120000.zip',
            'file_path' => BackupService::DIRECTORY.'/database-backup-2026-06-21-120000.zip',
            'disk' => BackupService::DISK,
            'file_size' => 1024,
            'status' => 'completed',
            'message' => 'Backup generated successfully.',
            'completed_at' => now(),
        ], $overrides);
    }
}
