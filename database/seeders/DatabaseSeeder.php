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
            GalleryImageSeeder::class,
            AdditionalServiceSeeder::class,
        ]);

        if (app()->environment(['local', 'testing'])) {
            $this->call([FaqSeeder::class, CallbackRequestSeeder::class]);
        }
    }
}
