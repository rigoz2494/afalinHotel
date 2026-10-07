<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true).' Room';

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'capacity' => fake()->numberBetween(1, 4),
            'bed_type' => fake()->randomElement(['King', 'Queen', 'Twin', 'Double']),
            'has_tv' => true,
            'has_air_conditioning' => true,
            'furniture' => ['Bedside table', 'Wardrobe', 'Armchair', 'Clothes rack'],
            'images' => [],
            'base_price' => fake()->numberBetween(80, 500),
            'discount_percentage' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
