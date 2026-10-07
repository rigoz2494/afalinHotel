<?php

namespace Database\Factories;

use App\Models\PricingPeriod;
use App\Models\Room;
use App\Models\RoomPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomPrice>
 */
class RoomPriceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'pricing_period_id' => PricingPeriod::factory(),
            'discount_percentage' => null,
        ];
    }
}
