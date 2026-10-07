<?php

use App\Models\HotelSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Converts a pre-existing `promo_banner` row from a single string into
     * the {en, ru} shape, the same way hotel_name was converted. Unlike
     * hotel_name, there's no sensible default to fall back to here — an
     * admin who never set a promo just keeps seeing no banner at all.
     */
    public function up(): void
    {
        $setting = HotelSetting::query()->where('key', 'promo_banner')->first();

        if ($setting === null || ! is_string($setting->value) || $setting->value === '') {
            return;
        }

        $setting->update(['value' => ['en' => $setting->value, 'ru' => null]]);
    }

    public function down(): void
    {
        $setting = HotelSetting::query()->where('key', 'promo_banner')->first();

        if ($setting === null || ! is_array($setting->value)) {
            return;
        }

        $setting->update(['value' => $setting->value['en'] ?? null]);
    }
};
