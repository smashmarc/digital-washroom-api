<?php

namespace Database\Seeders;

use App\Constants\Role as RoleConstant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => RoleConstant::ADMINISTRATOR, 'guard_name' => 'api']
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@washroom.test'],
            [
                'name'     => 'Admin User',
                'username' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        $admin->assignRole(RoleConstant::ADMINISTRATOR);
    }
}
