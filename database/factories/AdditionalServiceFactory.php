<?php

namespace Database\Factories;

use App\Models\AdditionalService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdditionalService>
 */
class AdditionalServiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ['en' => ucfirst($name), 'ru' => ucfirst($name)],
            'price' => fake()->randomFloat(2, 10, 500),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
