<?php

namespace Database\Seeders;

use App\Models\HotelSetting;
use Illuminate\Database\Seeder;

class HotelSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Hero slides are managed by HeroImageSeeder, which stores local files.
        $settings = [
            'hotel_name' => HotelSetting::DEFAULT_HOTEL_NAME,
            'tagline' => [
                'en' => 'A seaside stay in Feodosia',
                'ru' => 'Отдых у моря в Феодосии',
            ],
            'section_headings' => HotelSetting::DEFAULT_SECTION_HEADINGS,
        ];

        if (app()->environment('local')) {
            $settings['promo_banner'] = [
                'en' => 'Special Offer: Book now and get a 5% discount on early bird reservations!',
                'ru' => 'Специальное предложение: забронируйте сейчас и получите скидку 5% при раннем бронировании!',
            ];
            $settings['contacts'] = [
                'phone' => '+1 555 010 0100',
                'email' => 'reservations@afalina.example',
                'address' => '1 Seaside Avenue, Riviera',
            ];
        }

        foreach ($settings as $key => $value) {
            HotelSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
