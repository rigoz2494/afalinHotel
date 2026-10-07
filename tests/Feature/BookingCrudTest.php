<?php

namespace Tests\Feature;

use App\Models\CallbackRequest;
use App\Models\PricingPeriod;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-to-end coverage for a booking: a guest submits a multi-room lead
 * through the public API, and an admin can then open it in the Filament
 * "Bookings" resource (the CallbackRequest model/admin resource).
 */
class BookingCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_a_multi_room_booking(): void
    {
        $deluxe = Room::factory()->create(['name' => ['en' => 'Deluxe Room', 'ru' => 'Делюкс'], 'base_price' => 100, 'discount_percentage' => null]);
        $suite = Room::factory()->create(['name' => ['en' => 'Family Suite', 'ru' => 'Семейный люкс'], 'base_price' => 210, 'discount_percentage' => null]);
        // 26% season modifier turns Deluxe's $100 base into $126 for Month 1.
        PricingPeriod::factory()->create(['name' => 'Month 1', 'sort_order' => 1, 'modifier_percentage' => 26]);
        PricingPeriod::factory()->create(['name' => 'Month 2', 'sort_order' => 2]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane Doe',
            'phone' => '+1 555 010 1234',
            'message' => 'Celebrating an anniversary.',
            'rooms' => [
                ['room_id' => $deluxe->id, 'room_name' => 'Deluxe Room', 'period' => 'Month 1', 'currency' => 'USD', 'base_price' => 126, 'price' => 126, 'quantity' => 2],
                ['room_id' => $suite->id, 'room_name' => 'Family Suite', 'period' => 'Month 2', 'currency' => 'USD', 'base_price' => 210, 'price' => 210, 'quantity' => 1],
            ],
        ])->assertCreated();

        $booking = CallbackRequest::first();

        $this->assertNotNull($booking);
        $this->assertSame('Jane Doe', $booking->name);
        $this->assertEquals([
            ['room_id' => $deluxe->id, 'room_name' => 'Deluxe Room', 'period' => 'Month 1', 'currency' => 'USD', 'base_price' => 126, 'price' => 126, 'quantity' => 2],
            ['room_id' => $suite->id, 'room_name' => 'Family Suite', 'period' => 'Month 2', 'currency' => 'USD', 'base_price' => 210, 'price' => 210, 'quantity' => 1],
        ], $booking->rooms);
    }

    public function test_admin_can_view_a_booking_in_the_filament_resource(): void
    {
        $admin = User::factory()->create();

        $booking = CallbackRequest::factory()->create([
            'name' => 'Jane Doe',
            'rooms' => [
                ['room_id' => 1, 'room_name' => 'Deluxe Room', 'period' => 'Month 1', 'price' => 126, 'quantity' => 2],
            ],
        ]);

        $response = $this->actingAs($admin)->get("/admin/callback-requests/{$booking->id}/edit");

        $response->assertOk();
        $response->assertSee('Jane Doe');
        $response->assertSee('Deluxe Room');
    }

    public function test_admin_can_list_bookings_in_the_filament_resource(): void
    {
        $admin = User::factory()->create();

        CallbackRequest::factory()->create(['name' => 'Jane Doe']);

        $this->actingAs($admin)
            ->get('/admin/callback-requests')
            ->assertOk()
            ->assertSee('Jane Doe');
    }

    public function test_guest_cannot_access_the_admin_booking_resource(): void
    {
        $booking = CallbackRequest::factory()->create();

        $this->get("/admin/callback-requests/{$booking->id}/edit")
            ->assertRedirect('/admin/login');
    }
}
