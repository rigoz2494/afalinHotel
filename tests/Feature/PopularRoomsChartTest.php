<?php

namespace Tests\Feature;

use App\Filament\Widgets\PopularRoomsChart;
use App\Models\CallbackRequest;
use App\Models\HotelSetting;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PopularRoomsChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_sums_requested_quantity_per_room_within_the_last_180_days(): void
    {
        $this->travelTo(now());

        // The widget groups by room_id (see PopularRoomsChart::getData())
        // and looks the live room up for its display name — room_name in
        // the booking's own snapshot is always English and ignored here,
        // the same way CallbackRequestService itself stores it.
        $deluxe = Room::factory()->create(['name' => ['en' => 'Deluxe Room', 'ru' => 'Делюкс']]);
        $family = Room::factory()->create(['name' => ['en' => 'Family Suite', 'ru' => 'Семейный люкс']]);
        $presidential = Room::factory()->create(['name' => ['en' => 'Presidential Suite', 'ru' => 'Президентский люкс']]);

        CallbackRequest::factory()->create([
            'rooms' => [
                ['room_id' => $deluxe->id, 'room_name' => 'Deluxe Room', 'quantity' => 2],
                ['room_id' => $family->id, 'room_name' => 'Family Suite', 'quantity' => 1],
            ],
        ]);
        CallbackRequest::factory()->create([
            'rooms' => [['room_id' => $deluxe->id, 'room_name' => 'Deluxe Room', 'quantity' => 3]],
        ]);

        // Outside the 180-day window: must not be counted.
        CallbackRequest::factory()->create([
            'rooms' => [['room_id' => $presidential->id, 'room_name' => 'Presidential Suite', 'quantity' => 10]],
            'created_at' => now()->subDays(181),
        ]);

        $html = Livewire::test(PopularRoomsChart::class)->html();

        preg_match('/cachedData:\s*JSON\.parse\(\'(.*?)\'\)/s', $html, $matches);
        $data = json_decode(str_replace('\\u0022', '"', $matches[1]), true);

        $this->assertSame(['Deluxe Room', 'Family Suite'], $data['labels']);
        $this->assertSame([5, 1], $data['datasets'][0]['data']);
    }

    public function test_labels_render_in_russian_when_the_site_is_locked_to_strict_russian_mode(): void
    {
        HotelSetting::factory()->create(['key' => 'locale_mode', 'value' => 'ru_only']);

        $deluxe = Room::factory()->create(['name' => ['en' => 'Deluxe Room', 'ru' => 'Делюкс']]);

        CallbackRequest::factory()->create([
            'rooms' => [['room_id' => $deluxe->id, 'room_name' => 'Deluxe Room', 'quantity' => 1]],
        ]);

        $html = Livewire::test(PopularRoomsChart::class)->html();

        preg_match('/cachedData:\s*JSON\.parse\(\'(.*?)\'\)/s', $html, $matches);
        $data = json_decode(str_replace('\\u0022', '"', $matches[1]), true);

        $this->assertSame(['Делюкс'], $data['labels']);
    }

    public function test_a_room_deleted_since_the_booking_falls_back_to_unknown(): void
    {
        $room = Room::factory()->create();

        CallbackRequest::factory()->create([
            'rooms' => [['room_id' => $room->id, 'room_name' => 'Deleted Room', 'quantity' => 1]],
        ]);

        $room->delete();

        $html = Livewire::test(PopularRoomsChart::class)->html();

        preg_match('/cachedData:\s*JSON\.parse\(\'(.*?)\'\)/s', $html, $matches);
        $data = json_decode(str_replace('\\u0022', '"', $matches[1]), true);

        $this->assertSame(['Unknown'], $data['labels']);
    }
}
