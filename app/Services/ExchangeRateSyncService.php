<?php

namespace App\Services;

use App\Exceptions\ExchangeRateSyncException;
use App\Models\Currency;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Refreshes exchange rates against the active base currency. This only ever
 * runs when a Filament action calls it directly: there is no scheduled job or
 * queue listener behind it, by design.
 */
class ExchangeRateSyncService
{
    private const string ENDPOINT = 'https://open.er-api.com/v6/latest/';

    /**
     * Fetches live rates for whichever currency is currently flagged as the
     * base, then updates every other active currency's rate relative to it.
     * The base currency's own rate is left at 1 (enforced by the model).
     *
     * @return array{base: string, updated: int}
     */
    public function syncFromBase(): array
    {
        $base = Currency::base();

        if ($base === null) {
            throw new ExchangeRateSyncException('No base currency is configured yet.');
        }

        $rates = $this->fetchRates($base->code);

        $others = Currency::query()->active()->where('id', '!=', $base->id)->get();
        $updated = 0;

        foreach ($others as $currency) {
            if (! array_key_exists($currency->code, $rates)) {
                continue;
            }

            $currency->update(['exchange_rate' => round((float) $rates[$currency->code], 4)]);
            $updated++;
        }

        return ['base' => $base->code, 'updated' => $updated];
    }

    /**
     * @return array<string, float>
     */
    private function fetchRates(string $baseCode): array
    {
        try {
            $response = Http::timeout(10)->get(self::ENDPOINT.$baseCode);
        } catch (Throwable $exception) {
            throw new ExchangeRateSyncException('Could not reach the exchange rate service.', previous: $exception);
        }

        if ($response->failed()) {
            throw new ExchangeRateSyncException('The exchange rate service returned an error.');
        }

        $body = $response->json();

        if (($body['result'] ?? null) !== 'success' || ! is_array($body['rates'] ?? null)) {
            throw new ExchangeRateSyncException("The exchange rate service does not support {$baseCode}.");
        }

        return $body['rates'];
    }
}
