<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRoleRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserRoleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $users = User::query()->with(['roles' => fn ($query) => $query->orderBy('name')])
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.user-roles.index', compact('users', 'search'));
    }

    public function edit(User $user): View
    {
        return view('admin.user-roles.edit', [
            'managedUser' => $user,
            'roles' => Role::query()->where('status', true)->orderBy('name')->get(),
            'selectedRoles' => $user->roles()->pluck('roles.id')->all(),
        ]);
    }

    public function update(UserRoleRequest $request, User $user, ActivityLogger $logger): RedirectResponse
    {
        $oldRoles = $user->roles()->orderBy('slug')->pluck('slug')->all();
        $roleIds = $request->validated('roles', []);

        if ($user->isSuperAdmin() && (int) User::query()->min('id') === (int) $user->id) {
            $superAdminRole = Role::query()->where('slug', Role::SUPER_ADMIN)->where('status', true)->first();
            if ($superAdminRole) {
                $roleIds[] = $superAdminRole->id;
            }
        }

        $user->roles()->sync(array_values(array_unique(array_map('intval', $roleIds))));
        $newRoles = $user->roles()->orderBy('slug')->pluck('slug')->all();
        $logger->log('update', 'user_roles', "Updated roles for {$user->email}.", $user, ['roles' => $oldRoles], ['roles' => $newRoles]);

        return to_route('admin.user-roles.index')->with('success', 'User roles updated successfully.');
    }
}
