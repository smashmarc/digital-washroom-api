<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Constants\PermissionConstant;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionConstant::all() as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'api'],
                ['description' => 'can '.ucfirst(str_replace(['.', '-'], ' ', $permission))],              
            );
        }
    }
}
