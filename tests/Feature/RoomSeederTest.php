<?php

namespace Tests\Feature;

use App\Models\Room;
use Database\Seeders\RoomSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The seeder auto-translates each room's English amenity tags into Russian,
 * so a fresh install never shows amenity chips in English only to a guest
 * browsing the site in Russian.
 */
class RoomSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_seeded_rooms_amenities_are_translated_into_russian(): void
    {
        Http::fake(['api.mymemory.translated.net/*' => Http::response([
            'responseData' => ['translatedText' => 'Переведённый тег'],
        ])]);

        $this->seed(RoomSeeder::class);

        $rooms = Room::query()->get();

        $this->assertCount(4, $rooms);
        $this->assertTrue($rooms->every(function (Room $room): bool {
            return filled($room->furniture_ru) && count($room->furniture_ru) === count($room->furniture);
        }));
        $this->assertSame('Переведённый тег', $rooms->first()->furniture_ru[0]);
    }

    public function test_the_same_amenity_label_is_only_translated_once(): void
    {
        // "Wardrobe" and "Bedside table" repeat across several mock rooms;
        // each distinct label should only ever reach the translation service once.
        Http::fake(['api.mymemory.translated.net/*' => Http::response([
            'responseData' => ['translatedText' => 'Шкаф'],
        ])]);

        $this->seed(RoomSeeder::class);

        Http::assertSentCount(
            Room::query()->get()->pluck('furniture')->flatten()->unique()->count(),
        );
    }
}
