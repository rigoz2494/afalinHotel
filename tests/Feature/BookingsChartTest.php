<?php

namespace Tests\Feature;

use App\Filament\Widgets\BookingsChart;
use App\Models\CallbackRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookingsChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_groups_lead_counts_by_day_within_the_last_30_days_only(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));

        // On the first day of the 30-day window: 2 leads.
        $windowStart = today()->subDays(29);
        CallbackRequest::factory()->count(2)->create(['created_at' => $windowStart->copy()->setTime(9, 0)]);

        // Mid-window: 1 lead.
        CallbackRequest::factory()->create(['created_at' => today()->subDays(5)->setTime(9, 0)]);

        // Just outside the window: must not be counted.
        CallbackRequest::factory()->create(['created_at' => $windowStart->copy()->subDay()]);

        $data = $this->renderedChartData(BookingsChart::class);

        $this->assertCount(30, $data['labels']);
        $this->assertSame(3, array_sum($data['datasets'][0]['data']));
    }

    /**
     * @return array<string, mixed>
     */
    private function renderedChartData(string $widget): array
    {
        $html = Livewire::test($widget)->html();

        preg_match('/cachedData:\s*JSON\.parse\(\'(.*?)\'\)/s', $html, $matches);

        return json_decode(str_replace('\\u0022', '"', $matches[1]), true);
    }
}
