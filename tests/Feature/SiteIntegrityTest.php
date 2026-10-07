<?php

namespace Tests\Feature;

use App\Models\CallbackRequest;
use App\Models\Currency;
use App\Models\Faq;
use App\Models\PricingPeriod;
use App\Models\Room;
use App\Models\RoomPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * End-to-end checks of the public customer flow: read the months and promo
 * rates, pick rooms, submit a booking, and confirm the stored booking matches
 * the price the customer was shown.
 */
class SiteIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_customer_sees_only_active_months_with_their_promo_rates(): void
    {
        $january = PricingPeriod::factory()->create(['name' => 'January', 'sort_order' => 1]);
        PricingPeriod::factory()->inactive()->create(['name' => 'February', 'sort_order' => 2]);
        $march = PricingPeriod::factory()->create(['name' => 'March', 'sort_order' => 3]);

        $room = Room::factory()->create(['is_active' => true, 'base_price' => 200, 'discount_percentage' => 5]);
        // January's -10% modifier makes the regular rate $180, then a 10% promo wins
        // over the room's 5% fallback. March's +10% makes it $220, with no promo of
        // its own, so the room's 5% fallback discount applies instead.
        $january->update(['modifier_percentage' => -10]);
        $march->update(['modifier_percentage' => 10]);
        RoomPrice::factory()->create(['room_id' => $room->id, 'pricing_period_id' => $january->id, 'discount_percentage' => 10]);

        $response = $this->getJson(route('api.v1.pricing.index'))
            ->assertOk()
            ->assertJsonPath('columns.0.label', 'January')
            ->assertJsonPath('columns.1.label', 'March');

        $this->assertCount(2, $response->json('columns'));
        $response->assertJsonPath('data.0.monthly_discounts', [
            (string) $january->id => 10,
            (string) $march->id => 5,
        ]);
    }

    public function test_booking_stores_the_price_the_customer_was_shown_even_when_the_browser_tampers(): void
    {
        // -25% season modifier makes the regular rate $150, then a 20% promo makes it $120.
        $period = PricingPeriod::factory()->create(['name' => 'June', 'sort_order' => 1, 'modifier_percentage' => -25]);
        $room = Room::factory()->create(['is_active' => true, 'base_price' => 200, 'discount_percentage' => null]);
        RoomPrice::factory()->create(['room_id' => $room->id, 'pricing_period_id' => $period->id, 'discount_percentage' => 20]);

        $pricing = $this->getJson(route('api.v1.pricing.index'))->assertOk();
        $shownPrice = (int) round(150 * (1 - 20 / 100));
        $pricing->assertJsonPath('data.0.prices.'.$period->id, 150);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane Doe',
            'phone' => '+7 915 000 00 00',
            'rooms' => [
                ['room_id' => $room->id, 'room_name' => 'Anything', 'period' => 'June', 'price' => 1, 'quantity' => 3],
            ],
        ])->assertCreated();

        $stored = CallbackRequest::query()->firstOrFail()->rooms;

        $this->assertSame($shownPrice, (int) $stored[0]['price']);
        $this->assertSame($room->name['en'], $stored[0]['room_name']);
        $this->assertSame(3, $stored[0]['quantity']);
    }

    public function test_multi_room_booking_is_stored_line_by_line(): void
    {
        // +20% season modifier turns Deluxe's $100 base into $120; no promo needed.
        $period = PricingPeriod::factory()->create(['name' => 'Summer', 'sort_order' => 1, 'modifier_percentage' => 20]);
        $deluxe = Room::factory()->create(['is_active' => true, 'name' => ['en' => 'Deluxe', 'ru' => 'Делюкс'], 'base_price' => 100]);
        $suite = Room::factory()->create(['is_active' => true, 'name' => ['en' => 'Suite', 'ru' => 'Люкс'], 'base_price' => 300]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Group Booking',
            'phone' => '5550101234',
            'message' => 'Two rooms, arriving late',
            'rooms' => [
                ['room_id' => $deluxe->id, 'room_name' => 'Deluxe', 'period' => 'Summer', 'price' => 120, 'quantity' => 2],
                ['room_id' => $suite->id, 'room_name' => 'Suite', 'period' => null, 'price' => 300, 'quantity' => 1],
            ],
        ])->assertCreated();

        $booking = CallbackRequest::query()->firstOrFail();

        $this->assertCount(2, $booking->rooms);
        $this->assertEquals([120, 300], array_column($booking->rooms, 'price'));
        $this->assertSame([2, 1], array_column($booking->rooms, 'quantity'));
    }

    public function test_unknown_or_inactive_rooms_and_periods_are_rejected(): void
    {
        $inactiveRoom = Room::factory()->create(['is_active' => false]);
        $activeRoom = Room::factory()->create(['is_active' => true]);
        PricingPeriod::factory()->create(['name' => 'Spring', 'sort_order' => 1]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane', 'phone' => '5550101234',
            'rooms' => [['room_id' => 999999, 'room_name' => 'Ghost', 'period' => null, 'price' => 1, 'quantity' => 1]],
        ])->assertUnprocessable()->assertJsonValidationErrors(['rooms.0.room_id']);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane', 'phone' => '5550101234',
            'rooms' => [['room_id' => $inactiveRoom->id, 'room_name' => 'Hidden', 'period' => null, 'price' => 1, 'quantity' => 1]],
        ])->assertUnprocessable()->assertJsonValidationErrors(['rooms.0.room_id']);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane', 'phone' => '5550101234',
            'rooms' => [['room_id' => $activeRoom->id, 'room_name' => 'Room', 'period' => 'Nonexistent', 'price' => 1, 'quantity' => 1]],
        ])->assertUnprocessable()->assertJsonValidationErrors(['rooms.0.period']);

        $this->assertDatabaseCount('callback_requests', 0);
    }

    public function test_empty_and_malformed_payloads_are_rejected_without_crashing(): void
    {
        $this->postJson(route('api.v1.callback-requests.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'phone']);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane', 'phone' => '5550101234', 'rooms' => 'not-an-array',
        ])->assertUnprocessable()->assertJsonValidationErrors(['rooms']);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane', 'phone' => '5550101234',
            'rooms' => array_fill(0, 21, ['room_id' => 1, 'room_name' => 'x', 'price' => 1, 'quantity' => 1]),
        ])->assertUnprocessable()->assertJsonValidationErrors(['rooms']);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane', 'phone' => '5550101234', 'message' => str_repeat('a', 2001),
        ])->assertUnprocessable()->assertJsonValidationErrors(['message']);

        $this->assertDatabaseCount('callback_requests', 0);
    }

    public function test_script_payloads_are_stored_as_plain_text_and_do_not_break_the_endpoint(): void
    {
        $payload = '<script>alert("x")</script>';

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => $payload,
            'phone' => '5550101234',
            'message' => "'; DROP TABLE callback_requests; --",
        ])->assertCreated();

        $booking = CallbackRequest::query()->firstOrFail();

        $this->assertSame($payload, $booking->name);
        $this->assertDatabaseCount('callback_requests', 1);
    }

    public function test_public_read_endpoints_respond(): void
    {
        Room::factory()->create(['is_active' => true]);

        $this->getJson(route('api.v1.hotel.show'))->assertOk();
        $this->getJson(route('api.v1.rooms.index'))->assertOk();
        $this->getJson(route('api.v1.pricing.index'))->assertOk();
    }

    public function test_admin_pages_redirect_guests_to_login(): void
    {
        foreach (['/admin/rooms', '/admin/room-prices', '/admin/pricing-periods', '/admin/callback-requests', '/admin/faqs', '/admin/currencies', '/admin/manage-hotel-settings'] as $path) {
            $this->get($path)->assertRedirect('/admin/login');
        }
    }

    public function test_the_multi_currency_pricing_endpoint_returns_ok_with_the_exchange_list(): void
    {
        Currency::factory()->base()->create();
        Currency::factory()->create(['code' => 'EUR']);
        Room::factory()->create(['is_active' => true]);

        $this->getJson(route('api.v1.pricing.index'))
            ->assertOk()
            ->assertJsonCount(2, 'currencies');
    }

    public function test_the_homepage_serves_the_faqs_guests_see_including_any_russian_translation(): void
    {
        Faq::factory()->create([
            'question' => 'Is parking available?',
            'answer' => 'Yes, free of charge.',
            'question_ru' => 'Есть ли парковка?',
            'answer_ru' => 'Да, бесплатно.',
            'is_active' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('faqs.data.0.question', 'Is parking available?')
                ->where('faqs.data.0.question_ru', 'Есть ли парковка?'));
    }

    public function test_the_booking_endpoint_returns_created_for_a_valid_request(): void
    {
        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Smoke Test',
            'phone' => '+1 555 010 9999',
        ])->assertCreated();
    }
}
