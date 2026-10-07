<?php

namespace App\Filament\Widgets;

use App\Models\CallbackRequest;
use Filament\Widgets\ChartWidget;

class BookingsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = true;

    protected ?string $pollingInterval = null;

    public function getHeading(): ?string
    {
        return __('Bookings (last 30 days)');
    }

    protected function getData(): array
    {
        $start = today()->subDays(29);

        // Let the database count and group rows by day instead of pulling
        // every lead from the last 30 days into memory just to tally them.
        $countsByDate = CallbackRequest::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as aggregate')
            ->groupBy('date')
            ->pluck('aggregate', 'date');

        $labels = [];
        $counts = [];

        for ($date = $start->copy(); $date->lte(today()); $date = $date->addDay()) {
            $labels[] = $date->locale(app()->getLocale())->isoFormat('MMM D');
            $counts[] = (int) ($countsByDate[$date->format('Y-m-d')] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => __('Leads received'),
                    'data' => $counts,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
