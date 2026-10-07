<?php

namespace App\Filament\Widgets;

use App\Models\CallbackRequest;
use Filament\Widgets\ChartWidget;

class PopularRoomsChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = true;

    protected ?string $pollingInterval = null;

    /**
     * How many days of leads to look at. `rooms` is a JSON column, and
     * summing its nested values can't be done portably as a single SQL
     * aggregate across SQLite/MySQL, so this still loads matching rows
     * into memory. Bounding the window keeps that load from growing
     * forever as leads accumulate, and `get(['rooms'])` avoids pulling
     * every other column along with it.
     */
    private const DAYS = 180;

    public function getHeading(): ?string
    {
        return __('Most Requested Rooms');
    }

    protected function getData(): array
    {
        $quantityByRoom = CallbackRequest::query()
            ->where('created_at', '>=', now()->subDays(self::DAYS))
            ->get(['rooms'])
            ->flatMap(fn (CallbackRequest $request): array => $request->rooms ?? [])
            ->groupBy(fn (array $room): string => $room['room_name'] ?? __('Unknown'))
            ->map(fn ($rooms) => $rooms->sum(fn (array $room): int => (int) ($room['quantity'] ?? 1)))
            ->sortDesc();

        return [
            'datasets' => [
                [
                    'label' => __('Times requested'),
                    'data' => $quantityByRoom->values()->all(),
                    'backgroundColor' => [
                        '#f59e0b', '#38bdf8', '#34d399', '#f472b6', '#a78bfa', '#fb923c',
                    ],
                ],
            ],
            'labels' => $quantityByRoom->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
