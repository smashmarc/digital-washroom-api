<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;

class LogSeeder extends Seeder
{
    public function run(): void
    {
        $roomIds = Room::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();

        $noteCodes = [
            0 => 'Room not yet cleaned.',
            1 => 'Room partially cleaned.',
            2 => 'Room fully cleaned and sanitised.',
        ];

        $start = now()->subMonths(2)->startOfDay();
        $end   = now()->subDay()->endOfDay();

        $current = $start->copy();

        while ($current->lte($end)) {
            $records = [];

            for ($i = 0; $i < 20; $i++) {
                $noteCode = fake()->randomElement([0, 0, 1, 1, 2, 2, 2]);
                $records[] = [
                    'room_id'    => fake()->randomElement($roomIds),
                    'user_id'    => fake()->randomElement($userIds),
                    'note'       => $noteCodes[$noteCode],
                    'note_code'  => $noteCode,
                    'logged_at'  => $current->copy()->setTime(
                        fake()->numberBetween(6, 22),
                        fake()->numberBetween(0, 59),
                        fake()->numberBetween(0, 59)
                    )->toDateTimeString(),
                    'created_at' => $current->toDateTimeString(),
                    'updated_at' => $current->toDateTimeString(),
                ];
            }

            DB::table('logs')->insert($records);
            $current->addDay();
        }
    }
}
