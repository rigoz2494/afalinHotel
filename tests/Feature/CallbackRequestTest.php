<?php

namespace Tests\Feature;

use App\Enums\CallbackRequestStatus;
use App\Models\AdditionalService;
use App\Models\CallbackRequest;
use App\Models\PricingPeriod;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
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

    public function test_a_booking_submitted_with_only_ids_and_a_quantity_is_still_priced_correctly(): void
    {
        // No room_name, no price field at all anywhere in this payload —
        // the tightened contract the frontend now actually sends.
        $room = Room::factory()->create(['base_price' => 150, 'discount_percentage' => null]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '5550101234',
            'rooms' => [
                ['room_id' => $room->id, 'period' => null, 'quantity' => 1],
            ],
        ])->assertCreated();

        $this->assertSame(150, CallbackRequest::first()->rooms[0]['price']);
    }

    public function test_selected_services_are_priced_from_the_database_not_the_browser(): void
    {
        $service = AdditionalService::factory()->create(['price' => 500]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '5550101234',
            'services' => [
                ['service_id' => $service->id, 'quantity' => 2],
            ],
        ])->assertCreated();

        $booking = CallbackRequest::first();

        $this->assertSame(500, $booking->services[0]['price']);
        $this->assertSame(2, $booking->services[0]['quantity']);
        // Authoritative sum: nothing here was derived from client input.
        $this->assertSame(1000.0, $booking->total_price);
    }

    public function test_an_inactive_or_unknown_service_id_is_rejected(): void
    {
        $inactive = AdditionalService::factory()->create(['is_active' => false]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '5550101234',
            'services' => [
                ['service_id' => $inactive->id, 'quantity' => 1],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['services.0.service_id']);

        $this->assertDatabaseCount('callback_requests', 0);
    }

    public function test_total_price_sums_both_room_and_service_lines(): void
    {
        $room = Room::factory()->create(['base_price' => 100, 'discount_percentage' => null]);
        $service = AdditionalService::factory()->create(['price' => 50]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '5550101234',
            'rooms' => [
                ['room_id' => $room->id, 'period' => null, 'quantity' => 2],
            ],
            'services' => [
                ['service_id' => $service->id, 'quantity' => 1],
            ],
        ])->assertCreated();

        // 2 x $100 room + 1 x $50 service = $250.
        $this->assertSame(250.0, CallbackRequest::first()->total_price);
    }

    public function test_the_admin_dashboard_shows_the_customer_currency_marker(): void
    {
        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '5550101234',
            'currency' => 'USD',
        ])->assertCreated();

        $booking = CallbackRequest::first();

        $this->actingAs(User::factory()->create())
            ->get("/admin/callback-requests/{$booking->id}/edit")
            ->assertOk()
            ->assertSee('Customer Currency: USD');
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
