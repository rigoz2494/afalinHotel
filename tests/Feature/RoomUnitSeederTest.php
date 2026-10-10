<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomUnit;
use Database\Seeders\RoomSeeder;
use Database\Seeders\RoomUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * RoomUnitSeeder reads the hotel's own real photos from
 * public/seedImgs/hotel_imgs, so every test here fakes the public disk —
 * the actual WebP conversion pipeline (ImageOptimizer) is exercised for
 * real, but nothing is written to the real storage directory during a
 * test run.
 */
class RoomUnitSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_room_type_with_a_real_photo_folder_gets_its_specific_numbered_rooms(): void
    {
        Storage::fake('public');

        $this->seed(RoomSeeder::class);
        $this->seed(RoomUnitSeeder::class);

        Room::query()->get()->each(function (Room $room): void {
            $this->assertGreaterThanOrEqual(1, $room->units()->count(), "Room {$room->slug} has no units.");
        });
    }

    public function test_room_numbers_are_unique_within_their_own_room_type(): void
    {
        Storage::fake('public');

        $this->seed(RoomSeeder::class);
        $this->seed(RoomUnitSeeder::class);

        $numbers = RoomUnit::query()->pluck('number');

        $this->assertSame($numbers->count(), $numbers->unique()->count());
    }

    public function test_each_units_photos_are_converted_to_webp_and_stored_on_the_public_disk(): void
    {
        Storage::fake('public');

        $this->seed(RoomSeeder::class);
        $this->seed(RoomUnitSeeder::class);

        $unit = RoomUnit::query()->whereNotNull('images')->first();

        $this->assertNotEmpty($unit->images);

        foreach ($unit->images as $path) {
            $this->assertStringEndsWith('.webp', $path);
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_a_rooms_showcase_photo_is_never_one_of_its_units_bathroom_photos(): void
    {
        Storage::fake('public');

        $this->seed(RoomSeeder::class);
        $this->seed(RoomUnitSeeder::class);

        Room::query()->get()->each(function (Room $room): void {
            $this->assertNotEmpty($room->images, "Room {$room->slug} has no showcase photo.");
            $this->assertStringNotContainsStringIgnoringCase('bath', $room->images[0]);
        });
    }
}
