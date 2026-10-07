<?php

namespace App\Filament\Widgets;

use App\Enums\CallbackRequestStatus;
use App\Models\CallbackRequest;
use App\Models\Currency;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    // Already Filament's own default (see Filament\Support\Concerns\CanBeLazy);
    // kept explicit so the dashboard's loading behavior is documented here,
    // not just inherited silently.
    protected static bool $isLazy = true;

    // ChartWidget/StatsOverviewWidget poll every 5s by default. A hotel's
    // lead volume has no need for near-real-time refreshes, so this is off.
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $bookingsToday = CallbackRequest::query()
            ->whereDate('created_at', today())
            ->count();

        $pending = CallbackRequest::query()
            ->where('status', CallbackRequestStatus::New)
            ->count();

        // Only the `rooms` column is needed for the revenue sum below; the
        // lead count for the description reuses this same collection
        // in-memory, so no second query is issued for it.
        $thisMonth = CallbackRequest::query()
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->get(['rooms', 'exchange_rate']);

        // Revenue is reported in the base currency. Each line stores its base price,
        // and older lines fall back to converting their stored price back by the rate.
        $revenueThisMonth = $thisMonth->sum(
            fn (CallbackRequest $request): float => collect($request->rooms ?? [])
                ->sum(fn (array $room): float => ((float) ($room['base_price'] ?? ((float) ($room['price'] ?? 0) / ($request->exchange_rate ?: 1))))
                    * (int) ($room['quantity'] ?? 1)),
        );

        return [
            Stat::make(__('Bookings today'), (string) $bookingsToday)
                ->description(__('New leads received today'))
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('info'),
            Stat::make(__('Pending requests'), (string) $pending)
                ->description(__('Awaiting a callback'))
                ->icon(Heroicon::OutlinedClock)
                ->color('warning'),
            Stat::make(__('Revenue this month'), Currency::baseSymbol().number_format($revenueThisMonth, 0))
                ->description(trans_choice('{1} :count lead this month|[2,*] :count leads this month', $thisMonth->count(), ['count' => $thisMonth->count()]))
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('success'),
        ];
    }
}
