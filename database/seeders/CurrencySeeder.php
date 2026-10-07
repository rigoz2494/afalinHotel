<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

/**
 * Creates the default currencies once. Existing rows are left alone, so rates an
 * admin has edited are never overwritten by reseeding.
 */
class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'USD', 'symbol' => '$', 'symbol_position' => 'before', 'exchange_rate' => 1, 'is_base' => true, 'sort_order' => 1],
            ['code' => 'EUR', 'symbol' => '€', 'symbol_position' => 'before', 'exchange_rate' => 0.92, 'is_base' => false, 'sort_order' => 2],
            ['code' => 'RUB', 'symbol' => '₽', 'symbol_position' => 'after', 'exchange_rate' => 92.5, 'is_base' => false, 'sort_order' => 3],
            ['code' => 'GBP', 'symbol' => '£', 'symbol_position' => 'before', 'exchange_rate' => 0.79, 'is_base' => false, 'sort_order' => 4],
        ];

        foreach ($currencies as $currency) {
            Currency::query()->firstOrCreate(['code' => $currency['code']], $currency);
        }
    }
}
