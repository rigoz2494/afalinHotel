<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Collection;

class CurrencyService
{
    public function __construct(private HotelSettingService $settings) {}

    /**
     * The currencies a guest may choose from, base currency first — or, in
     * a single-language "Global Language Mode", just the one matching
     * currency, so there is nothing left to pick between (the frontend
     * hides the switcher entirely in that case; this is what makes the
     * restriction actually take effect, not just look applied).
     *
     * @return Collection<int, Currency>
     */
    public function selectable(): Collection
    {
        $currencies = Currency::query()->active()->ordered()->get()
            ->sortByDesc('is_base')
            ->values();

        $forcedCode = match ($this->settings->all()['locale_mode'] ?? 'multilingual') {
            'ru_only' => 'RUB',
            'en_only' => 'USD',
            default => null,
        };

        if ($forcedCode === null) {
            return $currencies;
        }

        $locked = $currencies->where('code', $forcedCode)->values();

        // Falls back to the full list if the forced currency isn't actually
        // configured — a misconfiguration shouldn't leave guests with no
        // currency to book in at all.
        return $locked->isNotEmpty() ? $locked : $currencies;
    }
}
