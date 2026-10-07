<?php

namespace Tests\Feature;

use App\Filament\Widgets\PopularRoomsChart;
use App\Models\CallbackRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PopularRoomsChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_sums_requested_quantity_per_room_within_the_last_180_days(): void
    {
        $this->travelTo(now());

        CallbackRequest::factory()->create([
            'rooms' => [
                ['room_name' => 'Deluxe Room', 'quantity' => 2],
                ['room_name' => 'Family Suite', 'quantity' => 1],
            ],
        ]);
        CallbackRequest::factory()->create([
            'rooms' => [['room_name' => 'Deluxe Room', 'quantity' => 3]],
        ]);

        // Outside the 180-day window: must not be counted.
        CallbackRequest::factory()->create([
            'rooms' => [['room_name' => 'Presidential Suite', 'quantity' => 10]],
            'created_at' => now()->subDays(181),
        ]);

        $html = Livewire::test(PopularRoomsChart::class)->html();

        preg_match('/cachedData:\s*JSON\.parse\(\'(.*?)\'\)/s', $html, $matches);
        $data = json_decode(str_replace('\\u0022', '"', $matches[1]), true);

        $this->assertSame(['Deluxe Room', 'Family Suite'], $data['labels']);
        $this->assertSame([5, 1], $data['datasets'][0]['data']);
    }
}
