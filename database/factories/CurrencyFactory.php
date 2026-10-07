<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->currencyCode(),
            'symbol' => fake()->randomElement(['$', '€', '₽', '£']),
            'exchange_rate' => fake()->randomFloat(4, 0.5, 100),
            'symbol_position' => 'before',
            'is_base' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function base(): static
    {
        return $this->state(fn (): array => [
            'code' => 'USD',
            'symbol' => '$',
            'exchange_rate' => 1,
            'is_base' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
