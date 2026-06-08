<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $locationIds = Location::pluck('id')->toArray();
        $roles = Role::pluck('name')->toArray();

        $admin = User::firstOrCreate(
            ['email' => 'admin@washroom.test'],
            [
                'name'        => 'Admin User',
                'username'    => 'admin',
                'password'    => Hash::make('password'),
                'location_id' => $locationIds[0],
            ]
        );
        $admin->assignRole('admin');

        for ($i = 0; $i < 19; $i++) {
            $user = User::factory()->create([
                'location_id' => fake()->randomElement($locationIds),
                'username'    => fake()->unique()->userName(),
            ]);
            $user->assignRole(fake()->randomElement($roles));
        }
    }
}
