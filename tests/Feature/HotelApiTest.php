<?php

namespace Tests\Feature;

use App\Models\HotelSetting;
use App\Models\PricingPeriod;
use App\Models\Room;
use App\Models\RoomPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_hotel_endpoint_returns_settings(): void
    {
        HotelSetting::factory()->create(['key' => 'tagline', 'value' => 'Welcome']);

        $this->getJson(route('api.v1.hotel.show'))
            ->assertOk()
            ->assertJsonPath('data.tagline', 'Welcome')
            ->assertJsonPath('data.promo_banner', null);
    }

    public function test_rooms_endpoint_returns_only_active_rooms_in_order_with_amenities(): void
    {
        Room::factory()->create(['name' => 'Second', 'sort_order' => 2]);
        Room::factory()->create(['name' => 'First', 'sort_order' => 1, 'bed_type' => 'King']);
        Room::factory()->inactive()->create();

        $this->getJson(route('api.v1.rooms.index'))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'First')
            ->assertJsonPath('data.0.amenities.bed_type', 'King')
            ->assertJsonStructure(['data' => [['id', 'slug', 'name', 'description', 'images', 'base_price', 'amenities' => ['capacity', 'bed_type', 'furniture', 'has_tv', 'has_air_conditioning']]]]);
    }

    public function test_pricing_endpoint_returns_dynamic_columns_and_rows(): void
    {
        // June's +10% modifier makes the regular rate $110, July's +20% makes it $120.
        $june = PricingPeriod::factory()->create(['name' => 'June', 'sort_order' => 1, 'modifier_percentage' => 10]);
        $july = PricingPeriod::factory()->create(['name' => 'July', 'sort_order' => 2, 'modifier_percentage' => 20]);
        $room = Room::factory()->create(['name' => 'Deluxe', 'base_price' => 100]);
        RoomPrice::factory()->for($room)->for($june, 'pricingPeriod')->create(['discount_percentage' => 5]);

        $this->getJson(route('api.v1.pricing.index'))
            ->assertOk()
            ->assertJsonPath('columns', [
                ['id' => $june->id, 'label' => 'June'],
                ['id' => $july->id, 'label' => 'July'],
            ])
            ->assertJsonPath('data.0.room_name', 'Deluxe')
            ->assertJsonPath('data.0.prices', [(string) $june->id => 110, (string) $july->id => 120])
            ->assertJsonPath('data.0.base_price', 100)
            ->assertJsonPath('data.0.monthly_discounts', [(string) $june->id => 5]);
    }

    public function test_pricing_endpoint_keeps_discounts_correct_after_a_period_is_renamed(): void
    {
        $period = PricingPeriod::factory()->create(['name' => 'Month 1', 'sort_order' => 1]);
        $room = Room::factory()->create(['name' => 'Deluxe']);
        RoomPrice::factory()->for($room)->for($period, 'pricingPeriod')->create(['discount_percentage' => 5]);

        // Renaming the period (as an admin would in Filament) must not
        // detach the discount from it, since matching is by the period's
        // id, not by its label text.
        $period->update(['name' => 'January']);

        $this->getJson(route('api.v1.pricing.index'))
            ->assertOk()
            ->assertJsonPath('columns.0.label', 'January')
            ->assertJsonPath('data.0.monthly_discounts', [(string) $period->id => 5]);
    }

    public function test_pricing_endpoint_excludes_inactive_periods_from_the_table(): void
    {
        $active = PricingPeriod::factory()->create(['name' => 'Active Month', 'sort_order' => 1]);
        $inactive = PricingPeriod::factory()->inactive()->create(['name' => 'Retired Month', 'sort_order' => 2]);
        $room = Room::factory()->create();
        RoomPrice::factory()->for($room)->for($active, 'pricingPeriod')->create();
        RoomPrice::factory()->for($room)->for($inactive, 'pricingPeriod')->create();

        $response = $this->getJson(route('api.v1.pricing.index'))->assertOk();

        $response->assertJsonPath('columns.0.id', $active->id);
        $this->assertCount(1, $response->json('columns'));
        $this->assertArrayNotHasKey((string) $inactive->id, $response->json('data.0.prices'));
    }

    public function test_pricing_endpoint_computes_a_price_for_every_room_and_period_with_no_admin_entry_needed(): void
    {
        // No RoomPrice row at all: the regular rate is still computed from the
        // room's base price and the period's modifier, with no manual entry.
        $period = PricingPeriod::factory()->create(['modifier_percentage' => 15]);
        Room::factory()->create(['base_price' => 100]);

        $response = $this->getJson(route('api.v1.pricing.index'))->assertOk();

        $response->assertJsonPath('data.0.prices.'.$period->id, 115);
    }
}
