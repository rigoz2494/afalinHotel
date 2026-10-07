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
            'hotel_name' => 'Grand Meridian',
            'tagline' => 'Where every stay becomes a story.',
            'promo_banner' => 'Special Offer: Book now and get a 5% discount on early bird reservations!',
            'contacts' => [
                'phone' => '+1 555 010 0100',
                'email' => 'reservations@grandmeridian.example',
                'address' => '1 Seaside Avenue, Riviera',
            ],
            'section_headings' => HotelSetting::DEFAULT_SECTION_HEADINGS,
        ];

        foreach ($settings as $key => $value) {
            HotelSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
