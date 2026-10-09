<?php

namespace App\Services;

use App\Enums\CallbackRequestStatus;
use App\Models\CallbackRequest;
use App\Models\Currency;
use App\Models\PricingPeriod;
use App\Models\Room;
use App\Models\RoomPrice;
use Illuminate\Validation\ValidationException;

class CallbackRequestService
{
    /**
     * Every price is derived from the database here, never trusted from the
     * browser. The rules match the pricing table: the discount is applied in the
     * base currency and rounded, then the result is converted and rounded again.
     *
     * @param  array{name: string, phone: string, message?: string|null, wants_balcony?: bool, special_requests?: string|null, room_number?: string|null, currency?: string|null, rooms?: array<int, array{room_id: int, period: string|null, quantity: int}>}  $data
     */
    public function create(array $data, ?string $ipAddress): CallbackRequest
    {
        $currency = $this->resolveCurrency($data['currency'] ?? null);

        return CallbackRequest::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'message' => $data['message'] ?? null,
            'wants_balcony' => $data['wants_balcony'] ?? false,
            'special_requests' => $data['special_requests'] ?? null,
            'room_number' => $data['room_number'] ?? null,
            'currency' => $currency->code,
            'exchange_rate' => $currency->exchange_rate,
            'rooms' => $this->priceRoomSelection($data['rooms'] ?? [], $currency),
            'status' => CallbackRequestStatus::New,
            'ip_address' => $ipAddress,
        ]);
    }

    private function resolveCurrency(?string $code): Currency
    {
        // Before any currency is configured, bookings are taken in USD at a rate of 1,
        // so a fresh install never rejects guests.
        if (! Currency::query()->exists()) {
            return new Currency(['code' => 'USD', 'symbol' => '$', 'exchange_rate' => 1, 'is_base' => true]);
        }

        $currency = $code === null
            ? Currency::base()
            : Currency::query()->active()->where('code', strtoupper($code))->first();

        if ($currency === null) {
            throw ValidationException::withMessages([
                'currency' => 'This currency is not available.',
            ]);
        }

        return $currency;
    }

    /**
     * @param  array<int, array{room_id: int, period: string|null, quantity: int}>  $selection
     * @return array<int, array{room_id: int, room_name: string, period: string|null, currency: string, base_price: float, price: float, quantity: int}>
     */
    private function priceRoomSelection(array $selection, Currency $currency): array
    {
        $priced = [];

        foreach ($selection as $index => $line) {
            $priced[] = $this->priceLine($index, $line, $currency);
        }

        return $priced;
    }

    /**
     * @param  array{room_id: int, period: string|null, quantity: int}  $line
     * @return array{room_id: int, room_name: string, period: string|null, currency: string, base_price: float, price: float, quantity: int}
     */
    private function priceLine(int $index, array $line, Currency $currency): array
    {
        $room = Room::query()->active()->find($line['room_id']);

        if ($room === null) {
            throw ValidationException::withMessages([
                "rooms.{$index}.room_id" => 'This room is not available for booking.',
            ]);
        }

        $period = null;
        $override = null;

        if (filled($line['period'] ?? null)) {
            $period = PricingPeriod::query()->active()->ordered()->where('name', $line['period'])->first();

            if ($period === null) {
                throw ValidationException::withMessages([
                    "rooms.{$index}.period" => 'This pricing period is not available.',
                ]);
            }

            $override = RoomPrice::query()
                ->where('room_id', $room->id)
                ->where('pricing_period_id', $period->id)
                ->first();
        }

        if ($override?->price_override !== null && ! is_numeric($override->price_override)) {
            // A literal, non-numeric display value (e.g. "900/1300" for a
            // child/adult split rate) — there's no single per-unit price to
            // book a quantity of, the same reason the pricing table shows no
            // Select button for a cell like this.
            throw ValidationException::withMessages([
                "rooms.{$index}.period" => 'This room cannot be booked for that month directly — please call us instead.',
            ]);
        }

        // The season's modifier prices every room at once; no period means the
        // plain base rate, with no promotional discount applied to it. An
        // explicit price_override — set where the real rate doesn't fit the
        // modifier formula closely enough — replaces it outright.
        $regularPrice = match (true) {
            $period === null => (float) $room->base_price,
            $override?->price_override !== null => (float) $override->price_override,
            default => round($room->base_price * (1 + $period->modifier_percentage / 100)),
        };

        // A room-specific promo override wins; otherwise the room's fallback discount applies.
        // Doesn't stack with a price_override, which is already the final rate.
        $discount = $period === null || $override?->price_override !== null
            ? null
            : ($override?->discount_percentage ?? $room->discount_percentage);

        $basePrice = $discount ? round($regularPrice * (1 - $discount / 100)) : $regularPrice;

        return [
            'room_id' => $room->id,
            // A plain-string snapshot for the admin dashboard (always
            // English, since the admin panel itself runs in English) —
            // room names are bilingual, but a booking record isn't.
            'room_name' => $room->name['en'] ?? $room->name['ru'] ?? '',
            'period' => $period?->name,
            'currency' => $currency->code,
            'base_price' => (float) $basePrice,
            'price' => (float) round($basePrice * $currency->exchange_rate),
            'quantity' => (int) $line['quantity'],
        ];
    }
}
