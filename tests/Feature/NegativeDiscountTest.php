<?php

namespace Tests\Feature;

use App\Filament\Resources\Rooms\Pages\EditRoom;
use App\Models\PricingPeriod;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * discount_percentage used to be an unsignedTinyInteger, so it could never
 * actually store a negative value regardless of what the form allowed —
 * an admin typing -15 to mark a markup rather than a discount would have
 * either been rejected or silently truncated. It's a signed column now.
 */
class NegativeDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_room_prices_discount_percentage_accepts_a_negative_value(): void
    {
        $room = Room::factory()->create(['base_price' => 100]);
        $period = PricingPeriod::factory()->create(['modifier_percentage' => 0]);

        $roomPrice = RoomPrice::query()->create([
            'room_id' => $room->id,
            'pricing_period_id' => $period->id,
            'discount_percentage' => -15,
        ]);

        $this->assertSame(-15, $roomPrice->refresh()->discount_percentage);
    }

    public function test_a_negative_discount_acts_as_a_markup_in_the_pricing_table(): void
    {
        $room = Room::factory()->create(['base_price' => 100]);
        $period = PricingPeriod::factory()->create(['modifier_percentage' => 0]);
        RoomPrice::query()->create([
            'room_id' => $room->id,
            'pricing_period_id' => $period->id,
            'discount_percentage' => -15,
        ]);

        $response = $this->getJson(route('api.v1.pricing.index'))->assertOk();

        // The regular rate stays $100 (0% season modifier); a -15 "discount"
        // is a 15% markup, shown to the guest via monthly_discounts.
        $response->assertJsonPath('data.0.prices.'.$period->id, 100);
        $response->assertJsonPath('data.0.monthly_discounts.'.$period->id, -15);
    }

    public function test_the_room_form_accepts_a_negative_fallback_discount(): void
    {
        $room = Room::factory()->create();
        $this->actingAs(User::factory()->create());

        Livewire::test(EditRoom::class, ['record' => $room->getRouteKey()])
            ->fillForm(['discount_percentage' => -15])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(-15, $room->refresh()->discount_percentage);
    }
}
