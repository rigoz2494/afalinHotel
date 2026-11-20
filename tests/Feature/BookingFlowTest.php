<?php

namespace Tests\Feature;

use App\Filament\Resources\CallbackRequests\CallbackRequestResource;
use App\Models\AdditionalService;
use App\Models\CallbackRequest;
use App\Models\Currency;
use App\Models\PricingPeriod;
use App\Models\Room;
use App\Models\RoomUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_seasonal_room_and_services_reach_the_admin_with_the_correct_total(): void
    {
        Queue::fake();
        Currency::factory()->base()->create(['code' => 'RUB', 'symbol' => '₽']);

        $room = Room::factory()->create([
            'name' => ['en' => 'Standard room', 'ru' => 'Стандарт'],
            'base_price' => 4000,
            'discount_percentage' => 10,
        ]);
        $unit = RoomUnit::factory()->for($room)->create(['number' => '101']);
        $period = PricingPeriod::factory()->create(['name' => '01.07–31.08', 'modifier_percentage' => 50]);
        $service = AdditionalService::factory()->create(['price' => 300]);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Landing')
            ->where('rooms.data.0.units.0.number', $unit->number)
            ->where('pricing.columns.0.label', $period->name)
            ->where("pricing.rows.data.0.prices.{$period->id}", 6000)
            ->where("pricing.rows.data.0.monthly_discounts.{$period->id}", 10)
            ->where('additionalServices.data.0.price', 300));

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Test guest',
            'phone' => '+1 202 555 0142',
            'currency' => 'RUB',
            'room_number' => $unit->number,
            'rooms' => [['room_id' => $room->id, 'period' => $period->name, 'quantity' => 1]],
            'services' => [['service_id' => $service->id, 'quantity' => 2]],
        ])->assertCreated();

        $booking = CallbackRequest::query()->sole();
        $this->assertSame('101', $booking->room_number);
        $this->assertSame('RUB', $booking->currency);
        $this->assertEquals(5400, $booking->rooms[0]['price']);
        $this->assertEquals($service->id, $booking->services[0]['service_id']);
        $this->assertSame(2, $booking->services[0]['quantity']);
        $this->assertSame(6000.0, $booking->total_price);

        $this->actingAs(User::factory()->create())
            ->get(CallbackRequestResource::getUrl('edit', ['record' => $booking]))
            ->assertOk()
            ->assertSee('Test guest')
            ->assertSee('₽6,000.00');
    }
}
