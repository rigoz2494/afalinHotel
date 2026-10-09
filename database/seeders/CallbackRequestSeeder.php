<?php

namespace Database\Seeders;

use App\Enums\CallbackRequestStatus;
use App\Models\AdditionalService;
use App\Models\CallbackRequest;
use App\Models\PricingPeriod;
use App\Models\Room;
use App\Services\CallbackRequestService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

/**
 * Mock booking requests spread across the last 30 days, so the dashboard's
 * line and pie charts show real data straight after deployment. Skips itself
 * when bookings already exist, so reseeding never adds to or overwrites real
 * guest requests.
 */
class CallbackRequestSeeder extends Seeder
{
    private const int BOOKING_COUNT = 40;

    /**
     * Weighted, so the mix looks like a real inbox: most leads are still
     * pending, a good share has been processed, and some were cancelled.
     *
     * @var array<int, CallbackRequestStatus>
     */
    private const array STATUS_POOL = [
        CallbackRequestStatus::New, CallbackRequestStatus::New, CallbackRequestStatus::New,
        CallbackRequestStatus::New, CallbackRequestStatus::New, CallbackRequestStatus::New,
        CallbackRequestStatus::New, CallbackRequestStatus::New, CallbackRequestStatus::New,
        CallbackRequestStatus::Contacted, CallbackRequestStatus::Contacted,
        CallbackRequestStatus::Contacted, CallbackRequestStatus::Contacted,
        CallbackRequestStatus::Contacted, CallbackRequestStatus::Contacted,
        CallbackRequestStatus::Contacted, CallbackRequestStatus::Cancelled,
        CallbackRequestStatus::Cancelled, CallbackRequestStatus::Cancelled,
        CallbackRequestStatus::Cancelled,
    ];

    /**
     * Genuinely Cyrillic (Faker's `ru_RU` locale has no localized text
     * provider, so `fake('ru_RU')->sentence()` would still return Latin
     * lorem-ipsum text), so CallbackRequestObserver never tries to
     * translate one and fire a real HTTP request to the translation API
     * while seeding.
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

    public function run(CallbackRequestService $callbackRequests): void
    {
        if (CallbackRequest::query()->exists()) {
            $this->command?->warn('Bookings already exist, so mock bookings were not added.');

            return;
        }

        $rooms = Room::query()->active()->ordered()->get();
        $periods = PricingPeriod::query()->active()->ordered()->get();
        $services = AdditionalService::query()->active()->ordered()->get();

        if ($rooms->isEmpty() || $periods->isEmpty()) {
            $this->command?->warn('Rooms and pricing periods must be seeded before bookings.');

            return;
        }

        for ($index = 0; $index < self::BOOKING_COUNT; $index++) {
            $createdAt = now()
                ->subDays(random_int(0, 29))
                ->setTime(random_int(8, 22), random_int(0, 59));

            // Goes through the same service a real booking does, so every
            // line's price is priced exactly the way the live site prices
            // it, instead of a second, separately maintained calculation
            // that can drift out of sync with it.
            $request = $callbackRequests->create([
                'name' => $this->randomGuestName(),
                'phone' => $this->randomPhone(),
                'message' => fake()->optional(0.6)->sentence(),
                'wants_balcony' => fake()->boolean(35),
                'special_requests' => fake()->optional(0.25)->randomElement(self::RUSSIAN_SPECIAL_REQUESTS),
                'room_number' => fake()->optional(0.3)->numerify('1##'),
                'rooms' => $this->randomRoomSelection($rooms, $periods),
                'services' => $this->randomServiceSelection($services),
            ], ipAddress: null);

            $request->forceFill([
                'status' => fake()->randomElement(self::STATUS_POOL),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ])->save();
        }
    }

    private function randomGuestName(): string
    {
        return fake()->boolean(50)
            ? fake('ru_RU')->name()
            : fake()->name();
    }

    private function randomPhone(): string
    {
        return fake()->boolean(50)
            ? '+7 9'.fake()->numerify('## ### ## ##')
            : '+1 555 '.fake()->numerify('### ####');
    }

    /**
     * Raw selection input, in the same shape a guest's booking form submits —
     * CallbackRequestService resolves each room's name, price and currency
     * from it, the same way it does for a real request.
     *
     * @param  Collection<int, Room>  $rooms
     * @param  Collection<int, PricingPeriod>  $periods
     * @return array<int, array{room_id: int, period: string, quantity: int}>
     */
    private function randomRoomSelection($rooms, $periods): array
    {
        $roomCount = fake()->randomElement([1, 1, 1, 1, 2, 2, 3]);
        $selection = [];

        for ($line = 0; $line < $roomCount; $line++) {
            $selection[] = [
                'room_id' => $rooms->random()->id,
                'period' => $periods->random()->name,
                'quantity' => random_int(1, 3),
            ];
        }

        return $selection;
    }

    /**
     * @param  Collection<int, AdditionalService>  $services
     * @return array<int, array{service_id: int, quantity: int}>
     */
    private function randomServiceSelection($services): array
    {
        if ($services->isEmpty() || ! fake()->boolean(40)) {
            return [];
        }

        return [[
            'service_id' => $services->random()->id,
            'quantity' => random_int(1, 2),
        ]];
    }
}
