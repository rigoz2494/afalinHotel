<?php

namespace Tests\Feature;

use App\Models\PricingPeriod;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The per-room, per-month price grid is gone: a room's regular rate for a
 * period is always `base_price * (1 + period.modifier_percentage / 100)`,
 * computed on the fly. RoomPrice only ever stores an optional, additional
 * promotional discount on top of that.
 */
class SeasonalPricingArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_prices_no_longer_has_a_price_column(): void
    {
        $this->assertFalse(Schema::hasColumn('room_prices', 'price'));
        $this->assertTrue(Schema::hasColumn('pricing_periods', 'modifier_percentage'));
    }

    public function test_a_positive_modifier_raises_every_rooms_rate_from_its_own_base_price(): void
    {
        $peak = PricingPeriod::factory()->create(['modifier_percentage' => 25]);
        $cheap = Room::factory()->create(['base_price' => 100, 'discount_percentage' => null]);
        $pricey = Room::factory()->create(['base_price' => 400, 'discount_percentage' => null]);

        $table = app(PricingService::class)->table();
        $rows = collect($table['rows'])->keyBy('room_id');

        $this->assertSame(125.0, $rows[$cheap->id]['prices'][$peak->id]);
        $this->assertSame(500.0, $rows[$pricey->id]['prices'][$peak->id]);
    }

    public function test_a_negative_modifier_lowers_the_rate_with_no_admin_entry_needed(): void
    {
        $lowSeason = PricingPeriod::factory()->create(['modifier_percentage' => -15]);
        $room = Room::factory()->create(['base_price' => 200, 'discount_percentage' => null]);

        $table = app(PricingService::class)->table();
        $row = collect($table['rows'])->firstWhere('room_id', $room->id);

        $this->assertSame(170.0, $row['prices'][$lowSeason->id]);
        $this->assertArrayNotHasKey($lowSeason->id, $row['monthly_discounts']);
    }

    public function test_a_room_specific_promo_discount_stacks_on_top_of_the_season_modifier(): void
    {
        $period = PricingPeriod::factory()->create(['modifier_percentage' => 20]);
        $room = Room::factory()->create(['base_price' => 100, 'discount_percentage' => null]);
        RoomPrice::factory()->create([
            'room_id' => $room->id,
            'pricing_period_id' => $period->id,
            'discount_percentage' => 10,
        ]);

        $table = app(PricingService::class)->table();
        $row = collect($table['rows'])->firstWhere('room_id', $room->id);

        // Regular rate is $120 (the +20% modifier); the discount is applied on top of it.
        $this->assertSame(120.0, $row['prices'][$period->id]);
        $this->assertSame(10, $row['monthly_discounts'][$period->id]);
    }

    public function test_the_rooms_fallback_discount_applies_only_when_no_promo_override_exists(): void
    {
        $withOverride = PricingPeriod::factory()->create(['modifier_percentage' => 0]);
        $withoutOverride = PricingPeriod::factory()->create(['modifier_percentage' => 0]);
        $room = Room::factory()->create(['base_price' => 100, 'discount_percentage' => 5]);
        RoomPrice::factory()->create([
            'room_id' => $room->id,
            'pricing_period_id' => $withOverride->id,
            'discount_percentage' => 15,
        ]);

        $table = app(PricingService::class)->table();
        $row = collect($table['rows'])->firstWhere('room_id', $room->id);

        $this->assertSame(15, $row['monthly_discounts'][$withOverride->id]);
        $this->assertSame(5, $row['monthly_discounts'][$withoutOverride->id]);
    }
}
