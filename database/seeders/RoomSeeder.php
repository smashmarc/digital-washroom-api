<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        Location::all()->each(function (Location $location) {
            Room::factory()->count(20)->create(['location_id' => $location->id]);
        });
    }
}
