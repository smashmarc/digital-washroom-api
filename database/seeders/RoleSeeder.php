<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin',
            'manager',
            'supervisor',
            'inspector',
            'staff',
            'cleaner',
            'auditor',
            'viewer',
            'coordinator',
            'technician',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'api']);
        }
    }
}
