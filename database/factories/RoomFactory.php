<?php

namespace Database\Factories;

use App\Models\Room;
use App\Services\TransliterationService;
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
            'name' => ['en' => Str::title($name), 'ru' => Str::title($name)],
            'slug' => app(TransliterationService::class)->slug($name),
            'description' => ['en' => fake()->paragraph(), 'ru' => fake()->paragraph()],
            'capacity' => fake()->numberBetween(1, 4),
            'bed_type' => fake()->randomElement([
                ['en' => 'King', 'ru' => 'Кровать king-size'],
                ['en' => 'Queen', 'ru' => 'Кровать queen-size'],
                ['en' => 'Twin', 'ru' => 'Раздельные кровати'],
                ['en' => 'Double', 'ru' => 'Двуспальная кровать'],
            ]),
            'amenities' => ['double_bed', 'wardrobe', 'tv', 'ac'],
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
