<?php

namespace Database\Factories;

use App\Models\PricingPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PricingPeriod>
 */
class PricingPeriodFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->monthName(),
            'modifier_percentage' => 0,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
