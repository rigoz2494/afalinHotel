<?php

namespace Tests\Feature;

use App\Filament\Resources\RoomUnits\Pages\EditRoomUnit;
use App\Http\Resources\RoomUnitResource;
use App\Models\Room;
use App\Models\RoomUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
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
        $this->assertNull($resolved['video_url']);
    }

    public function test_a_unit_with_its_own_photos_uses_them_instead_of_the_room_types(): void
    {
        $room = Room::factory()->create(['images' => ['rooms/room-type-photo.webp']]);
        $unit = RoomUnit::factory()->for($room)->create(['images' => ['rooms/own-photo.webp']]);
        $unit->setRelation('room', $room);

        $resolved = RoomUnitResource::make($unit)->resolve();

        $this->assertSame([url('storage/rooms/own-photo.webp')], $resolved['images']);
    }

    public function test_admin_can_add_and_remove_a_video_without_changing_the_photos(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $room = Room::factory()->create(['images' => ['rooms/room-type-photo.webp']]);
        $unit = RoomUnit::factory()->for($room)->create(['images' => []]);
        $video = UploadedFile::fake()->create('room.mp4', 100, 'video/mp4');

        Livewire::test(EditRoomUnit::class, ['record' => $unit->getRouteKey()])
            ->set('data.video_path', [$video])
            ->call('save')
            ->assertHasNoErrors();

        $unit->refresh()->setRelation('room', $room);
        $this->assertNotNull($unit->video_path);
        $uploadedPath = $unit->video_path;
        Storage::disk('public')->assertExists($unit->video_path);
        $resolved = RoomUnitResource::make($unit)->resolve();
        $this->assertSame([url('storage/rooms/room-type-photo.webp')], $resolved['images']);
        $this->assertSame(url(Storage::disk('public')->url($unit->video_path)), $resolved['video_url']);

        Livewire::test(EditRoomUnit::class, ['record' => $unit->getRouteKey()])
            ->set('data.video_path', [])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($unit->refresh()->video_path);
        Storage::disk('public')->assertMissing($uploadedPath);
    }
}
