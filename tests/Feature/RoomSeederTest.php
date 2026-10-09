<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Services\PricingService;
use Database\Seeders\PricingPeriodSeeder;
use Database\Seeders\RoomSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The seeder installs the five real room categories ("Room Types"), each
 * with a bilingual name/description and a fixed set of amenity tags — no
 * translation service involved, since this content is hand-authored in
 * both languages already. "Дополнительное место" (Extra Bed Space) is an
 * AdditionalService now, not a sixth room — see AdditionalServiceSeeder.
 */
class RoomSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_exactly_the_five_real_categories(): void
    {
        $this->seed(RoomSeeder::class);

        $this->assertSame(5, Room::query()->count());
        $this->assertSame(
            ['standard-double-room', 'twin-beds-standard-room', 'extended-triple-room', 'family-two-room-triple', 'luxury-two-room-triple'],
            Room::query()->ordered()->pluck('slug')->all(),
        );
    }

    public function test_every_room_has_a_real_bilingual_name_and_description(): void
    {
        $this->seed(RoomSeeder::class);

        $room = Room::query()->where('slug', 'standard-double-room')->firstOrFail();

        $this->assertSame('Standard Double Room', $room->name['en']);
        $this->assertSame('Стандарт однокомнатный 2-х местный', $room->name['ru']);
        $this->assertStringContainsString('Уютный однокомнатный номер', $room->description['ru']);
    }

    public function test_amenity_tags_are_parsed_from_the_real_furniture_list(): void
    {
        $this->seed(RoomSeeder::class);

        $luxury = Room::query()->where('slug', 'luxury-two-room-triple')->firstOrFail();

        $this->assertSame(
            ['double_bed', 'table', 'nightstand', 'chairs', 'armchair', 'sofa', 'wardrobe', 'hanger', 'tv', 'ac', 'fridge', 'safe_box'],
            $luxury->amenities,
        );
        // Every tag is one Room::AMENITY_TAGS knows how to render an icon for.
        $this->assertEmpty(array_diff($luxury->amenities, Room::AMENITY_TAGS));
    }

    /**
     * The real target grid from the legacy pricing sheet: each room's rate
     * across the four seasons, reproduced either by the season modifier
     * alone (Standard Double) or by an explicit price_override (every
     * other room) — see PricingPeriodSeeder's and RoomSeeder's own comments.
     */
    public function test_the_pricing_grid_matches_the_real_target_rates_exactly(): void
    {
        $this->seed(PricingPeriodSeeder::class);
        $this->seed(RoomSeeder::class);

        $table = app(PricingService::class)->table();
        $bySlug = Room::query()->ordered()->pluck('id', 'slug');

        $rowFor = fn (string $slug) => collect($table['rows'])->firstWhere('room_id', $bySlug[$slug]);
        $pricesFor = fn (string $slug) => array_values($rowFor($slug)['prices']);

        // Numeric overrides and plain modifier-computed cells alike come
        // back as numbers (int or float); assertEquals, not assertSame, so
        // e.g. 4500 and 4500.0 aren't treated as a mismatch.
        $this->assertEquals([4500, 5500, 6000, 5500], $pricesFor('standard-double-room'));
        $this->assertEquals([4700, 5700, 6200, 5700], $pricesFor('twin-beds-standard-room'));
        $this->assertEquals([5500, 6500, 7500, 6500], $pricesFor('extended-triple-room'));
        $this->assertEquals([6500, 8500, 9500, 8500], $pricesFor('family-two-room-triple'));
        $this->assertEquals([7500, 9500, 11500, 9500], $pricesFor('luxury-two-room-triple'));
    }
}
