<?php

use App\Models\HotelSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Converts a pre-existing `section_headings` row from the old, single
     * English string per section into the new {section: {en, ru}} shape, so
     * the database itself is canonical rather than relying on a runtime
     * fallback indefinitely. Any custom English text the admin already
     * typed is preserved as the "en" value; Russian is left for them, or the
     * default, to fill in.
     */
    public function up(): void
    {
        $setting = HotelSetting::query()->where('key', 'section_headings')->first();

        if ($setting === null) {
            return;
        }

        $setting->update([
            'value' => HotelSetting::defaultedSectionHeadings((array) $setting->value),
        ]);
    }

    public function down(): void
    {
        $setting = HotelSetting::query()->where('key', 'section_headings')->first();

        if ($setting === null) {
            return;
        }

        $setting->update([
            'value' => collect($setting->value)
                ->map(fn (array $locales): ?string => $locales['en'] ?? null)
                ->filter()
                ->all(),
        ]);
    }
};
