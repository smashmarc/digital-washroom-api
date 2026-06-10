<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'location_id' => Location::inRandomOrder()->first()->id,
            'name'        => fake()->randomElement(['Restroom', 'Bathroom', 'Washroom']) . ' ' . fake()->bothify('##?'),
            'qr_code'     => Room::generateQrCode(),
        ];
    }
}
