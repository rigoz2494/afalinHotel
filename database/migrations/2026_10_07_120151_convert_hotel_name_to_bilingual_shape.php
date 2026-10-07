<?php

use App\Models\HotelSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Converts a pre-existing `hotel_name` row from a single string into the
     * new {en, ru} shape, the same way section_headings was converted. Any
     * custom name the admin already typed is preserved as the "en" value;
     * Russian is left for them, or the default, to fill in.
     */
    public function up(): void
    {
        $setting = HotelSetting::query()->where('key', 'hotel_name')->first();

        if ($setting === null) {
            return;
        }

        $setting->update([
            'value' => HotelSetting::defaultedHotelName($setting->value),
        ]);
    }

    public function down(): void
    {
        $setting = HotelSetting::query()->where('key', 'hotel_name')->first();

        if ($setting === null) {
            return;
        }

        $setting->update([
            'value' => $setting->value['en'] ?? null,
        ]);
    }
};
