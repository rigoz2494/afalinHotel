<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

/**
 * Creates the default currencies once. Existing rows are left alone, so rates an
 * admin has edited are never overwritten by reseeding.
 *
 * RUB is the base currency: every Room::base_price and RoomPrice override is
 * entered in real ruble figures (e.g. 4500 for a standard double), matching
 * the hotel's actual price grid — not USD, which would turn a 4500 room into
 * an absurd $4500/night. Every other currency's rate is "how many units of
 * it equal 1 RUB", so the pricing table and every booking convert correctly.
 */
class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'RUB', 'symbol' => '₽', 'symbol_position' => 'after', 'exchange_rate' => 1, 'is_base' => true, 'sort_order' => 1],
            ['code' => 'USD', 'symbol' => '$', 'symbol_position' => 'before', 'exchange_rate' => 0.0108, 'is_base' => false, 'sort_order' => 2],
            ['code' => 'EUR', 'symbol' => '€', 'symbol_position' => 'before', 'exchange_rate' => 0.0099, 'is_base' => false, 'sort_order' => 3],
            ['code' => 'GBP', 'symbol' => '£', 'symbol_position' => 'before', 'exchange_rate' => 0.0086, 'is_base' => false, 'sort_order' => 4],
            ['code' => 'UAH', 'symbol' => '₴', 'symbol_position' => 'after', 'exchange_rate' => 0.443, 'is_base' => false, 'sort_order' => 5],
        ];

        foreach ($currencies as $currency) {
            Currency::query()->firstOrCreate(['code' => $currency['code']], $currency);
        }
    }
}
