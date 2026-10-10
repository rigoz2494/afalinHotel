<?php

namespace App\Filament\Widgets;

use App\Models\CallbackRequest;
use App\Models\HotelSetting;
use App\Models\Room;
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
        // Grouped by room_id, not the snapshotted room_name: that snapshot
        // is always English (see CallbackRequestService::priceLine()'s own
        // comment on it — a booking record isn't bilingual), so there's no
        // Russian text to "extract" from it no matter how it's read. The
        // room's own, live, bilingual name is looked up separately below.
        $quantityByRoomId = CallbackRequest::query()
            ->where('created_at', '>=', now()->subDays(self::DAYS))
            ->get(['rooms'])
            ->flatMap(fn (CallbackRequest $request): array => $request->rooms ?? [])
            ->groupBy(fn (array $room): int => (int) ($room['room_id'] ?? 0))
            ->map(fn ($rooms) => $rooms->sum(fn (array $room): int => (int) ($room['quantity'] ?? 1)))
            ->sortDesc();

        $roomNames = Room::query()
            ->whereIn('id', $quantityByRoomId->keys())
            ->get()
            ->mapWithKeys(fn (Room $room): array => [$room->id => HotelSetting::adminLabel($room->name)]);

        return [
            'datasets' => [
                [
                    'label' => __('Times requested'),
                    'data' => $quantityByRoomId->values()->all(),
                    'backgroundColor' => [
                        '#f59e0b', '#38bdf8', '#34d399', '#f472b6', '#a78bfa', '#fb923c',
                    ],
                ],
            ],
            // A room deleted since the booking was made falls back to
            // "Unknown" rather than disappearing from the chart entirely.
            'labels' => $quantityByRoomId->keys()
                ->map(fn (int $roomId): string => $roomNames[$roomId] ?? __('Unknown'))
                ->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
