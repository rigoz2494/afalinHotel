<?php

namespace Database\Seeders;

use App\Models\PricingPeriod;
use Illuminate\Database\Seeder;

class PricingPeriodSeeder extends Seeder
{
    public function run(): void
    {
        // The four real seasons. Each period's modifier is calibrated to the
        // Standard Double room (the most common category) — its own rate for
        // every season comes out exact from this formula alone. The other
        // five rooms don't share a single clean percentage curve with it (a
        // real hotel's seasonal pricing rarely does), so RoomSeeder gives
        // them an explicit RoomPrice::price_override for the seasons where
        // the formula alone wouldn't land on their real rate.
        $periods = [
            ['name' => '01.05–10.06', 'modifier_percentage' => 0],
            ['name' => '11.06–30.06', 'modifier_percentage' => 22.22],
            ['name' => '01.07–31.08', 'modifier_percentage' => 33.33],
            ['name' => '01.09–30.09', 'modifier_percentage' => 22.22],
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
