<?php

namespace App\Models;

use Database\Factories\HotelSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelSetting extends Model
{
    /** @use HasFactory<HotelSettingFactory> */
    use HasFactory;

    protected $fillable = ['key', 'value'];

    /**
     * Fallback heading for each landing-page section, in each supported
     * language, used whenever the admin hasn't set that section/locale
     * combination yet.
     *
     * @var array<string, array<string, string>>
     */
    public const array DEFAULT_SECTION_HEADINGS = [
        'rooms' => ['en' => 'Our Rooms', 'ru' => 'Наши номера'],
        'pricing' => ['en' => 'Seasonal Rates & Special Offers', 'ru' => 'Сезонные тарифы и специальные предложения'],
        'about' => ['en' => 'A story of hospitality by the water', 'ru' => 'История гостеприимства у воды'],
        'faq' => ['en' => 'Frequently Asked Questions', 'ru' => 'Часто задаваемые вопросы'],
        'contact' => ['en' => 'Request a Callback', 'ru' => 'Заказать обратный звонок'],
    ];

    /**
     * The hotel's own official name, in each supported language, used
     * whenever the admin hasn't set that locale yet — including on a fresh
     * install, so the site never ships with a generic placeholder name.
     *
     * @var array<string, string>
     */
    public const array DEFAULT_HOTEL_NAME = ['en' => 'Afalina Hotel', 'ru' => 'Отель Афалина'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    /**
     * Fills in any section/locale the admin hasn't set yet with the default,
     * so a landing-page section or the nav never renders a blank title. Both
     * languages are always returned, in full: the guest's browser picks which
     * one to show, instantly, with no round trip to the server.
     *
     * @param  array<string, mixed>  $stored
     * @return array<string, array<string, string>>
     */
    public static function defaultedSectionHeadings(array $stored): array
    {
        $headings = self::DEFAULT_SECTION_HEADINGS;

        foreach ($stored as $section => $value) {
            // Pre-translation data was a single, English-only string per
            // section; treat it as the "en" value rather than discarding it.
            if (is_string($value) && $value !== '') {
                $headings[$section]['en'] = $value;

                continue;
            }

            if (! is_array($value)) {
                continue;
            }

            foreach (array_filter($value) as $locale => $localizedValue) {
                $headings[$section][$locale] = $localizedValue;
            }
        }

        return $headings;
    }

    /**
     * Fills in whichever language the admin hasn't set yet with the default
     * brand name, the same way {@see defaultedSectionHeadings()} does for
     * section titles. Both languages are always returned, in full.
     *
     * @return array<string, string>
     */
    public static function defaultedHotelName(mixed $stored): array
    {
        $name = self::DEFAULT_HOTEL_NAME;

        // Pre-translation data was a single, English-only string; treat it
        // as the "en" value rather than discarding it.
        if (is_string($stored) && $stored !== '') {
            $name['en'] = $stored;

            return $name;
        }

        if (! is_array($stored)) {
            return $name;
        }

        foreach (array_filter($stored) as $locale => $localizedValue) {
            $name[$locale] = $localizedValue;
        }

        return $name;
    }
}
