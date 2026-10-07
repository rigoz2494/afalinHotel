<?php

namespace Database\Factories;

use App\Enums\CallbackRequestStatus;
use App\Models\CallbackRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CallbackRequest>
 */
class CallbackRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '+1 555 010 '.fake()->numerify('####'),
            'message' => fake()->optional()->sentence(),
            'wants_balcony' => fake()->boolean(),
            'status' => CallbackRequestStatus::New,
            'ip_address' => fake()->ipv4(),
        ];
    }
}
