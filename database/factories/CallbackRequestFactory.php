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
     * Faker's `ru_RU` locale has no localized text provider, so
     * `fake('ru_RU')->sentence()` still returns Latin lorem-ipsum text —
     * these are hand-picked instead, genuinely Cyrillic, so
     * CallbackRequestObserver never tries to translate one and fire a real
     * HTTP request to the translation API outside of a test that
     * explicitly fakes it.
     *
     * @var array<int, string>
     */
    private const array RUSSIAN_SPECIAL_REQUESTS = [
        'Тихая сторона, пожалуйста.',
        'Нужны отдельные одеяла.',
        'Ранний заезд, если возможно.',
        'Можно номер подальше от лифта?',
        'Пожалуйста, детскую кроватку в номер.',
    ];

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
            'special_requests' => fake()->optional(0.3)->randomElement(self::RUSSIAN_SPECIAL_REQUESTS),
            'room_number' => fake()->optional(0.4)->numerify('1##'),
            'status' => CallbackRequestStatus::New,
            'ip_address' => fake()->ipv4(),
        ];
    }
}
