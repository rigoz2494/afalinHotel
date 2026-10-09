<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            HotelSettingSeeder::class,
            HeroImageSeeder::class,
            CurrencySeeder::class,
            PricingPeriodSeeder::class,
            RoomSeeder::class,
            RoomUnitSeeder::class,
            AdditionalServiceSeeder::class,
            FaqSeeder::class,
            CallbackRequestSeeder::class,
        ]);
    }
}
