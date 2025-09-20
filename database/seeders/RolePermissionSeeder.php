<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $editorRole = Role::create(['name' => 'editor']);

        // Create permissions
        $createPost = Permission::create(['name' => 'create post']);
        $editPost   = Permission::create(['name' => 'edit post']);

        // Assign permissions to roles
        $adminRole->givePermissionTo([$createPost, $editPost]);
        $editorRole->givePermissionTo($createPost);

        // Assign role to user
        $user = \App\Models\User::find(1);
        $user->assignRole('admin');
    }
}
