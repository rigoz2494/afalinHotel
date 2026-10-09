<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomUnit>
 */
class RoomUnitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'number' => (string) fake()->unique()->numberBetween(100, 999),
            'images' => [],
            'amenities' => [],
            'has_balcony' => false,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withBalcony(): static
    {
        return $this->state(['has_balcony' => true]);
    }
}
