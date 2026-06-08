<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            LocationSeeder::class,
            UserSeeder::class,
            RoomSeeder::class,
            CriteriaCategorySeeder::class,
            CriteriaSeeder::class,
            EvaluationTemplateSeeder::class,
            LogSeeder::class,
            EvaluationSeeder::class,
        ]);
    }
}
