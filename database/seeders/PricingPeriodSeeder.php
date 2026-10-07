<?php

namespace Database\Seeders;

use App\Models\PricingPeriod;
use Illuminate\Database\Seeder;

class PricingPeriodSeeder extends Seeder
{
    public function run(): void
    {
        // A realistic seasonal curve: a quiet month, two regular months, then a
        // peak month, each priced from the room's own base price automatically.
        $periods = [
            ['name' => 'Month 1', 'modifier_percentage' => -10],
            ['name' => 'Month 2', 'modifier_percentage' => 0],
            ['name' => 'Month 3', 'modifier_percentage' => 10],
            ['name' => 'Month 4', 'modifier_percentage' => 25],
        ];

        foreach ($periods as $index => $period) {
            PricingPeriod::updateOrCreate(['name' => $period['name']], [
                'modifier_percentage' => $period['modifier_percentage'],
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }
}
