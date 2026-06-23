<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = in_array($request->query('status'), ['active', 'inactive'], true) ? $request->query('status') : '';
        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status === 'active'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.roles.index', compact('roles', 'search', 'status'));
    }

    public function create(): View
    {
        return view('admin.roles.create', [
            'role' => new Role(['status' => true]),
            'permissionGroups' => $this->permissionGroups(),
            'selectedPermissions' => [],
        ]);
    }

    public function store(RoleRequest $request, ActivityLogger $logger): RedirectResponse
    {
        $validated = $request->validated();
        $role = DB::transaction(function () use ($validated): Role {
            $role = Role::create([
                ...collect($validated)->except('permissions')->all(),
                'status' => (bool) ($validated['status'] ?? false),
            ]);
            $role->permissions()->sync($validated['permissions'] ?? []);

            return $role;
        });
        $logger->log('create', 'roles', "Created role {$role->name}.", $role, null, $this->snapshot($role));

        return to_route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function show(Role $role): View
    {
        $role->load(['permissions' => fn ($query) => $query->orderBy('module')->orderBy('name'), 'users:id,name,email']);

        return view('admin.roles.show', compact('role'));
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', [
            'role' => $role,
            'permissionGroups' => $this->permissionGroups(),
            'selectedPermissions' => $role->permissions()->pluck('permissions.id')->all(),
        ]);
    }

    public function update(RoleRequest $request, Role $role, ActivityLogger $logger): RedirectResponse
    {
        $validated = $request->validated();
        $oldValues = $this->snapshot($role);

        if ($role->slug === Role::SUPER_ADMIN) {
            $validated['slug'] = Role::SUPER_ADMIN;
            $validated['status'] = true;
            $validated['permissions'] = Permission::query()->where('status', true)->pluck('id')->all();
        }

        DB::transaction(function () use ($validated, $role): void {
            $role->update([
                ...collect($validated)->except('permissions')->all(),
                'status' => (bool) ($validated['status'] ?? false),
            ]);
            $role->permissions()->sync($validated['permissions'] ?? []);
        });
        $role->refresh();
        $logger->log('update', 'roles', "Updated role {$role->name}.", $role, $oldValues, $this->snapshot($role));

        return to_route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function toggleStatus(Role $role, ActivityLogger $logger): RedirectResponse
    {
        if ($role->slug === Role::SUPER_ADMIN) {
            return back()->with('error', 'The Super Admin role must remain active.');
        }

        $oldValues = ['status' => $role->status];
        $role->update(['status' => ! $role->status]);
        $logger->statusChanged('roles', $role, $oldValues, ['status' => $role->status], "Changed status for role {$role->name}.");

        return back()->with('success', 'Role status updated successfully.');
    }

    public function destroy(Role $role, ActivityLogger $logger): RedirectResponse
    {
        if ($role->slug === Role::SUPER_ADMIN && $role->users()->exists()) {
            return back()->with('error', 'The Super Admin role cannot be deleted while it is assigned to a user.');
        }

        $oldValues = $this->snapshot($role);
        DB::transaction(function () use ($role): void {
            $role->permissions()->detach();
            $role->users()->detach();
            $role->delete();
        });
        $logger->log('delete', 'roles', "Deleted role {$role->name}.", $role, $oldValues);

        return to_route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }

    private function permissionGroups()
    {
        return Permission::query()->where('status', true)->orderBy('module')->orderBy('name')->get()
            ->groupBy(fn (Permission $permission) => $permission->module ?: 'Other');
    }

    /** @return array<string, mixed> */
    private function snapshot(Role $role): array
    {
        return [
            ...$role->only(['name', 'slug', 'description', 'status']),
            'permissions' => $role->permissions()->orderBy('slug')->pluck('slug')->all(),
        ];
    }
}
