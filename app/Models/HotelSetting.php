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
        'location' => ['en' => 'Our Location', 'ru' => 'Наше расположение'],
        'reviews' => ['en' => 'What Our Guests Say', 'ru' => 'Отзывы наших гостей'],
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
    public const array DEFAULT_HOTEL_NAME = ['en' => 'Afalina', 'ru' => 'Афалина'];

    /**
     * Fallback for each About Us statistics badge (label + value, each
     * bilingual), used wherever the admin hasn't set that badge yet —
     * including on a fresh install, so the section never renders blank.
     *
     * @var array<string, array<string, array<string, string>>>
     */
    public const array DEFAULT_ABOUT_STATS = [
        'years' => ['label' => ['en' => 'Years', 'ru' => 'Лет'], 'value' => ['en' => '20+', 'ru' => '20+']],
        'rooms' => ['label' => ['en' => 'Rooms', 'ru' => 'Номеров'], 'value' => ['en' => '48', 'ru' => '48']],
        'guests' => ['label' => ['en' => 'Guests', 'ru' => 'Гостей'], 'value' => ['en' => '30k', 'ru' => '30k']],
    ];

    /**
     * The About Us section's own story text, in each supported language —
     * two paragraphs, separated by a blank line (see `localized()` on the
     * frontend, which splits on that to render each as its own <p>).
     *
     * @var array<string, string>
     */
    public const array DEFAULT_ABOUT_STORY = [
        'en' => "Afalina opened its doors with a simple idea: every guest should feel like a welcome friend. For over two decades our team has combined warm, attentive service with calm, thoughtfully designed spaces.\n\nFrom the sunlit lobby to the poolside terrace and our seasonal restaurant, every corner of the grounds is made for slowing down and enjoying your stay.",
        'ru' => "Афалина открыла свои двери с простой идеей: каждый гость должен чувствовать себя как дома. Уже более двадцати лет наша команда сочетает тёплый, внимательный сервис со спокойными, продуманными пространствами.\n\nОт светлого лобби до террасы у бассейна и сезонного ресторана — каждый уголок создан для того, чтобы вы могли отдохнуть и насладиться пребыванием.",
    ];

    /**
     * The playful, purely decorative status phrases the preloader cycles
     * through while the page loads — never a real progress report.
     *
     * @var array<int, array<string, string>>
     */
    public const array DEFAULT_PRELOADER_PHRASES = [
        ['en' => 'Feeding the resort dolphins…', 'ru' => 'Кормим дельфинов…'],
        ['en' => 'Ironing the crisp white linens…', 'ru' => 'Гладим белоснежные простыни…'],
        ['en' => 'Polishing welcome crystal glasses…', 'ru' => 'Натираем бокалы до идеального блеска…'],
        ['en' => 'Chilling the complimentary champagne…', 'ru' => 'Охлаждаем шампанское для вашего заезда…'],
        ['en' => 'Hiding beautiful seashells along the shore…', 'ru' => 'Прячем самые красивые ракушки на пляже…'],
    ];

    /**
     * The "Global Language Mode" set in Site Settings — 'multilingual'
     * (default), 'ru_only' or 'en_only'. Read directly from the key/value
     * store rather than through HotelSettingService, so it's usable from
     * anywhere (Filament resources, services) without constructing that
     * service just for this one setting.
     */
    public static function currentLocaleMode(): string
    {
        return static::query()->where('key', 'locale_mode')->first()?->value ?? 'multilingual';
    }

    /**
     * 'ru' or 'en' when a single-language mode is active, null when the
     * site is multilingual — the one check every Filament form and nav
     * item needs to decide whether to show both languages or just one.
     */
    public static function restrictedLocale(): ?string
    {
        return match (static::currentLocaleMode()) {
            'ru_only' => 'ru',
            'en_only' => 'en',
            default => null,
        };
    }

    /**
     * Picks the right side of a {en, ru} pair to show inside the admin
     * panel itself — table columns, select options, anything other than a
     * guest-facing page. Prefers the active single-language mode's locale;
     * in multilingual mode, falls back to the app's own configured locale
     * instead of always defaulting to English, which is what previously
     * made every one of these confusing for a Russian-only admin.
     *
     * Untyped, not `?array`: every caller passes an Eloquent attribute
     * cast to 'array' (Room::$name, GalleryImage::$alt, ...), but none of
     * these models declare that cast via an `@property` tag, so static
     * analysis only ever sees it as `mixed`.
     */
    public static function adminLabel(mixed $bilingual): string
    {
        if (! is_array($bilingual)) {
            return '—';
        }

        $preferred = static::restrictedLocale() ?? app()->getLocale();
        $other = $preferred === 'ru' ? 'en' : 'ru';

        return $bilingual[$preferred] ?? $bilingual[$other] ?? '—';
    }

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

    /**
     * Fills in whichever language the admin hasn't set yet with the default
     * story text, the same way {@see defaultedHotelName()} does for the
     * brand name. Both languages are always returned, in full.
     *
     * @return array<string, string>
     */
    public static function defaultedAboutStory(mixed $stored): array
    {
        $story = self::DEFAULT_ABOUT_STORY;

        if (! is_array($stored)) {
            return $story;
        }

        foreach (array_filter($stored) as $locale => $localizedValue) {
            $story[$locale] = $localizedValue;
        }

        return $story;
    }

    /**
     * The preloader's playful status phrases are a plain list the admin
     * can freely add to, edit or remove from — unlike the fixed-key
     * defaults above, there's nothing to merge per item. Falls back to the
     * default set only when nothing usable was ever saved, since an empty
     * list would leave the preloader with no text to show at all.
     *
     * @return array<int, array<string, string>>
     */
    public static function defaultedPreloaderPhrases(mixed $stored): array
    {
        if (! is_array($stored)) {
            return self::DEFAULT_PRELOADER_PHRASES;
        }

        $phrases = array_values(array_filter(
            $stored,
            fn (mixed $phrase): bool => is_array($phrase) && (filled($phrase['en'] ?? null) || filled($phrase['ru'] ?? null)),
        ));

        return $phrases === [] ? self::DEFAULT_PRELOADER_PHRASES : $phrases;
    }

    /**
     * Fills in any badge/field/locale the admin hasn't set yet with the
     * default, the same way {@see defaultedSectionHeadings()} does for
     * section titles. Both languages are always returned, in full.
     *
     * @param  array<string, mixed>  $stored
     * @return array<string, array<string, array<string, string>>>
     */
    public static function defaultedAboutStats(array $stored): array
    {
        $stats = self::DEFAULT_ABOUT_STATS;

        foreach ($stored as $badge => $fields) {
            if (! is_array($fields) || ! isset($stats[$badge])) {
                continue;
            }

            foreach (['label', 'value'] as $field) {
                if (! isset($fields[$field]) || ! is_array($fields[$field])) {
                    continue;
                }

                foreach (array_filter($fields[$field]) as $locale => $localizedValue) {
                    $stats[$badge][$field][$locale] = $localizedValue;
                }
            }
        }

        return $stats;
    }

    /**
     * Unlike {@see defaultedHotelName()}, there's no sensible default promo
     * text to fall back to — an admin who never set one just keeps seeing
     * no banner at all (the frontend hides it when this returns null).
     *
     * @return array{en: string|null, ru: string|null}|null
     */
    public static function normalizedPromoBanner(mixed $stored): ?array
    {
        // Pre-translation data was a single, English-only string; treat it
        // as the "en" value rather than discarding it.
        if (is_string($stored)) {
            return $stored === '' ? null : ['en' => $stored, 'ru' => null];
        }

        if (! is_array($stored)) {
            return null;
        }

        $banner = ['en' => $stored['en'] ?? null, 'ru' => $stored['ru'] ?? null];

        return filled($banner['en']) || filled($banner['ru']) ? $banner : null;
    }

    /**
     * The homepage hero slogan, in each supported language. Same shape and
     * fallback behavior as {@see normalizedPromoBanner()} — there's no
     * sensible default tagline to fall back to, so a never-set one is just
     * rendered as empty text rather than a placeholder.
     *
     * @return array{en: string|null, ru: string|null}|null
     */
    public static function normalizedTagline(mixed $stored): ?array
    {
        // Pre-translation data was a single, English-only string; treat it
        // as the "en" value rather than discarding it.
        if (is_string($stored)) {
            return $stored === '' ? null : ['en' => $stored, 'ru' => null];
        }

        if (! is_array($stored)) {
            return null;
        }

        $tagline = ['en' => $stored['en'] ?? null, 'ru' => $stored['ru'] ?? null];

        return filled($tagline['en']) || filled($tagline['ru']) ? $tagline : null;
    }
}
