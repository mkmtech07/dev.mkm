<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function hasRole(Role|string $role): bool
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('role_user')) {
            return false;
        }

        $slug = $role instanceof Role ? $role->slug : $role;

        return $this->roles()
            ->where('roles.status', true)
            ->where('roles.slug', $slug)
            ->exists();
    }

    public function hasPermission(Permission|string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('permission_role')) {
            return false;
        }

        $slug = $permission instanceof Permission ? $permission->slug : $permission;

        return $this->roles()
            ->where('roles.status', true)
            ->whereHas('permissions', fn ($query) => $query
                ->where('permissions.status', true)
                ->where('permissions.slug', $slug))
            ->exists();
    }

    /** @param array<int, string> $permissions */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        if (! $this->exists) {
            return false;
        }

        // The original account is a permanent lockout-safe super administrator.
        if ((int) static::query()->min('id') === (int) $this->getKey()) {
            return true;
        }

        return $this->hasRole(Role::SUPER_ADMIN);
    }
}
