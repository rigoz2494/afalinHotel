<?php

namespace Tests\Feature;

use App\Enums\CallbackRequestStatus;
use App\Models\CallbackRequest;
use App\Models\PricingPeriod;
use App\Models\Room;
use Database\Seeders\CallbackRequestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CallbackRequestSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_forty_bookings_spread_over_the_last_thirty_days(): void
    {
        Room::factory()->count(3)->create();
        PricingPeriod::factory()->count(2)->create();

        $this->seed(CallbackRequestSeeder::class);

        $bookings = CallbackRequest::query()->get();

        $this->assertCount(40, $bookings);
        $this->assertTrue($bookings->every(fn (CallbackRequest $booking): bool => $booking->created_at->gte(now()->subDays(30)->startOfDay())));
        $this->assertGreaterThan(1, $bookings->pluck('created_at')->map->toDateString()->unique()->count());
    }

    public function test_seeded_bookings_cover_every_status_and_multi_room_selections(): void
    {
        Room::factory()->count(3)->create();
        PricingPeriod::factory()->count(2)->create();

        $this->seed(CallbackRequestSeeder::class);

        $statuses = CallbackRequest::query()->pluck('status')->map(fn (CallbackRequestStatus $status) => $status->value)->unique();

        $this->assertEqualsCanonicalizing(['new', 'contacted', 'cancelled'], $statuses->values()->all());
        $this->assertTrue(CallbackRequest::query()->get()->contains(fn (CallbackRequest $booking): bool => count($booking->rooms) > 1));
    }

    public function test_seeder_does_not_duplicate_bookings_when_run_again(): void
    {
        Room::factory()->create();
        PricingPeriod::factory()->create();

        $this->seed(CallbackRequestSeeder::class);
        $this->seed(CallbackRequestSeeder::class);

        $this->assertSame(40, CallbackRequest::query()->count());
    }
}
