<?php

namespace Tests\Feature;

use App\Enums\CallbackRequestStatus;
use App\Filament\Widgets\BookingStatsOverview;
use App\Models\CallbackRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookingStatsOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_todays_bookings_pending_count_and_months_revenue(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));

        // Today: 2 leads, one of each status.
        CallbackRequest::factory()->create([
            'status' => CallbackRequestStatus::New,
            'rooms' => [['room_name' => 'Deluxe Room', 'price' => 100, 'quantity' => 1]],
            'created_at' => now(),
        ]);
        CallbackRequest::factory()->create([
            'status' => CallbackRequestStatus::Contacted,
            'rooms' => [['room_name' => 'Family Suite', 'price' => 50, 'quantity' => 2]],
            'created_at' => now(),
        ]);

        // Earlier this month: not "today", still counts toward the month.
        CallbackRequest::factory()->create([
            'status' => CallbackRequestStatus::New,
            'rooms' => [['room_name' => 'Standard Room', 'price' => 30, 'quantity' => 1]],
            'created_at' => now()->subDays(5),
        ]);

        // Last month: must be excluded from this month's revenue, despite
        // being "pending" too.
        CallbackRequest::factory()->create([
            'status' => CallbackRequestStatus::New,
            'rooms' => [['room_name' => 'Presidential Suite', 'price' => 999, 'quantity' => 1]],
            'created_at' => now()->subMonth(),
        ]);

        Livewire::test(BookingStatsOverview::class)
            ->assertSee('Bookings today')
            ->assertSee('2')
            ->assertSee('Pending requests')
            ->assertSee('3')
            ->assertSee('Revenue this month')
            ->assertSee('$230');
    }
}
