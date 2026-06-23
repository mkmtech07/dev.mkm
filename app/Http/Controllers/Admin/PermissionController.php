<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionRequest;
use App\Models\Permission;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $module = trim((string) $request->query('module'));
        $status = in_array($request->query('status'), ['active', 'inactive'], true) ? $request->query('status') : '';
        $permissions = Permission::query()->withCount('roles')
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->when($module !== '', fn (Builder $query) => $query->where('module', $module))
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status === 'active'))
            ->orderBy('module')->orderBy('name')->paginate(20)->withQueryString();
        $modules = Permission::query()->whereNotNull('module')->distinct()->orderBy('module')->pluck('module');

        return view('admin.permissions.index', compact('permissions', 'modules', 'search', 'module', 'status'));
    }

    public function create(): View
    {
        return view('admin.permissions.create', ['permission' => new Permission(['status' => true])]);
    }

    public function store(PermissionRequest $request, ActivityLogger $logger): RedirectResponse
    {
        $permission = Permission::create([...$request->validated(), 'status' => $request->boolean('status')]);
        $logger->log('create', 'permissions', "Created permission {$permission->slug}.", $permission, null, $permission->toArray());

        return to_route('admin.permissions.index')->with('success', 'Permission created successfully.');
    }

    public function edit(Permission $permission): View
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(PermissionRequest $request, Permission $permission, ActivityLogger $logger): RedirectResponse
    {
        $oldValues = $permission->toArray();
        $validated = $request->validated();
        if (in_array($permission->slug, Permission::CRITICAL_SLUGS, true)) {
            $validated['slug'] = $permission->slug;
            $validated['status'] = true;
        }
        $permission->update([...$validated, 'status' => (bool) ($validated['status'] ?? false)]);
        $logger->log('update', 'permissions', "Updated permission {$permission->slug}.", $permission, $oldValues, $permission->fresh()->toArray());

        return to_route('admin.permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function toggleStatus(Permission $permission, ActivityLogger $logger): RedirectResponse
    {
        if (in_array($permission->slug, Permission::CRITICAL_SLUGS, true)) {
            return back()->with('error', 'Critical access-control permissions must remain active.');
        }

        $oldValues = ['status' => $permission->status];
        $permission->update(['status' => ! $permission->status]);
        $logger->statusChanged('permissions', $permission, $oldValues, ['status' => $permission->status], "Changed status for permission {$permission->slug}.");

        return back()->with('success', 'Permission status updated successfully.');
    }

    public function destroy(Permission $permission, ActivityLogger $logger): RedirectResponse
    {
        if (in_array($permission->slug, Permission::CRITICAL_SLUGS, true)) {
            return back()->with('error', 'Critical access-control permissions cannot be deleted.');
        }

        $oldValues = $permission->toArray();
        DB::transaction(function () use ($permission): void {
            $permission->roles()->detach();
            $permission->delete();
        });
        $logger->log('delete', 'permissions', "Deleted permission {$permission->slug}.", $permission, $oldValues);

        return to_route('admin.permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
