<?php

namespace Tests\Feature;

use App\Enums\CallbackRequestStatus;
use App\Models\CallbackRequest;
use App\Models\PricingPeriod;
use App\Models\Room;
use App\Models\RoomPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CallbackRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_request_a_callback(): void
    {
        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane Doe',
            'phone' => '+1 555 010 1234',
        ])->assertCreated()->assertJsonStructure(['message']);

        $this->assertDatabaseHas('callback_requests', [
            'name' => 'Jane Doe',
            'status' => CallbackRequestStatus::New->value,
        ]);
    }

    public function test_message_is_optional_and_stored_when_given(): void
    {
        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '5550101234',
            'message' => 'Late check-in please',
        ])->assertCreated();

        $this->assertSame('Late check-in please', CallbackRequest::first()->message);
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->postJson(route('api.v1.callback-requests.store'), ['name' => '', 'phone' => 'abc'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'phone']);

        $this->assertDatabaseCount('callback_requests', 0);
    }

    public function test_selected_rooms_are_priced_by_the_server_not_the_browser(): void
    {
        $deluxe = Room::factory()->create(['name' => ['en' => 'Deluxe Room', 'ru' => 'Делюкс'], 'base_price' => 200, 'discount_percentage' => null]);
        // +50% season modifier makes the regular rate $300, then a 10% promo makes it $270.
        $period = PricingPeriod::factory()->create(['name' => 'Month 1', 'modifier_percentage' => 50]);
        RoomPrice::factory()->create([
            'room_id' => $deluxe->id,
            'pricing_period_id' => $period->id,
            'discount_percentage' => 10,
        ]);
        $suite = Room::factory()->create(['name' => ['en' => 'Family Suite', 'ru' => 'Семейный люкс'], 'base_price' => 210]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '5550101234',
            'rooms' => [
                ['room_id' => $deluxe->id, 'room_name' => 'Tampered name', 'period' => 'Month 1', 'price' => 1, 'quantity' => 2],
                ['room_id' => $suite->id, 'room_name' => 'Family Suite', 'period' => null, 'price' => 0, 'quantity' => 1],
            ],
        ])->assertCreated();

        $this->assertEquals([
            ['room_id' => $deluxe->id, 'room_name' => 'Deluxe Room', 'period' => 'Month 1', 'currency' => 'USD', 'base_price' => 270, 'price' => 270, 'quantity' => 2],
            ['room_id' => $suite->id, 'room_name' => 'Family Suite', 'period' => null, 'currency' => 'USD', 'base_price' => 210, 'price' => 210, 'quantity' => 1],
        ], CallbackRequest::first()->rooms);
    }

    public function test_a_promo_discount_still_applies_on_top_of_a_numeric_price_override(): void
    {
        $room = Room::factory()->create(['base_price' => 100, 'discount_percentage' => null]);
        $period = PricingPeriod::factory()->create(['name' => 'Month 1', 'modifier_percentage' => 0]);
        RoomPrice::factory()->create([
            'room_id' => $room->id,
            'pricing_period_id' => $period->id,
            'price_override' => '5700',
            'discount_percentage' => 10,
        ]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '5550101234',
            'rooms' => [
                ['room_id' => $room->id, 'room_name' => 'Ignored', 'period' => 'Month 1', 'price' => 1, 'quantity' => 1],
            ],
        ])->assertCreated();

        // The override ($5700) is the regular rate; the 10% promo applies on top of it.
        $this->assertSame(5130, CallbackRequest::first()->rooms[0]['price']);
    }

    public function test_invalid_room_entry_is_rejected(): void
    {
        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '5550101234',
            'rooms' => [['room_id' => 'not-a-number', 'room_name' => 'Deluxe', 'price' => 100, 'quantity' => 0]],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rooms.0.room_id', 'rooms.0.quantity']);

        $this->assertDatabaseCount('callback_requests', 0);
    }

    public function test_endpoint_is_rate_limited(): void
    {
        $payload = ['name' => 'Jane', 'phone' => '5550101234'];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('api.v1.callback-requests.store'), $payload)->assertCreated();
        }

        $this->postJson(route('api.v1.callback-requests.store'), $payload)->assertTooManyRequests();
    }
}
