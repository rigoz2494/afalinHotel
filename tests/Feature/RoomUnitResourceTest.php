<?php

namespace Tests\Feature;

use App\Http\Resources\RoomUnitResource;
use App\Models\Room;
use App\Models\RoomUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomUnitResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_unit_with_no_photos_of_its_own_falls_back_to_its_room_types_photos(): void
    {
        $room = Room::factory()->create(['images' => ['rooms/room-type-photo.webp']]);
        $unit = RoomUnit::factory()->for($room)->create(['images' => []]);
        $unit->setRelation('room', $room);

        $resolved = RoomUnitResource::make($unit)->resolve();

        $this->assertSame([url('storage/rooms/room-type-photo.webp')], $resolved['images']);
    }

    public function test_a_unit_with_its_own_photos_uses_them_instead_of_the_room_types(): void
    {
        $room = Room::factory()->create(['images' => ['rooms/room-type-photo.webp']]);
        $unit = RoomUnit::factory()->for($room)->create(['images' => ['rooms/own-photo.webp']]);
        $unit->setRelation('room', $room);

        $resolved = RoomUnitResource::make($unit)->resolve();

        $this->assertSame([url('storage/rooms/own-photo.webp')], $resolved['images']);
    }
}
