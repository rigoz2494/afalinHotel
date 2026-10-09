<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomUnit;
use Database\Seeders\RoomSeeder;
use Database\Seeders\RoomUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomUnitSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_room_type_gets_at_least_two_specific_numbered_rooms(): void
    {
        $this->seed(RoomSeeder::class);
        $this->seed(RoomUnitSeeder::class);

        Room::query()->get()->each(function (Room $room): void {
            $this->assertGreaterThanOrEqual(2, $room->units()->count(), "Room {$room->slug} has fewer than 2 units.");
        });
    }

    public function test_room_numbers_are_unique_within_their_own_room_type(): void
    {
        $this->seed(RoomSeeder::class);
        $this->seed(RoomUnitSeeder::class);

        $numbers = RoomUnit::query()->pluck('number');

        $this->assertSame($numbers->count(), $numbers->unique()->count());
    }

    public function test_a_unit_with_no_photos_of_its_own_falls_back_to_its_room_types_photos(): void
    {
        $this->seed(RoomSeeder::class);
        $this->seed(RoomUnitSeeder::class);

        $unit = RoomUnit::query()->where('number', '101')->firstOrFail();

        $this->assertSame([], $unit->images);
        $this->assertNotEmpty($unit->room->images);
    }
}
