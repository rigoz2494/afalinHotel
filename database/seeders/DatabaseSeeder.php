<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            HotelSettingSeeder::class,
            HeroImageSeeder::class,
            CurrencySeeder::class,
            PricingPeriodSeeder::class,
            RoomSeeder::class,
            FaqSeeder::class,
            CallbackRequestSeeder::class,
        ]);
    }
}
