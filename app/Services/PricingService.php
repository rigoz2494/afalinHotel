<?php

namespace App\Services;

use App\Models\PricingPeriod;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

class PricingService
{
    /**
     * The pricing table's columns are the currently active PricingPeriod rows,
     * admin-managed, in their configured display order. Each row's regular
     * price for a column is computed from the room's own base price and that
     * period's `modifier_percentage`, never entered by hand, unless a
     * RoomPrice row sets an explicit `price_override` for that cell — used
     * where the real rate doesn't fit that formula closely enough to leave
     * to it. An optional promotional discount can still stack on top of the
     * computed (not overridden) price, falling back to the room's own
     * fallback discount.
     *
     * @return array{columns: list<array{id: int, label: string}>, rows: list<array{room_id: int, room_name: array{en: string, ru: string}, prices: array<int, float|string>, base_price: float, monthly_discounts: array<int, float>}>}
     */
    public function table(): array
    {
        $periods = PricingPeriod::query()->active()->ordered()->get();

        $rooms = Room::query()->active()->ordered()
            ->with(['discountOverrides' => fn ($query) => $query->whereIn('pricing_period_id', $periods->modelKeys())])
            ->get();

        $columns = $periods
            ->map(fn (PricingPeriod $period): array => [
                'id' => $period->id,
                'label' => $period->name,
            ])
            ->values()
            ->all();

        $rows = $rooms
            ->map(fn (Room $room): array => $this->row($room, $periods))
            ->values()
            ->all();

        return ['columns' => $columns, 'rows' => $rows];
    }

    /**
     * @param  Collection<int, PricingPeriod>  $periods
     * @return array{room_id: int, room_name: array{en: string, ru: string}, prices: array<int, float|string>, base_price: float, monthly_discounts: array<int, float>}
     */
    private function row(Room $room, $periods): array
    {
        $basePrice = (float) $room->base_price;
        $overridesByPeriod = $room->discountOverrides->keyBy('pricing_period_id');

        $prices = [];
        $monthlyDiscounts = [];

        foreach ($periods as $period) {
            $override = $overridesByPeriod->get($period->id);

            if ($override?->price_override !== null) {
                // A literal, final value — e.g. "900/1300" for a child/adult
                // split rate, or just a plain number the formula can't reach
                // exactly — not something a discount percentage multiplies.
                $prices[$period->id] = is_numeric($override->price_override)
                    ? (float) $override->price_override
                    : $override->price_override;

                continue;
            }

            $prices[$period->id] = round($basePrice * (1 + $period->modifier_percentage / 100));

            $promoPercentage = $override?->discount_percentage ?? $room->discount_percentage;

            if ($promoPercentage) {
                $monthlyDiscounts[$period->id] = $promoPercentage;
            }
        }

        return [
            'room_id' => $room->id,
            'room_name' => $room->name,
            'prices' => $prices,
            'base_price' => $basePrice,
            'monthly_discounts' => $monthlyDiscounts,
        ];
    }
}
