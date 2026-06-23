<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    public const CRITICAL_SLUGS = [
        'dashboard.view',
        'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
        'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
        'users.roles.manage',
    ];

    protected $fillable = ['name', 'slug', 'module', 'description', 'status'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }
}
