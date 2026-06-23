<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_access_control_routes_require_authentication(): void
    {
        $role = Role::create(['name' => 'Editor', 'slug' => 'editor', 'status' => true]);
        $permission = Permission::create(['name' => 'Pages View', 'slug' => 'pages.view', 'status' => true]);
        $user = User::factory()->create();

        $this->get(route('admin.roles.index'))->assertRedirect(route('login'));
        $this->get(route('admin.roles.edit', $role))->assertRedirect(route('login'));
        $this->get(route('admin.permissions.edit', $permission))->assertRedirect(route('login'));
        $this->get(route('admin.user-roles.edit', $user))->assertRedirect(route('login'));
    }

    public function test_default_roles_permissions_and_main_super_admin_are_seeded_idempotently(): void
    {
        $mainAdmin = User::factory()->create();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $this->assertDatabaseCount('roles', 5);
        $this->assertDatabaseHas('roles', ['slug' => Role::SUPER_ADMIN, 'status' => true]);
        $this->assertDatabaseHas('permissions', ['slug' => 'backups.download', 'module' => 'Backups']);
        $this->assertDatabaseHas('permissions', ['slug' => 'users.roles.manage', 'module' => 'User Roles']);
        $this->assertTrue($mainAdmin->fresh()->isSuperAdmin());
        $this->assertTrue($mainAdmin->fresh()->hasPermission('permissions.delete'));
        $this->assertSame(Permission::query()->count(), Role::where('slug', Role::SUPER_ADMIN)->firstOrFail()->permissions()->count());
    }

    public function test_existing_admin_seeder_assigns_explicit_super_admin_access(): void
    {
        User::factory()->create(['email' => 'first@example.com']);

        $this->seed(AdminSeeder::class);
        $this->seed(AdminSeeder::class);

        $seededAdmin = User::where('email', 'superadmin@billsoft.com')->firstOrFail();
        $this->assertTrue($seededAdmin->hasRole(Role::SUPER_ADMIN));
        $this->assertTrue($seededAdmin->isSuperAdmin());
        $this->assertDatabaseCount('users', 2);
    }

    public function test_only_active_roles_and_permissions_grant_access(): void
    {
        $mainAdmin = User::factory()->create();
        $staff = User::factory()->create();
        $permission = Permission::create(['name' => 'Backups View', 'slug' => 'backups.view', 'status' => true]);
        $role = Role::create(['name' => 'Backup Reader', 'slug' => 'backup-reader', 'status' => true]);
        $role->permissions()->attach($permission);
        $staff->roles()->attach($role);

        $this->assertTrue($mainAdmin->hasPermission('anything.at_all'));
        $this->assertTrue($staff->hasRole('backup-reader'));
        $this->assertTrue($staff->hasPermission('backups.view'));
        $this->assertTrue($staff->hasAnyPermission(['activity_logs.view', 'backups.view']));

        $permission->update(['status' => false]);
        $this->assertFalse($staff->hasPermission('backups.view'));
        $permission->update(['status' => true]);
        $role->update(['status' => false]);
        $this->assertFalse($staff->hasRole('backup-reader'));
        $this->assertFalse($staff->hasPermission('backups.view'));
    }

    public function test_permission_middleware_and_sidebar_respect_operation_access(): void
    {
        User::factory()->create(); // Permanent main super admin.
        $staff = User::factory()->create();
        $dashboard = Permission::create(['name' => 'Dashboard View', 'slug' => 'dashboard.view', 'module' => 'Dashboard', 'status' => true]);
        $backups = Permission::create(['name' => 'Backups View', 'slug' => 'backups.view', 'module' => 'Backups', 'status' => true]);
        $role = Role::create(['name' => 'Backup Reader', 'slug' => 'backup-reader', 'status' => true]);
        $role->permissions()->sync([$dashboard->id, $backups->id]);
        $staff->roles()->attach($role);

        $this->actingAs($staff)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Backups')
            ->assertDontSee('Activity Logs')
            ->assertDontSee('User Roles');
        $this->actingAs($staff)->get(route('admin.backups.index'))->assertOk();
        $this->actingAs($staff)->get(route('admin.backups.create'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.activity-logs.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.website.settings.edit'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.roles.index'))->assertForbidden();
    }

    public function test_super_admin_can_manage_roles_permissions_and_role_activity_is_logged(): void
    {
        $admin = User::factory()->create();
        $this->seed(RolePermissionSeeder::class);
        $pageView = Permission::where('slug', 'pages.view')->firstOrFail();
        $pageEdit = Permission::where('slug', 'pages.edit')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.roles.store'), [
            'name' => '<b>Content Reviewer</b>',
            'slug' => 'Content Reviewer',
            'description' => '<p>Reviews website content.</p>',
            'permissions' => [$pageView->id, $pageView->id],
            'status' => '1',
        ])->assertSessionHasErrors('permissions.1');

        $this->actingAs($admin)->post(route('admin.roles.store'), [
            'name' => '<b>Content Reviewer</b>',
            'slug' => 'Content Reviewer',
            'description' => '<p>Reviews website content.</p>',
            'permissions' => [$pageView->id],
            'status' => '1',
        ])->assertRedirect(route('admin.roles.index'));
        $role = Role::where('slug', 'content-reviewer')->firstOrFail();
        $this->assertSame('Content Reviewer', $role->name);
        $this->assertSame('Reviews website content.', $role->description);
        $this->assertTrue($role->permissions()->whereKey($pageView->id)->exists());
        $this->assertDatabaseHas('activity_logs', ['action' => 'create', 'module' => 'roles', 'model_id' => $role->id]);

        $this->actingAs($admin)->put(route('admin.roles.update', $role), [
            'name' => 'Senior Reviewer', 'slug' => 'senior-reviewer',
            'permissions' => [$pageView->id, $pageEdit->id], 'status' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.roles.index'));
        $this->assertSame(2, $role->fresh()->permissions()->count());
        $this->actingAs($admin)->get(route('admin.roles.show', $role->fresh()))
            ->assertOk()->assertSee('pages.view')->assertSee('pages.edit');

        $this->actingAs($admin)->delete(route('admin.roles.destroy', $role->fresh()))
            ->assertRedirect(route('admin.roles.index'));
        $this->assertSoftDeleted('roles', ['id' => $role->id]);

        $superRole = Role::where('slug', Role::SUPER_ADMIN)->firstOrFail();
        $this->actingAs($admin)->delete(route('admin.roles.destroy', $superRole))
            ->assertSessionHas('error');
        $this->assertNotSoftDeleted('roles', ['id' => $superRole->id]);
    }

    public function test_permission_crud_filtering_and_critical_safeguards_work(): void
    {
        $admin = User::factory()->create();
        $this->seed(RolePermissionSeeder::class);

        $this->actingAs($admin)->post(route('admin.permissions.store'), [
            'name' => 'Reports Export', 'slug' => 'reports-export', 'module' => 'Reports', 'status' => '1',
        ])->assertSessionHasErrors('slug');
        $this->actingAs($admin)->post(route('admin.permissions.store'), [
            'name' => 'Reports Export', 'slug' => 'reports.export', 'module' => 'Reports',
            'description' => '<b>Export reports.</b>', 'status' => '1',
        ])->assertRedirect(route('admin.permissions.index'));
        $permission = Permission::where('slug', 'reports.export')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.permissions.index', ['search' => 'reports', 'module' => 'Reports']))
            ->assertOk()->assertSee('reports.export')->assertDontSee('pages.view');
        $this->actingAs($admin)->put(route('admin.permissions.update', $permission), [
            'name' => 'Reports Download', 'slug' => 'reports.download', 'module' => 'Reports', 'status' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('permissions', ['id' => $permission->id, 'slug' => 'reports.download']);
        $this->actingAs($admin)->delete(route('admin.permissions.destroy', $permission->fresh()));
        $this->assertSoftDeleted('permissions', ['id' => $permission->id]);

        $critical = Permission::where('slug', 'roles.delete')->firstOrFail();
        $this->actingAs($admin)->delete(route('admin.permissions.destroy', $critical))->assertSessionHas('error');
        $this->actingAs($admin)->patch(route('admin.permissions.toggle-status', $critical))->assertSessionHas('error');
        $this->assertTrue($critical->fresh()->status);
    }

    public function test_user_role_assignment_preserves_main_super_admin_and_is_audited(): void
    {
        $admin = User::factory()->create(['email' => 'owner@example.com']);
        $staff = User::factory()->create(['email' => 'staff@example.com']);
        $this->seed(RolePermissionSeeder::class);
        $editor = Role::where('slug', 'editor')->firstOrFail();
        $super = Role::where('slug', Role::SUPER_ADMIN)->firstOrFail();

        $this->actingAs($admin)->put(route('admin.user-roles.update', $staff), ['roles' => [$editor->id]])
            ->assertRedirect(route('admin.user-roles.index'));
        $this->assertTrue($staff->fresh()->hasRole('editor'));

        $this->actingAs($admin)->put(route('admin.user-roles.update', $admin), ['roles' => []])
            ->assertRedirect(route('admin.user-roles.index'));
        $this->assertTrue($admin->fresh()->roles()->whereKey($super->id)->exists());
        $this->assertTrue($admin->fresh()->isSuperAdmin());
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'update', 'module' => 'user_roles', 'model_type' => User::class, 'model_id' => $staff->id,
        ]);
    }
}
