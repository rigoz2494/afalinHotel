<?php

namespace Database\Factories;

use App\Models\HotelSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HotelSetting>
 */
class HotelSettingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'value' => fake()->sentence(),
        ];
    }
}
