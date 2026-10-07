<?php

namespace Tests\Feature;

use App\Filament\Resources\CallbackRequests\Pages\ListCallbackRequests;
use App\Models\CallbackRequest;
use App\Models\PricingPeriod;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression coverage for a bug class: Filament's TextColumn calls
 * `formatStateUsing` once per element for an array-cast attribute (like the
 * {en, ru} name pair, or a booking's list of room lines) rather than once
 * with the whole value — `getStateUsing` is the fix, used throughout these
 * tables. Without it, the Rooms list page fatals outright (name is a
 * keyed array, so an element is a bare string, not the ?array the closure
 * expected) and the bookings list silently showed the wrong room count
 * (counting a single line's fields, not the booking's actual rooms).
 */
class AdminTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_rooms_list_page_renders_the_bilingual_name_without_crashing(): void
    {
        Room::factory()->create(['name' => ['en' => 'Standard Double Room', 'ru' => 'Стандарт']]);

        $this->actingAs(User::factory()->create())
            ->get('/admin/rooms')
            ->assertOk()
            ->assertSee('Standard Double Room');
    }

    public function test_the_room_prices_list_page_shows_the_rooms_bilingual_name(): void
    {
        $room = Room::factory()->create(['name' => ['en' => 'Luxury Suite', 'ru' => 'Люкс']]);
        $period = PricingPeriod::factory()->create();
        RoomPrice::factory()->for($room)->for($period, 'pricingPeriod')->create();

        $this->actingAs(User::factory()->create())
            ->get('/admin/room-prices')
            ->assertOk()
            ->assertSee('Luxury Suite');
    }

    public function test_the_bookings_list_shows_the_real_room_count_not_the_line_items_field_count(): void
    {
        $booking = CallbackRequest::factory()->create([
            'rooms' => [
                ['room_id' => 1, 'room_name' => 'A', 'period' => null, 'currency' => 'USD', 'base_price' => 100, 'price' => 100, 'quantity' => 1],
                ['room_id' => 2, 'room_name' => 'B', 'period' => null, 'currency' => 'USD', 'base_price' => 100, 'price' => 100, 'quantity' => 1],
            ],
        ]);

        $this->actingAs(User::factory()->create());

        // Each line has 7 fields — asserting the component's actual state,
        // not scraping rendered badge text, so this can't be fooled by a
        // coincidental "2" appearing anywhere else on the page.
        Livewire::test(ListCallbackRequests::class)
            ->assertOk()
            ->assertSee('2 rooms')
            ->assertDontSee('7 rooms');

        $this->assertCount(2, $booking->refresh()->rooms);
    }
}
