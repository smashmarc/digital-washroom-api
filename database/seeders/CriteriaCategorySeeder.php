<?php

namespace Database\Seeders;

use App\Models\CriteriaCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CriteriaCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Cleanliness',         'description' => 'General cleanliness of surfaces, floors, and fixtures.'],
            ['name' => 'Hygiene & Sanitation', 'description' => 'Soap, hand drying, and sanitation supply availability.'],
            ['name' => 'Maintenance',          'description' => 'Physical condition of fixtures, tiles, and fittings.'],
            ['name' => 'Safety',               'description' => 'Slip hazards, emergency access, and safety compliance.'],
            ['name' => 'Equipment & Supplies', 'description' => 'Operational status of dispensers, lighting, and ventilation.'],
            ['name' => 'Odor Control',         'description' => 'Air quality and deodorisation effectiveness.'],
        ];

        foreach ($categories as $data) {
            CriteriaCategory::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name'        => $data['name'],
                    'description' => $data['description'],
                    'is_active'   => true,
                ]
            );
        }
    }
}
