<?php

namespace Database\Seeders;

use App\Models\Criteria;
use App\Models\CriteriaCategory;
use Illuminate\Database\Seeder;

class CriteriaSeeder extends Seeder
{
    private array $criteria = [
        'Cleanliness' => [
            'Floors are clean, dry, and free of debris.',
            'Walls and partitions are free of stains and graffiti.',
            'Mirrors are clean and streak-free.',
            'Toilets and urinals are clean inside and out.',
            'Sinks and countertops are clean and dry.',
            'Waste bins are emptied and liners are in place.',
        ],
        'Hygiene & Sanitation' => [
            'Liquid soap dispensers are stocked and functional.',
            'Paper towel dispensers or hand dryers are operational.',
            'Toilet paper is adequately stocked in all cubicles.',
            'Feminine hygiene disposal units are emptied and sanitised.',
            'Hand sanitiser stations are stocked and functional.',
        ],
        'Maintenance' => [
            'All fixtures (taps, flushers, locks) are in working order.',
            'No leaking pipes, taps, or cisterns are present.',
            'Tiles and grouting are intact with no cracks or damage.',
            'Cubicle doors open, close, and lock correctly.',
            'Drainage is clear with no standing water.',
        ],
        'Safety' => [
            'No slip hazards are present on floors.',
            'Wet floor signage is deployed when surfaces are damp.',
            'Emergency exit signage is visible and unobstructed.',
            'No sharp edges or broken fixtures pose an injury risk.',
        ],
        'Equipment & Supplies' => [
            'All lighting is functional with no blown bulbs.',
            'Exhaust fans and ventilation are operational.',
            'Cleaning equipment is stored appropriately out of sight.',
            'QR code placard is present and undamaged.',
        ],
        'Odor Control' => [
            'No unpleasant odours are detectable upon entry.',
            'Air freshener units are stocked and operational.',
            'Drains are free from malodour.',
        ],
    ];

    public function run(): void
    {
        foreach ($this->criteria as $categoryName => $items) {
            $category = CriteriaCategory::where('name', $categoryName)->first();
            if (!$category) {
                continue;
            }

            foreach ($items as $text) {
                Criteria::firstOrCreate(
                    ['criteria_category_id' => $category->id, 'text' => $text],
                    ['is_active' => true]
                );
            }
        }
    }
}
