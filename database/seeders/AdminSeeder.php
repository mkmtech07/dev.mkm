<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'superadmin@billsoft.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('strongpassword')]
        );

        $this->call(RolePermissionSeeder::class);
        $superAdminRole = Role::query()->where('slug', Role::SUPER_ADMIN)->first();
        if ($superAdminRole) {
            $user->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }
    }
}
