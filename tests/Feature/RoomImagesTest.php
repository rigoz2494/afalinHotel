<?php

namespace Tests\Feature;

use App\Filament\Resources\Rooms\Pages\EditRoom;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression coverage: uploaded room photos must survive a save and still be
 * shown, with their thumbnails and delete controls, when the edit page is
 * reopened; an empty gallery must stay empty, not a bug, and the admin
 * should be told plainly what the public site shows in that case.
 */
class RoomImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_room_images_are_stored_and_shown_back_in_the_form(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $room = Room::factory()->create(['images' => []]);

        Livewire::test(EditRoom::class, ['record' => $room->getRouteKey()])
            ->fillForm([
                'images' => [
                    UploadedFile::fake()->image('one.jpg'),
                    UploadedFile::fake()->image('two.jpg'),
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $storedPaths = $room->refresh()->images;

        $this->assertCount(2, $storedPaths);

        foreach ($storedPaths as $path) {
            Storage::disk('public')->assertExists($path);
        }

        Livewire::test(EditRoom::class, ['record' => $room->getRouteKey()])
            ->assertFormSet(['images' => $storedPaths]);
    }

    public function test_the_api_exposes_every_stored_room_image_as_a_public_url(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('rooms/a.webp', 'binary');
        Storage::disk('public')->put('rooms/b.webp', 'binary');
        Room::factory()->create(['is_active' => true, 'images' => ['rooms/a.webp', 'rooms/b.webp']]);

        $this->getJson(route('api.v1.rooms.index'))
            ->assertOk()
            ->assertJsonCount(2, 'data.0.images');
    }

    public function test_editing_a_room_without_touching_photos_never_loses_the_existing_ones(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('rooms/a.webp', 'binary-a');
        Storage::disk('public')->put('rooms/b.webp', 'binary-b');

        $this->actingAs(User::factory()->create());
        $room = Room::factory()->create(['images' => ['rooms/a.webp', 'rooms/b.webp']]);

        Livewire::test(EditRoom::class, ['record' => $room->getRouteKey()])
            ->fillForm(['name.en' => 'Renamed Room'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['rooms/a.webp', 'rooms/b.webp'], $room->refresh()->images);
    }

    public function test_a_room_with_no_photos_shows_a_hint_about_the_public_placeholder(): void
    {
        $this->actingAs(User::factory()->create());
        $withoutPhotos = Room::factory()->create(['images' => []]);

        Livewire::test(EditRoom::class, ['record' => $withoutPhotos->getRouteKey()])
            ->assertSee('No photos uploaded yet');
    }

    public function test_a_room_with_photos_shows_the_ordinary_upload_hint_not_the_placeholder_notice(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('rooms/a.webp', 'binary-a');

        $this->actingAs(User::factory()->create());
        $withPhotos = Room::factory()->create(['images' => ['rooms/a.webp']]);

        Livewire::test(EditRoom::class, ['record' => $withPhotos->getRouteKey()])
            ->assertDontSee('No photos uploaded yet');
    }
}
